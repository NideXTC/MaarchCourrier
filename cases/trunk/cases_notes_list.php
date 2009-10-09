<?php  /**
* File : cases_notes_list.php
*
* Frame, shows the notes of a document 
*
* @package Maarch Entreprise 1.0
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_manage_status.php");

require_once($_SESSION['pathtomodules']."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('manage_notes_doc', 'notes');
$func = new functions();
$cases = new cases();
$status_obj = new manage_status();
$sec = new security();


if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}


$where_request = $_SESSION['searching']['cases_request'];



$status = $status_obj->get_not_searchable_status();
$status_str = '';
for($i=0; $i<count($status);$i++)
{
	$status_str .=	"'".$status[$i]['ID']."',";
}
$status_str = preg_replace('/,$/', '', $status_str);
$where_request.= "  status not in (".$status_str.") ";
$where_clause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);

if(!empty($where_request))
{
	if($_SESSION['searching']['where_clause_bis'] <> "")
	{
		$where_clause = "((".$where_clause.") or (".$_SESSION['searching']['where_clause_bis']."))";
	}
	$where_request = '('.$where_request.') and ('.$where_clause.')';
}
else
{
	if($_SESSION['searching']['where_clause_bis'] <> "")
	{
		$where_clause = "((".$where_clause.") or (".$_SESSION['searching']['where_clause_bis']."))";
	}
	$where_request = $where_clause;
}
$where_request = str_replace("()", "(1=-1)", $where_request);
$where_request = str_replace("and ()", "", $where_request);

$request= new request();
$select = array();
$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice'] );


$select[$view] = array();
array_push($select[$view], "res_id",  "subject", "dest_user", "type_label", "creation_date", "destination", "category_id, exp_user_id", "category_id as category_img" );
$select[$_SESSION['tablename']['not_notes']] = array();
array_push($select[$_SESSION['tablename']['not_notes']],"id", "date", "note_text", "user_id");

$where_request .= " and ".$_SESSION['tablename']['not_notes'].".identifier = ".$view.".res_id";


//Listing only document in this case...

//Get the entire doc library
$docs_library = $cases->get_res_id($_SESSION['cases']['actual_case_id']);
$docs_limitation = ' and res_id in( ';

if(count($docs_library) >1)
{
	foreach($docs_library as $tmp_implode)
	{
		$docs_limitation .= $tmp_implode.',';
	}
	$docs_limitation = substr($docs_limitation, 0,-1);
}
else
$docs_limitation .= $docs_library[0];
$docs_limitation .= ' ) ';



$tabNotes=$request->select($select,$where_request.$docs_limitation,"order by ".$view.".res_id",$_SESSION['config']['databasetype'], "500", false );
$ind_notes1d = '';





if($_GET['size'] == "full")
{
	$size_medium = "15";
	$size_small = "15";
	$size_full = "70";
	$css = "listing spec detailtabricatordebug";
	$body = "";
	$cut_string = 100;
	$extend_url = "&size=full";
}
else
{
	$size_medium = "18";
	$size_small = "10";
	$size_full = "30";
	$css = "listingsmall";
	$body = "iframe";
	$cut_string = 20;
	$extend_url = "";
}

for ($ind_notes1=0;$ind_notes1<count($tabNotes);$ind_notes1++)
{
	for ($ind_notes2=0;$ind_notes2<count($tabNotes[$ind_notes1]);$ind_notes2++)
	{
		foreach(array_keys($tabNotes[$ind_notes1][$ind_notes2]) as $value)
		{
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _ID;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=false;
				$ind_notes1d = $tabNotes[$ind_notes1][$ind_notes2]['value'];
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="res_id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _NUM_GED;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
				$ind_notes1d = $tabNotes[$ind_notes1][$ind_notes2]['value'];
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="user_id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["user_id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _ID;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=false;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="lastname")
			{
				$tabNotes[$ind_notes1][$ind_notes2]['value']=$request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']);
				$tabNotes[$ind_notes1][$ind_notes2]["lastname"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_LASTNAME;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small ;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom"; 
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="date")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["date"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_DATE;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="firstname")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["firstname"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_FIRSTNAME;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="note_text")
			{
				//$tabNotes[$ind_notes1][$ind_notes2]['value'] = '<a href="javascript://" onclick="ouvreFenetre(\''.$_SESSION['urltomodules'].'notes/note_details.php?id='.$ind_notes1d.'&amp;resid='.$_SESSION['doc_id'].'&amp;coll_id='.$_SESSION['collection_id_choice'].'\', 450, 300)">'.substr($request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']), 0, 20).'... <span class="sstit"> > '._READ.'</span>';
				$tabNotes[$ind_notes1][$ind_notes2]['value'] = '<a href="javascript://" onclick="ouvreFenetre(\''.$_SESSION['urltomodules'].'notes/note_details.php?id='.$ind_notes1d.'&amp;resid='.$_SESSION['doc_id'].'&amp;coll_id='.$_SESSION['collection_id_choice'].$extend_url.'\', 450, 300)">'.$func->cut_string($request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']), $cut_string).'<span class="sstit"> > '._READ.'</span>';
				$tabNotes[$ind_notes1][$ind_notes2]["note_text"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_NOTES;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_full;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
		}
	}
}
//$request->show_array($tabNotes);
$core_tools->load_html();
//here we building the header
$core_tools->load_header();	
?>
<body id="<? echo $body; ?>">
<?php 
$title = '';
$list_notes = new list_show();
$list_notes->list_simple($tabNotes, count($tabNotes), $title,'id','id', false, '',$css);
?>
</body>
</html>
