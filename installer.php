<?php

/**
 * Handle module installer functions
 *
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow;

use Xaraya\Modules\InstallerClass;
use xarSystemVars;
use xarTableDDL;
use xarMod;
use xarPrivileges;
use xarMasks;
use xarHooks;
use xarModHooks;
use DataObjectDescriptor;
use DataObjectFactory;
use sys;
use Exception;

sys::import('xaraya.modules.installer');

/**
 * Handle module installer functions
 *
 * @todo add extra use ...; statements above as needed
 * @todo replaced workflow_*() function calls with $this->*() calls
 * @extends InstallerClass<Module>
 */
class Installer extends InstallerClass
{
    /**
     * Configure this module - override this method
     *
     * @todo use this instead of init() etc. for standard installation
     * @return void
     */
    public function configure()
    {
        $this->objects = [
            // add your DD objects here
            //'workflow_object',
        ];
        $this->variables = [
            // add your module variables here
            'hello' => 'world',
        ];
        $this->oldversion = '2.4.1';
    }

    /** xarinit.php functions imported by bermuda_cleanup */

    /**
     * initialise the workflow module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function init()
    {
        $this->var()->find('loadexample', $loadexample, 'checkbox', 1);

        $dbconn = $this->db()->getConn();
        $xartable = $this->db()->getTables();

        sys::import('xaraya.tableddl');

        // Galaxia developers use quotes around column names.
        // Since PostgreSQL creates column names in lowercase by
        // default, the column names must be surrounded by quotes.
        $dbtype  = xarSystemVars::get(null, 'DB.Type');
        switch ($dbtype) {
            case 'postgres':
                $qte = '"';
                break;
            case 'mysql':
            default:
                $qte = '';
                break;
        }

        /*
        $queries[] =
        "CREATE TABLE $xartable[workflow_activities] (
        activityId integer unsigned NOT NULL auto_increment,
        name varchar(80) default NULL,
        normalized_name varchar(80) default NULL,
        pId int(14) NOT NULL default '0',
        type enum('start','end','split','switch','join','activity','standalone') default NULL,
        isAutoRouted char(1) default NULL,
        flowNum int(10) default NULL,
        isInteractive char(1) default NULL,
        lastModif int(14) default NULL,
        description text,
        PRIMARY KEY  (activityId)
        )";
        */

        // Create table workflow_activities
        $table = $xartable['workflow_activities'];

        $fields = [
            $qte . 'activityId' . $qte        => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'name' . $qte              => ['type' => 'varchar','size' => 80,'null' => true],
            $qte . 'normalized_name' . $qte   => ['type' => 'varchar','size' => 80,'null' => true],
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'type' . $qte              => ['type' => 'varchar','size' => 20,'null' => true],
            $qte . 'isAutoRouted' . $qte      => ['type' => 'integer','size' => 1,'null' => true],
            $qte . 'flowNum' . $qte           => ['type' => 'integer','null' => true],
            $qte . 'isInteractive' . $qte     => ['type' => 'integer','size' => 1,'null' => true],
            $qte . 'lastModif' . $qte         => ['type' => 'integer','null' => true],
            $qte . 'description' . $qte       => ['type' => 'text','null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
        "CREATE TABLE $xartable[workflow_activity_roles] (
        activityId int(14) NOT NULL default '0',
        roleId int(14) NOT NULL default '0',
        PRIMARY KEY  (activityId,roleId)
        )";
        */
        // Create table workflow_activity_roles
        $table = $xartable['workflow_activity_roles'];

        $fields = [
            $qte . 'activityId' . $qte        => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
            $qte . 'roleId' . $qte            => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_instance_activities] (
      instanceId int(14) NOT NULL default '0',
      activityId int(14) NOT NULL default '0',
      started int(14) NOT NULL default '0',
      ended int(14) NOT NULL default '0',
      user varchar(200) default NULL,
      status enum('running','completed') default NULL,
      PRIMARY KEY  (instanceId,activityId)
    )";
        */

        // Create table workflow_instance_activities
        $table = $xartable['workflow_instance_activities'];

        $fields = [
            $qte . 'id' . $qte                => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'instanceId' . $qte        => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
            $qte . 'activityId' . $qte        => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
            $qte . 'started' . $qte           => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'ended' . $qte             => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'user' . $qte              => ['type' => 'varchar','size' => 200,'null' => true],
            $qte . 'status' . $qte            => ['type' => 'varchar','size' => 20,'null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_instance_comments] (
      cId integer unsigned NOT NULL auto_increment,
      instanceId int(14) NOT NULL default '0',
      user varchar(200) default NULL,
      activityId int(14) default NULL,
      hash varchar(32) default NULL,
      title varchar(250) default NULL,
      comment text,
      activity varchar(80) default NULL,
      timestamp int(14) default NULL,
      PRIMARY KEY  (cId)
    )";
        */

        // Create table workflow_instance_comments
        $table = $xartable['workflow_instance_comments'];

        $fields = [
            $qte . 'cId' . $qte               => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'instanceId' . $qte        => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'user' . $qte              => ['type' => 'varchar','size' => 200,'null' => true],
            $qte . 'activityId' . $qte        => ['type' => 'integer','null' => true],
            $qte . 'hash' . $qte              => ['type' => 'varchar','size' => 32,'null' => true],
            $qte . 'title' . $qte             => ['type' => 'varchar','size' => 250,'null' => true],
            $qte . 'comment' . $qte           => ['type' => 'text','null' => true],
            $qte . 'activity' . $qte          => ['type' => 'varchar','size' => 80,'null' => true],
            $qte . 'timestamp' . $qte         => ['type' => 'integer','null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_instances] (
      instanceId integer unsigned NOT NULL auto_increment,
      pId int(14) NOT NULL default '0',
      started int(14) default NULL,
      owner varchar(200) default NULL,
      nextActivity int(14) default NULL,
      nextUser varchar(200) default NULL,
      ended int(14) default NULL,
      status enum('active','exception','aborted','completed') default NULL,
      properties longblob,
      PRIMARY KEY  (instanceId)
    )";
        */

        // Create table workflow_instances
        $table = $xartable['workflow_instances'];

        $fields = [
            $qte . 'instanceId' . $qte        => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'started' . $qte           => ['type' => 'integer','null' => true],
            $qte . 'owner' . $qte             => ['type' => 'varchar','size' => 200,'null' => true],
            $qte . 'nextActivity' . $qte      => ['type' => 'integer','null' => true],
            $qte . 'nextUser' . $qte          => ['type' => 'varchar','size' => 200,'null' => true],
            $qte . 'ended' . $qte             => ['type' => 'integer','null' => true],
            $qte . 'status' . $qte            => ['type' => 'varchar','size' => 20,'null' => true],
            $qte . 'properties' . $qte        => ['type' => 'blob','null' => true],
            $qte . 'name' . $qte              => ['type' => 'varchar','size' => 80,'null' => false,'default' => ''],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_processes] (
      pId integer unsigned NOT NULL auto_increment,
      name varchar(80) default NULL,
      isValid char(1) default NULL,
      isActive char(1) default NULL,
      version varchar(12) default NULL,
      description text,
      lastModif int(14) default NULL,
      normalized_name varchar(80) default NULL,
      PRIMARY KEY  (pId)
    )";
        */

        // Create table workflow_processes
        $table = $xartable['workflow_processes'];

        $fields = [
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'name' . $qte              => ['type' => 'varchar','size' => 80,'null' => true],
            $qte . 'isValid' . $qte           => ['type' => 'integer','size' => 1,'null' => true],
            $qte . 'isActive' . $qte          => ['type' => 'integer','size' => 1,'null' => true],
            $qte . 'isSingleton' . $qte       => ['type' => 'integer','size' => 1,'null' => true],
            $qte . 'version' . $qte           => ['type' => 'varchar','size' => 12,'null' => true],
            $qte . 'description' . $qte       => ['type' => 'text','null' => true],
            $qte . 'lastModif' . $qte         => ['type' => 'integer','null' => true],
            $qte . 'normalized_name' . $qte   => ['type' => 'varchar','size' => 80,'null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_roles] (
      roleId integer unsigned NOT NULL auto_increment,
      pId int(14) NOT NULL default '0',
      lastModif int(14) default NULL,
      name varchar(80) default NULL,
      description text,
      PRIMARY KEY  (roleId)
    )";
        */

        // Create table workflow_roles
        $table = $xartable['workflow_roles'];

        $fields = [
            $qte . 'roleId' . $qte            => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'lastModif' . $qte         => ['type' => 'integer','null' => true],
            $qte . 'name' . $qte              => ['type' => 'varchar','size' => 80,'null' => true],
            $qte . 'description' . $qte       => ['type' => 'text','null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_transitions] (
      pId int(14) NOT NULL default '0',
      actFromId int(14) NOT NULL default '0',
      actToId int(14) NOT NULL default '0',
      PRIMARY KEY  (actFromId,actToId)
    )";
        */

        // Create table workflow_transitions
        $table = $xartable['workflow_transitions'];

        $fields = [
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'actFromId' . $qte         => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
            $qte . 'actToId' . $qte           => ['type' => 'integer','null' => false,'default' => '0','primary_key' => true],
        ];


        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_user_roles] (
      pId int(14) NOT NULL default '0',
      roleId int(14) NOT NULL auto_increment,
      user varchar(200) NOT NULL default '',
      PRIMARY KEY  (roleId,user)
    )";
        */

        // Create table workflow_user_roles
        $table = $xartable['workflow_user_roles'];

        $fields = [
            $qte . 'pId' . $qte               => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'roleId' . $qte            => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'user' . $qte              => ['type' => 'varchar','size' => 200,'null' => false,'default' => '','primary_key' => true],
        ];


        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        /*
        $queries[] =
    "CREATE TABLE $xartable[workflow_workitems] (
      itemId integer unsigned NOT NULL auto_increment,
      instanceId int(14) NOT NULL default '0',
      orderId int(14) NOT NULL default '0',
      activityId int(14) NOT NULL default '0',
      properties longblob,
      started int(14) default NULL,
      ended int(14) default NULL,
      user varchar(200) default NULL,
      PRIMARY KEY  (itemId)
    )";
        */

        // Create table workflow_workitems
        $table = $xartable['workflow_workitems'];

        $fields = [
            $qte . 'itemId' . $qte            => ['type' => 'integer','null' => false,'increment' => true,'primary_key' => true],
            $qte . 'instanceId' . $qte        => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'orderId' . $qte           => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'activityId' . $qte        => ['type' => 'integer','null' => false,'default' => '0'],
            $qte . 'type' . $qte              => ['type' => 'varchar','size' => 20,'null' => true],
            $qte . 'properties' . $qte        => ['type' => 'blob','null' => true],
            $qte . 'started' . $qte           => ['type' => 'integer','null' => true],
            $qte . 'ended' . $qte             => ['type' => 'integer','null' => true],
            $qte . 'user' . $qte              => ['type' => 'varchar','size' => 200,'null' => true],
        ];

        // Create the table DDL
        $query = xarTableDDL::dropTable($table);
        if (empty($query)) {
            return false;
        } // throw back
        $result = $dbconn->Execute($query);
        if (!$result) {
            return false;
        }
        $query = xarTableDDL::createTable($table, $fields);
        if (empty($query)) {
            return false;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        # --------------------------------------------------------
        #
        # Create DD objects
        #
        $module = 'workflow';
        $objects = [
            'workflow_roles',
            'workflow_processes',
            'workflow_activities',
            'workflow_instances',
            'workflow_instance_activities',
            'workflow_tracker',
            'workflow_history',
            'workflow_queue',
            //'cdcollection3',
        ];

        if (!xarMod::apiFunc('modules', 'admin', 'standardinstall', ['module' => $module, 'objects' => $objects])) {
            return;
        }

        # --------------------------------------------------------
        #
        # Set up modvars
        #
        $this->mod()->setVar('default.create', 0);
        $this->mod()->setVar('default.update', 0);
        $this->mod()->setVar('default.delete', 0);

        $this->mod()->setVar('SupportShortURLs', 0);
        $this->mod()->setVar('items_per_page', 20);
        $this->mod()->setVar('seenlist', '');

        # --------------------------------------------------------
        #
        # Set up hooks
        #
        //$this->create_old_hooks();
        //$this->create_new_hooks();

        // define privilege instances and masks
        $instances = [
            ['header' => 'external', // this keyword indicates an external "wizard"
                'query'  => $this->mod()->getURL('admin', 'privileges'),
                'limit'  => 0, ],
        ];
        xarPrivileges::defineInstance('workflow', 'Item', $instances);

        # --------------------------------------------------------
        #
        # Set up masks
        #
        xarMasks::register('ViewWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_OVERVIEW');
        xarMasks::register('ReadWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_READ');
        xarMasks::register('SubmitWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_COMMENT');
        xarMasks::register('ModerateWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_MODERATE');
        xarMasks::register('EditWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_EDIT');
        xarMasks::register('AddWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_ADD');
        xarMasks::register('ManageWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_DELETE');
        xarMasks::register('AdminWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_ADMIN');

        # --------------------------------------------------------
        #
        # Set up privileges
        #
        xarPrivileges::register('ViewWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_OVERVIEW');
        xarPrivileges::register('ReadWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_READ');
        xarPrivileges::register('SubmitWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_COMMENT');
        xarPrivileges::register('ModerateWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_MODERATE');
        xarPrivileges::register('EditWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_EDIT');
        xarPrivileges::register('AddWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_ADD');
        xarPrivileges::register('ManageWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_DELETE');
        xarPrivileges::register('AdminWorkflow', 'All', 'workflow', 'All', 'All', 'ACCESS_ADMIN');

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the workflow module from an old version
     * This function can be called multiple times
     * @return bool true on success
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.5':
            case '1.5.0':
                // Code to upgrade from version 1.5.0 goes here
                $this->delete_old_hooks();
                $this->create_new_hooks();

                // no break
            case '1.6.0':
                // Code to upgrade from version 1.6.0 goes here

            case '1.6.1':
                // Code to upgrade from version 1.6.1 goes here
                sys::import('modules.dynamicdata.class.objects.base');
                $objectinfo = DataObjectDescriptor::getObjectID(['name' => 'workflow_activities']);
                $object = DataObjectFactory::getObject(['name' => 'objects', 'itemid' => $objectinfo['objectid']]);
                $itemid = $object->getItem();
                if (empty($itemid) || $itemid != $objectinfo['objectid']) {
                    throw new Exception("Invalid item id $itemid");
                }
                // switch to fully qualified class name when using namespace
                $object->updateItem(['class' => 'Xaraya\Modules\Workflow\ActivityObject']);

                // no break
            case '1.7.0':
                // Code to upgrade from version 1.7.0 goes here

            case '1.7.1':
                // Code to upgrade from version 1.7.1 goes here
                $this->delete_new_hooks();
                $this->create_new_hooks();

                // no break
            case '2.4.1':
                // Code to upgrade from version 2.4.1 goes here
                $this->add_queue_object();

                // no break
            case '2.5.0':
                // Code to upgrade from version 2.5.0 goes here

            case '3.0.0':
                break;
        }
        // Update successful
        return true;
    }

    public function activate()
    {
        return $this->create_new_hooks();
    }

    public function deactivate()
    {
        return $this->delete_new_hooks();
    }

    public function add_queue_object()
    {
        $module = 'workflow';
        $objects = [
            'workflow_queue',
        ];

        if (!xarMod::apiFunc('modules', 'admin', 'standardinstall', ['module' => $module, 'objects' => $objects])) {
            return;
        }
    }

    public function create_new_hooks()
    {
        $namespace = 'Xaraya\Modules\Workflow\HookObservers';
        xarHooks::registerObserver('ItemCreate', 'workflow', $namespace . '\ItemCreateObserver');
        xarHooks::registerObserver('ItemUpdate', 'workflow', $namespace . '\ItemUpdateObserver');
        xarHooks::registerObserver('ItemDelete', 'workflow', $namespace . '\ItemDeleteObserver');
        xarHooks::registerObserver('ModuleRemove', 'workflow', $namespace . '\ModuleRemoveObserver');
        xarHooks::registerObserver('ItemDisplay', 'workflow', $namespace . '\ItemDisplayObserver');
        return true;
    }

    public function delete_new_hooks()
    {
        xarHooks::unregisterObserver('ItemCreate', 'workflow');
        xarHooks::unregisterObserver('ItemUpdate', 'workflow');
        xarHooks::unregisterObserver('ItemDelete', 'workflow');
        xarHooks::unregisterObserver('ModuleRemove', 'workflow');
        xarHooks::unregisterObserver('ItemDisplay', 'workflow');
        return true;
    }

    public function create_old_hooks()
    {
        if (!xarModHooks::register(
            'item',
            'create',
            'API',
            'workflow',
            'admin',
            'createhook'
        )) {
            return false;
        }
        if (!xarModHooks::register(
            'item',
            'update',
            'API',
            'workflow',
            'admin',
            'updatehook'
        )) {
            return false;
        }
        if (!xarModHooks::register(
            'item',
            'delete',
            'API',
            'workflow',
            'admin',
            'deletehook'
        )) {
            return false;
        }
        if (!xarModHooks::register(
            'module',
            'remove',
            'API',
            'workflow',
            'admin',
            'removehook'
        )) {
            return false;
        }

        /* // TODO: show pending instances someday ?
            if (!xarModHooks::register('item', 'usermenu', 'GUI',
                    'workflow', 'user', 'usermenu')) {
                return false;
            }
        */
        return true;
    }

    public function delete_old_hooks()
    {
        if (!xarModHooks::unregister(
            'item',
            'create',
            'API',
            'workflow',
            'admin',
            'createhook'
        )) {
            return false;
        }
        if (!xarModHooks::unregister(
            'item',
            'update',
            'API',
            'workflow',
            'admin',
            'updatehook'
        )) {
            return false;
        }
        if (!xarModHooks::unregister(
            'item',
            'delete',
            'API',
            'workflow',
            'admin',
            'deletehook'
        )) {
            return false;
        }
        // when a whole module is removed, e.g. via the modules admin screen
        // (set object ID to the module name !)
        if (!xarModHooks::unregister(
            'module',
            'remove',
            'API',
            'workflow',
            'admin',
            'removehook'
        )) {
            return false;
        }
        /* // TODO: show pending instances someday ?
            if (!xarModHooks::unregister('item', 'usermenu', 'GUI',
                    'workflow', 'user', 'usermenu')) {
                return false;
            }
        */
        return true;
    }

    /**
     * delete the workflow module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true on success
     */
    public function delete()
    {
        $dbconn = $this->db()->getConn();
        $xartable = $this->db()->getTables();

        sys::import('xaraya.tableddl');

        $mytables = [
            'workflow_activities',
            'workflow_activity_roles',
            'workflow_instance_activities',
            'workflow_instance_comments',
            'workflow_instances',
            'workflow_processes',
            'workflow_roles',
            'workflow_transitions',
            'workflow_user_roles',
            'workflow_workitems',
        ];

        foreach ($mytables as $mytable) {
            // Generate the SQL to drop the table using the API
            $query = xarTableDDL::dropTable($xartable[$mytable]);
            if (empty($query)) {
                return false;
            } // throw back

            // Drop the table and send exception if returns false.
            $result = $dbconn->Execute($query);
            if (!$result) {
                return false;
            }
        }

        // Remove module hooks
        //$this->delete_old_hooks();
        //$this->delete_new_hooks();

        // Remove all process files
        $this->remove_processes();

        // Remove Masks and Instances
        xarMasks::removemasks('workflow');
        xarPrivileges::removeInstances('workflow');

        $module = 'workflow';
        return xarMod::apiFunc('modules', 'admin', 'standarddeinstall', ['module' => $module]);
    }

    public function remove_processes()
    {
        sys::import('modules.workflow.lib.galaxia.config');
        $dir = GALAXIA_PROCESSES;
        if (!is_dir($dir)) {
            return;
        }
        $h = opendir($dir);
        while (($file = readdir($h)) != false) {
            if (is_dir($dir . '/' . $file) && $file != '.' && $file != '..') {
                $this->remove_directory($dir . '/' . $file);
            }
        }
        closedir($h);
    }

    public function remove_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $h = opendir($dir);
        while (($file = readdir($h)) != false) {
            if (is_file($dir . '/' . $file)) {
                @unlink($dir . '/' . $file);
            } else {
                if (is_dir($dir . '/' . $file) && $file != '.' && $file != '..') {
                    $this->remove_directory($dir . '/' . $file);
                }
            }
        }
        closedir($h);
        @rmdir($dir);
        if (file_exists($dir)) {
            @unlink($dir);
        }
    }
}
