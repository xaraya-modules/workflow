<?php

namespace Galaxia\Managers;

include_once(GALAXIA_LIBRARY . '/managers/base.php');

//!! RoleManager
//! A class to maniplate roles.
/*!
  This class is used to add,remove,modify and list
  roles used in the Workflow engine.
  Roles are managed in a per-process level, each
  role belongs to some process.
*/

/*!TODO
  Add a method to check if a role name exists in a process (to be used
  to prevent duplicate names)
*/

class RoleManager extends BaseManager
{
    public function get_role_id($pid, $name)
    {
        return ($this->getOne("select roleId from " . self::tbl('roles') . " where name=? and pId=?", [$name,$pid]));
    }

    /*!
      Gets a role fields are returned as an asociative array
    */
    public function get_role($pId, $roleId)
    {
        $query = "select * from " . self::tbl('roles') . " where `pId`=? and `roleId`=?";
        $result = $this->query($query, [$pId, $roleId]);
        $res = $result->fetchRow();
        return $res;
    }

    /*!
      Indicates if a role exists
    */
    public function role_name_exists($pid, $name)
    {
        return ($this->getOne("select count(*) from " . self::tbl('roles') . "where pId=? and name=?", [$pid,$name]));
    }

    /*!
      Maps a user to a role
    */
    public function map_user_to_role($pId, $user, $roleId)
    {
        $query = "delete from " . self::tbl('user_roles') . " where `roleId`=? and `user`=?";
        $this->query($query, [$roleId, $user]);
        $query = "insert into " . self::tbl('user_roles') . "(`pId`, `user`, `roleId`) values(?,?,?)";
        $this->query($query, [$pId,$user,$roleId]);
    }

    /*!
      Removes a mapping
    */
    public function remove_mapping($user, $roleId)
    {
        $query = "delete from " . self::tbl('user_roles') . " where `user`=? and `roleId`=?";
        $this->query($query, [$user, $roleId]);
    }

    /*!
      List mappings
    */
    public function list_mappings($pId, $offset, $maxRecords, $sort_mode, $find)
    {
        $sort_mode = $this->convert_sortmode($sort_mode);
        if ($find) {
            // no more quoting here - this is done in bind vars already
            $findesc = '%' . $find . '%';
            $query = "select `name`,`gr`.`roleId`,`user` from " . self::tbl('roles') . " gr, " . self::tbl('user_roles') . " gur where `gr`.`roleId`=`gur`.`roleId` and `gur`.`pId`=? and ((`name` like ?) or (`user` like ?) or (`description` like ?)) order by $sort_mode";
            $result = $this->query($query, [$pId,$findesc,$findesc,$findesc], $maxRecords, $offset);
            $query_cant = "select count(*) from " . self::tbl('roles') . " gr, " . self::tbl('user_roles') . " gur where `gr`.`roleId`=`gur`.`roleId` and `gur`.`pId`=? and ((`name` like ?) or (`user` like ?) or (`description` like ?))";
            $cant = $this->getOne($query_cant, [$pId,$findesc,$findesc,$findesc]);
        } else {
            $query = "select `name`,`gr`.`roleId`,`user` from " . self::tbl('roles') . "gr, " . self::tbl('user_roles') . " gur where `gr`.`roleId`=`gur`.`roleId` and `gur`.`pId`=? order by $sort_mode";
            $result = $this->query($query, [$pId], $maxRecords, $offset);
            $query_cant = "select count(*) from " . self::tbl('roles') . "gr, " . self::tbl('user_roles') . " gur where `gr`.`roleId`=`gur`.`roleId` and `gur`.`pId`=?";
            $cant = $this->getOne($query_cant, [$pId]);
        }
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }
        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    /*!
      Lists roles at a per-process level
    */
    public function list_roles($pId, $offset, $maxRecords, $sort_mode, $find, $where = '')
    {
        $sort_mode = $this->convert_sortmode($sort_mode);
        if ($find) {
            // no more quoting here - this is done in bind vars already
            $findesc = '%' . $find . '%';
            $mid = " where pId=? and ((name like ?) or (description like ?))";
            $bindvars = [$pId,$findesc,$findesc];
        } else {
            $mid = " where pId=? ";
            $bindvars = [$pId];
        }
        if ($where) {
            $mid .= " and ($where) ";
        }
        $query = "select * from " . self::tbl('roles') . " $mid order by $sort_mode";
        $query_cant = "select count(*) from " . self::tbl('roles') . " $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }
        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }



    /*!
      Removes a role.
    */
    public function remove_role($pId, $roleId)
    {
        $query = "delete from " . self::tbl('roles') . " where `pId`=? and `roleId`=?";
        $this->query($query, [$pId, $roleId]);
        $query = "delete from " . self::tbl('activity_roles') . " where `roleId`=?";
        $this->query($query, [$roleId]);
        $query = "delete from " . self::tbl('user_roles') . " where `roleId`=?";
        $this->query($query, [$roleId]);
    }

    /*!
      Updates or inserts a new role in the database, $vars is an asociative
      array containing the fields to update or to insert as needed.
      $pId is the processId
      $roleId is the roleId
    */
    public function replace_role($pId, $roleId, $vars)
    {
        $TABLE_NAME = self::tbl('roles');
        $now = date("U");
        $vars['lastModif'] = $now;
        $vars['pId'] = $pId;

        if ($roleId) {
            // update mode
            $first = true;
            $query = "update $TABLE_NAME set";
            $bindvars = [];
            foreach ($vars as $key => $value) {
                if (!$first) {
                    $query .= ',';
                }
                $query .= " $key=? ";
                $bindvars[] = $value;
                $first = false;
            }
            $query .= " where pId=? and roleId=? ";
            $bindvars[] = $pId;
            $bindvars[] = $roleId;
            $this->query($query, $bindvars);
        } else {
            $name = $vars['name'];
            if ($this->getOne("select count(*) from " . self::tbl('roles') . " where pId=? and name=?", [$pId,$name])) {
                return false;
            }
            unset($vars['roleId']);
            // insert mode
            $first = true;
            $query = "insert into $TABLE_NAME(";
            foreach (array_keys($vars) as $key) {
                if (!$first) {
                    $query .= ',';
                }
                $query .= "$key";
                $first = false;
            }
            $query .= ") values(";
            $first = true;
            $bindvars = [];
            foreach (array_values($vars) as $value) {
                if (!$first) {
                    $query .= ',';
                }
                $query .= "?";
                $bindvars[] = $value;
                $first = false;
            }
            $query .= ")";
            $this->query($query, $bindvars);
            $roleId = $this->getOne("select max(roleId) from $TABLE_NAME where pId=? and lastModif=?", [$pId,$now]);
        }
        // Get the id
        return $roleId;
    }
}
