<?php
/*
*
*    Copyright 2008,2012 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*
*   @author  Cyril Vazquez <dev@maarch.org>
*/

$admin = new core_tools();
$admin->test_admin('admin_listinstance_roles', 'entities');
$_SESSION['m_admin']= array();
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin_listinstance_roles&module=entities';
$page_label = _LISTINSTANCE_ROLES;
$page_id = "admin_listinstance_roles";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");

$func = new functions();
$request = new request;

$what = '';
$where = '';

$list = new list_show();

if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
    $what = $func->protect_string_db($_REQUEST['what']);
	$where = " lower(role_label) like lower('".$what."%') ";
}

$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
    $order = trim($_REQUEST['order']);
}
$field = 'role_label';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$select[ENT_LISTINSTANCE_ROLES] = array();
array_push($select[ENT_LISTINSTANCE_ROLES], "role_id", "role_label", "workflow_mode", "allow_entities");

$tab = $request->select($select, $where, $orderstr, $_SESSION['config']['databasetype']);
//$request->show();

for ($i=0;$i<count($tab);$i++)
{
    for ($j=0;$j<count($tab[$i]);$j++)
    {
        foreach(array_keys($tab[$i][$j]) as $value)
        {
            if($tab[$i][$j][$value]=="role_id")
            {
                $tab[$i][$j]["role_id"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]= _ID;
                $tab[$i][$j]["size"]="18";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }

            if($tab[$i][$j][$value]=="role_label")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["role_label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_ROLE_LABEL;
                $tab[$i][$j]["size"]="25";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }

            if($tab[$i][$j][$value]=="list_label")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["list_label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_LIST_LABEL;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }

            if($tab[$i][$j][$value]=="workflow_mode")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["list_label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_WORKFLOW_MODE;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }
            
            if($tab[$i][$j][$value]=="allow_entities")
            {
                if($tab[$i][$j]['value'] == 'Y') $img = 'picto_stat_enabled.gif';
                else $img = 'picto_stat_disabled.gif';
                $tab[$i][$j]["value"]= "<img src='".$_SESSION['config']['businessappurl']."static.php?filename=".$img."' title='".$tab[$i][$j]['value']."'/>";
                $tab[$i][$j]["label"]=_ALLOW_ENTITIES;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="center";
                $tab[$i][$j]["align"]="center";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }
        }
    }
}

$page_name = "admin_listinstance_roles";
$page_name_up = "admin_listinstance_role&mode=up";
$page_name_add = "admin_listinstance_role&mode=add";
$page_name_del = "admin_listinstance_role&mode=del";
$label_add = _ADD_LISTINSTANCE_ROLE;
$_SESSION['m_admin']['init'] = true;

$title = _LISTINSTANCE_ROLE_LIST." : ".$i." "._LISTINSTANCE_ROLES;
$autoCompletionArray = false;//array();

$list->admin_list($tab, $i, $title, 'role_id','admin_listinstance_roles','entities', 'role_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_ROLES, _ROLE, $_SESSION['config']['businessappurl'].'static.php?module=entities&filename=manage_entities_b.gif', true, true, false, true, "", true, $autoCompletionArray, false, true);
?>