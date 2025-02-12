<?php

use PHPUnit\Framework\TestCase;
use Xaraya\Context\Context;
use Xaraya\Context\SessionContext;
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowEventSubscriber;
use Xaraya\Modules\Workflow\WorkflowLogger;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
//use Xaraya\Sessions\SessionHandler;
use Symfony\Component\Workflow\Workflow;

final class SymfonyWorkflowTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // initialize bootstrap
        sys::init();
        // initialize caching - delay until we need results
        xarCache::init();
        // initialize loggers
        xarLog::init();
        // initialize database - delay until caching fails
        xarDatabase::init();
        // initialize modules
        //xarMod::init();
        // initialize users
        //xarUser::init();
        //WorkflowProcess::setLogger(new WorkflowLogger());
        xarSession::setSessionClass(SessionContext::class);
    }

    public static function tearDownAfterClass(): void {}

    protected function setUp(): void
    {
        WorkflowProcess::setLogger(new WorkflowLogger());
    }

    protected function tearDown(): void
    {
        WorkflowProcess::reset();
    }

    public function testGetWorkflow(): void
    {
        $workflow = WorkflowProcess::getProcess('cd_loans');
        $expected = 'cd_loans';
        $this->assertEquals($expected, $workflow->getName());
        $definition = $workflow->getDefinition();
        $expected = 10;
        $this->assertCount($expected, $definition->getPlaces());
        $expected = 10;
        $this->assertCount($expected, $definition->getTransitions());
    }

    public function testEventSubscriber(): void
    {
        $workflow = WorkflowProcess::getProcess('hook_sample');
        $expected = 'hook_sample';
        $this->assertEquals($expected, $workflow->getName());
        $events = WorkflowEventSubscriber::getSubscribedEvents();
        $expected = 2;
        $this->assertCount($expected, $events);
        $callbacks = WorkflowEventSubscriber::getCallbackFunctions();
        $this->assertCount($expected, $callbacks);

        $workflow = WorkflowProcess::getProcess('cd_loans');
        $expected = 'cd_loans';
        $this->assertEquals($expected, $workflow->getName());
        $events = WorkflowEventSubscriber::getSubscribedEvents();
        $expected = 9;
        $this->assertCount($expected, $events);
        $callbacks = WorkflowEventSubscriber::getCallbackFunctions();
        $this->assertCount($expected, $callbacks);
    }

    public function testGetSubject_AnonTransitions(): void
    {
        // create subject without context
        $subject = new WorkflowSubject('cdcollection', 5);
        $expected = 'cdcollection';
        $this->assertEquals($expected, $subject->getObject()->name);
        $this->assertEquals(null, $subject->getObject()->getContext());

        // initiate workflow
        $workflow = WorkflowProcess::getProcess('cd_loans');
        $marking = $workflow->getMarking($subject);
        $expected = 1;
        $this->assertCount($expected, $marking->getPlaces());
        $expected = 'available';
        $this->assertEquals($expected, $subject->getMarking());

        // get transitions for anonymous (= guarded)
        $expected = 0;
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount($expected, $transitions);

        $expected = false;
        $transition = "request";
        $result = $workflow->can($subject, $transition);
        $this->assertEquals($expected, $result);

        $expected = 1;
        $blockers = $workflow->buildTransitionBlockerList($subject, $transition);
        $this->assertCount($expected, $blockers);
        $expected = "Sorry, you do not have 'update' access to subject 'cdcollection.5' for event 'workflow.cd_loans.guard.request'";
        foreach ($blockers as $blocker) {
            $this->assertEquals($expected, $blocker->getMessage());
            break;
        }
    }

    public function testGetSubject_UserTransitions(): void
    {
        // initialize session
        xarSession::init();
        //$_SESSION[SessionHandler::PREFIX.'role_id'] = 6;
        $xarayaContext = new Context(['hello' => 'world']);
        xarSession::getInstance()->startSession($xarayaContext, 'phpunit', 6);

        // create subject with Xaraya context
        $subject = new WorkflowSubject('cdcollection', 5);
        $subject->setContext($xarayaContext);

        $expected = 'cdcollection';
        $this->assertEquals($expected, $subject->getObject()->name);
        $this->assertEquals($xarayaContext, $subject->getObject()->getContext());
        $expected = 6;
        $userId = $subject->getObject()->getContext()->getUserId();
        $this->assertEquals($expected, $userId);

        // initiate workflow
        $workflow = WorkflowProcess::getProcess('cd_loans');

        // get transitions for admin user (= guarded)
        $expected = 1;
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount($expected, $transitions);
        $expected = "request";
        $this->assertEquals($expected, $transitions[0]->getName());

        $expected = true;
        $transition = "request";
        $result = $workflow->can($subject, $transition);
        $this->assertEquals($expected, $result);

        $expected = 0;
        $blockers = $workflow->buildTransitionBlockerList($subject, $transition);
        $this->assertCount($expected, $blockers);

        $expected = ["requested" => 1];
        $transitionContext = [Workflow::DISABLE_ANNOUNCE_EVENT => true];
        $marking = $workflow->apply($subject, "request", $transitionContext);
        $this->assertEquals($expected, $marking->getPlaces());
        $expected = $transitionContext;
        $this->assertEquals($expected, $marking->getContext());
        // @todo check context creation for subject
        $this->assertEquals($expected, $subject->getContext()->getArrayCopy());

        // no update of object context with transition context
        $expected = $xarayaContext;
        $this->assertEquals($expected, $subject->getObject()->getContext());
    }

    public function testGetSubject_WrongContext(): void
    {
        $transitionContext = [Workflow::DISABLE_ANNOUNCE_EVENT => true];
        // create subject with transition context
        $subject = new WorkflowSubject('cdcollection', 5);
        $subject->setContext($transitionContext);

        $expected = 'cdcollection';
        try {
            $this->assertEquals($expected, $subject->getObject()->name);
            $this->assertEquals($transitionContext, $subject->getObject()->getContext());
            $userId = $subject->getObject()->getContext()->getUserId();
        } catch (Throwable $e) {
            $expected = 'Call to a member function tracePath() on array';
            $this->assertEquals($expected, $e->getMessage());
        }
        //$expected = 'Call to a member function getUserId() on array';
    }
}
