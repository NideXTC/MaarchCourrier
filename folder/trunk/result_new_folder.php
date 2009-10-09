<?php 
/**
* File : result_new_folder.php
*
* Frame : show the new folders created recently
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 10/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
  session_name('PeopleBox'); 
session_start();
 require_once($_SESSION['pathtocoreclass']."class_functions.php");
 require_once($_SESSION['pathtocoreclass']."class_db.php");
 require_once($_SESSION['pathtocoreclass']."class_request.php");
	require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");

 	require_once($_SESSION['pathtocoreclass']."class_core_tools.php"); 
$core_tools = new core_tools();
$core_tools->load_lang();

require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");

 $core_tools->load_html();
//here we building the header
$core_tools->load_header( );
?>

<body>
<?php 

	$select[$_SESSION['tablename']['fold_folders']]= array();
	$where = "";
	array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id","folder_id","custom_t1","custom_t2","custom_d1");
	
	if($_SESSION['res_folder'] == 'matricule' )
	{
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where = " folder_id ilike '". $_SESSION['search_res_folder']."%' and status <> 'DEL'";	
		}
		else
		{
			$where = " folder_id like '". $_SESSION['search_res_folder']."%' and status <> 'DEL'";	
		}

	}	
	elseif( $_SESSION['res_folder'] == 'nom' )
	{
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where = " custom_t1 ilike '". $_SESSION['search_res_folder']."%' and status <> 'DEL'";
		}
		else
		{
			$where = " custom_t1 like '". $_SESSION['search_res_folder']."%' and status <> 'DEL'";
		}
	}
	else
	{
		$where = " folder_id like 'T_%' and status <> 'DEL'";
	}
	$request= new request;
	$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype']);
	//$request->show_array($tab);
	//$folder_tmp = new folder();
	
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			//echo "test : ".$tab[$i][$j]."<br/>";
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="folders_system_id")
				{
					$tab[$i][$j]["folders_system_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_NUM_GED;
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
					
				}
				if($tab[$i][$j][$value]=="folder_id")
				{
					$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FOLDER_NUM;
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="custom_t1")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="center";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="custom_t2")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="custom_d1")
				{
					$tab[$i][$j]["label"]=_BIRTH_DATE;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				
			}
		}
	}
?>
<div align="center">
<?php 
	$list=new list_show();
	//$list->list_doc($tab,$i,_FOLDERS_LIST." : ".$i." "._FOLDERS,'res_id',"select_folder","folders_system_id","folder_detail",false,true,"get",$_SESSION['urltomodules']."folder/res_select_folder.php",_CHOOSE, false, false, "false", false, TRUE);
	$list->list_doc($tab,$i,_NEW_FOLDERS_LIST." : ".$i." "._FOLDERS,'res_id',"select_folder","folders_system_id","folder_detail",false,false,"","",'', false, false, "false", false, false);
?>
</div>
</body>
</html>