<?php

namespace Galaxia\Api;

include_once(GALAXIA_LIBRARY . '/common/base.php');
use Galaxia\Common\Base;

/**
 * Workflow process class
 *
 * Models a workflow process and the actions ON it. In contract with the
 * process manager which models the action WITH (sets of) it.
 *
 * @package default
 * @author Marcel van der Boom
 *
 * @todo make a distinction between a process as available for the framework and as available for the instance runtime.
**/
class Process extends Base
{
    public $name;
    public $description;
    public $version;
    public $pId    = 0;
    public $graph  = '';

    private $active = false;            // Process activated?
    private $valid  = false;            // Process validated?

    /**
     * Construct an object for a process with specified ID
     *
    **/
    public function __construct($id)
    {
        parent::__construct();
        $this->getProcess($id);
    }

    /**
     * Activating and Deactivating a process
     *
     * (De-)Activating is a two step process. We update the DB first and
     * the internal object representing the Process. The public methods refer
     * to the private method for their implementation, since it's just the
     * boolean value which differs.
     *
     * @return void
     * @see Process::SetActiveFlag
     * @todo apply this phpdoc to all three methods.
    **/
    public function activate()
    {
        $this->SetActiveFlag(true);
    }
    public function deactivate()
    {
        $this->SetActiveFlag(false);
    }
    private function SetActiveFlag($value)
    {
        assert($value === true or $value === false);
        // DB
        $query = "update " . self::tbl('processes') . " set isActive=? where pId=?";
        $this->query($query, [$value ? 1 : 0,$this->pId]);
        $msg = sprintf(\xarMLS::translate('Process %d has been (de)-activated'), $this->pId);
        // Object
        $this->active = $value;
        $this->notify_all(3, $msg);
    }

    /**
     * Validating and Invalidating a process
     *
     * (In)validating is, much like (De)activating a two step process. We update
     * the DB and the internal object presentation. Unlike activation, validation
     * is more more complex, as it involves a test against a ruleset.
    **/
    public function invalidate()
    {
        // Make sure we are inactive
        $this->deactivate();

        $query = "update " . self::tbl('processes') . " set isValid=? where pId=?";
        $this->query($query, [0,$this->pId]);
        $this->valid = false;
    }
    public function validate()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Loads a process from the database
    **/
    private function getProcess($pId)
    {
        $query = "select * from " . self::tbl('processes') . "where `pId`=?";
        $result = $this->query($query, [$pId]);
        if (!$result->numRows()) {
            return false;
        }
        $res = $result->fetchRow();
        $this->name = $res['name'];
        $this->description = $res['description'];
        $this->version = $res['version'];
        $this->pId = $res['pId'];
        $this->graph = GALAXIA_PROCESSES . "/" . $this->getNormalizedName() . "/graph/" . $this->getNormalizedName() . ".png";
    }

    /**
     * Various simple getters
     *
     * @todo make this phpdoc apply to all getters here (forgot how to do that)
     * @todo consider a helper like prepforstore instead of putting it in here.
    **/
    // Process name
    public function getName()
    {
        return $this->name;
    }

    // Name for filesystem storage
    public function getNormalizedName()
    {
        return self::normalize($this->getName(), $this->getVersion());
    }

    // Version string
    public function getVersion()
    {
        return $this->version;
    }
    // Path to process graph
    public function getGraph()
    {
        return $this->graph;
    }

    // Process Active?
    public function isActive()
    {
        return $this->active;
    }
    // Process Valid?
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Gets information about an activity in this process by name,
     * e.g. $actinfo = $process->getActivityByName('Approve CD Request');
     *
     * if ($actinfo) {
     *  $some_url = 'tiki-g-run_activity.php?activityId=' . $actinfo['activityId'];
     * }
     * @todo not sure why this is here, probably just for the runtime convenience.
    **/
    public function getActivityByName($actname)
    {
        // Get the activity data
        $query = "select * from " . self::tbl('activities') . "where `pId`=? and `name`=?";
        $result = $this->query($query, [$this->pId,$actname]);
        if (!$result->numRows()) {
            return false;
        }
        $res = $result->fetchRow();
        return $res;
    }

    /**
     * Checks if this proces has an activity, by name
     *
     * @return boolean
    **/
    public function hasActivity($name)
    {
        $bindvars = [$this->pId, $this->getNormalizedName()];
        $query    = "SELECT COUNT(*) FROM " . self::tbl('activities') . " WHERE pId=? AND normalized_name=?";
        return ($this->getOne($query, $bindvars) > 0);
    }

    /**
     * Returns all the activities for a process as
     * an array of Activity Objects.
     *
     * @todo consider returning an ActivityList Object
    */
    public function &getActivities()
    {
        $query = "select activityId from " . self::tbl('activities') . "where pId=?";
        $result = $this->query($query, [$this->pId]);
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = WorkflowActivity::get($res['activityId']);
        }
        return $ret;
    }

    public static function normalize($name, $version = null)
    {
        $name = $name . '_' . $version;
        return parent::normalize($name);
    }

    public static function exists($name, $version)
    {
        // @todo get rid of this temporary trick to get an object which has a $db
        $dummy = new Base();
        $name = self::normalize($name, $version);
        return $dummy->getOne("select count(*) from " . self::tbl('processes') . " where normalized_name=?", [$name]);
    }

    public function removeRoles()
    {
        $query = "delete from " . self::tbl('roles') . " where pId=?";
        $this->query($query, [$this->pId]);
        $query = "delete from " . self::tbl('user_roles') . " where pId=?";
        $this->query($query, [$this->pId]);
    }

    public function removeActivity($actId)
    {
        $act     = WorkflowActivity::get($actId);
        $actname = $act->getNormalizedName();

        // This removes the actual activity
        $query = "delete from " . self::tbl('activities') . " where pId=? and activityId=?";
        $this->query($query, [$this->pId, $actId]);

        // @todo This is activity->removeTransitions
        $query = "select actFromId,actToId from " . self::tbl('transitions') . " where actFromId=? or actToId=?";
        $result = $this->query($query, [$actId, $actId]);
        while ($res = $result->fetchRow()) {
            // @todo This is activity->removeTransition(from,to)
            $query = "delete from " . self::tbl('transitions') . " where actFromId=? and actToId=?";
            $this->query($query, [$res['actFromId'], $res['actToId']]);
        }

        // @todo This is activity->removeRoles
        $query = "delete from " . self::tbl('activity_roles') . " where activityId=?";
        $this->query($query, [$actId]);
        // And we have to remove the user and compiled files
        // for this activity
        $procname = $this->getNormalizedName();
        if (file_exists(GALAXIA_PROCESSES . "/$procname/code/activities/$actname" . '.php')) {
            unlink(GALAXIA_PROCESSES . "/$procname/code/activities/$actname" . '.php');
        }
        if (file_exists(GALAXIA_PROCESSES . "/$procname/code/templates/$actname" . '.xt')) {
            unlink(GALAXIA_PROCESSES . "/$procname/code/templates/$actname" . '.xt');
        }
        if (file_exists(GALAXIA_PROCESSES . "/$procname/compiled/$actname" . '.php')) {
            unlink(GALAXIA_PROCESSES . "/$procname/compiled/$actname" . '.php');
        }
        return true;
    }
}
