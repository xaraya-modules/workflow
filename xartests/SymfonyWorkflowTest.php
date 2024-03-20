<?php

use PHPUnit\Framework\TestCase;
use Xaraya\Context\Context;
use Xaraya\Context\SessionContext;
//use Xaraya\Sessions\SessionHandler;

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
        sys::import('modules.workflow.class.process');
        sys::import('modules.workflow.class.subject');
        sys::import('modules.workflow.class.tracker');
        //sys::import('modules.workflow.class.logger');
        xarWorkflowProcess::setLogger(new xarWorkflowLogger());
        xarSession::setSessionClass(SessionContext::class);
    }

    public static function tearDownAfterClass(): void
    {
        //xarSystemVars::set(sys::LAYOUT, 'BaseURI', null);
    }

    public function testGetWorkflow(): void
    {
        $workflow = xarWorkflowProcess::getProcess('cd_loans');
        $expected = 'cd_loans';
        $this->assertEquals($expected, $workflow->getName());
        $definition = $workflow->getDefinition();
        $expected = 10;
        $this->assertCount($expected, $definition->getPlaces());
        $expected = 10;
        $this->assertCount($expected, $definition->getTransitions());
    }

    public function testGetSubject_AnonTransitions(): void
    {
        // create subject without context
        $subject = new xarWorkflowSubject('cdcollection', 5);
        $expected = 'cdcollection';
        $this->assertEquals($expected, $subject->getObject()->name);
        $this->assertEquals(null, $subject->getObject()->getContext());

        // initiate workflow
        $workflow = xarWorkflowProcess::getProcess('cd_loans');
        $marking = $workflow->getMarking($subject);
        $expected = 1;
        $this->assertCount($expected, $marking->getPlaces());
        $expected = 'available';
        $this->assertEquals($expected, $subject->getMarking());

        // get transitions for anonymous (= guarded)
        $expected = 0;
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount($expected, $transitions);
    }

    public function testGetSubject_UserTransitions(): void
    {
        // initialize session
        xarSession::init();
        //$_SESSION[SessionHandler::PREFIX.'role_id'] = 6;
        $context = new Context(['hello' => 'world']);
        xarSession::getInstance()->startSession($context, 'phpunit', 6);

        // create subject with context
        $subject = new xarWorkflowSubject('cdcollection', 5);
        $subject->setContext($context);

        $expected = 'cdcollection';
        $this->assertEquals($expected, $subject->getObject()->name);
        $this->assertEquals($context, $subject->getObject()->getContext());

        // initiate workflow
        $workflow = xarWorkflowProcess::getProcess('cd_loans');

        // get transitions for admin user (= guarded)
        $expected = 1;
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount($expected, $transitions);
    }
}
