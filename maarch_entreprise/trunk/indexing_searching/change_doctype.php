<?php
/*
*    Copyright 2008,2009 Maarch
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
*/

/**
* @brief  Script called by an ajax object to process the document type change during indexing (index_mlb.php), process limit date calcul and possible services from apps or module
*
* @file change_doctype.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath'].'class'.DIRECTORY_SEPARATOR."class_types.php");

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$type = new types();

if(!isset($_REQUEST['type_id']) || empty($_REQUEST['type_id']))
{
	$_SESSION['error'] = _DOCTYPE.' '._IS_EMPTY;
	echo "{status : 1, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}

// Process limit date calcul
$db->connect();
$db->query("select process_delay from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = ".$_REQUEST['type_id']);
//$db->show();

if($db->nb_result() == 0)
{
	$_SESSION['error'] = _NO_DOCTYPE_IN_DB;
	echo "{status : 2, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}

$res = $db->fetch_object();
$delay = $res->process_delay;

$mandatory_indexes = $type->get_mandatory_indexes($_REQUEST['type_id'], 'letterbox_coll');
$indexes = $type->get_indexes($_REQUEST['type_id'], 'letterbox_coll');

$opt_indexes = '';
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
{
	$browser_ie = true;
	$display_value = 'block';
}
elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) )
{
	$browser_ie = true;
	$display_value = 'block';
}
else
{
	$browser_ie = false;
	$display_value = 'table-row';
}
$opt_indexes  = '';
if(count($indexes) > 0)
{
	$opt_indexes .= '<table width="100%" align="center" border="0">';
	foreach(array_keys($indexes) as $key)
	{
		//echo $key.' ';
		$mandatory = false;
		if(in_array($key, $mandatory_indexes))
		{
			$mandatory = true;
		}

			$opt_indexes .= '<tr >';

			$opt_indexes.='<td><label for="'.$key.'" class="form_title" >'.$indexes[$key]['label'].'</label></td>';
			$opt_indexes .='<td>&nbsp;</td>';
			$opt_indexes .='<td class="indexing_field">';
			if($indexes[$key]['type'] == 'date')
			{
				$opt_indexes .='<input name="'.$key.'" type="text" id="'.$key.'" value="'.$today.'" onclick="showCalender(this);"/>';
			}
			else
			{
				$opt_indexes .= '<input name="'.$key.'" type="text" id="'.$key.'" value="" />';
			}
			$opt_indexes .='</td>';
			$opt_indexes .='<td><span class="red_asterisk" id="'.$key.'_mandatory" style="display:';
			if($mandatory)
			{
				$opt_indexes .= 'inline';
			}
			else
			{
				$opt_indexes .= 'none';
			}
			$opt_indexes .= ';">*</span>&nbsp;</td>';
		$opt_indexes .= '</tr>';
	}
	$opt_indexes .= '</table>';
}

if(!$core->is_module_loaded('alert_diffusion'))
{
	$_SESSION['error'] = _MODULE.' alert_diffusion '._IS_MISSING;
	echo "{status : 3, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}


require_once($_SESSION['pathtomodules'].'alert_diffusion'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_alert_engine.php');
$alert_engine = new alert_engine();
$date = $alert_engine->date_max_treatment($delay, false);
$process_date = $db->dateformat($date, '-');
$services = '[';
$_SESSION['indexing_services'] = array();
$_SESSION['indexing_type_id'] = $_REQUEST['type_id'];
// Module and apps services
$core->execute_modules_services($_SESSION['modules_services'], 'change_doctype.php', 'include');
$core->execute_app_services($_SESSION['app_services'], 'change_doctype.php', 'include');
for($i=0;$i< count($_SESSION['indexing_services']);$i++)
{
	$services .= "{ script : '".$_SESSION['indexing_services'][$i]['script']."', function_to_execute : '".$_SESSION['indexing_services'][$i]['function_to_execute']."', arguments : '[";
	for($j=0;$j<count($_SESSION['indexing_services'][$i]['arguments']);$j++)
	{
		$services .= " { id : \'".$_SESSION['indexing_services'][$i]['arguments'][$j]['id']."\', value : \'".addslashes($_SESSION['indexing_services'][$i]['arguments'][$j]['value'])."\' }, ";
	}
	$services = preg_replace('/, $/', '', $services);
	$services .= "]' }, ";
}
$services = preg_replace('/, $/', '', $services);
$services .= ']';
unset($_SESSION['indexing_type_id']);
unset($_SESSION['indexing_services']);
echo "{status : 0, process_date : '".trim($process_date)."', opt_indexes : '".addslashes($opt_indexes)."', services : ".$services."}";
exit();
?>
