<?php
/**
* File : change_doctype.php
*
* Script called by an ajax object to process the document type change during
* indexing (index_mlb.php)
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Cyril Vazquez  <dev@maarch.org>
*/
require_once 'modules/entities/class/class_manage_listdiff.php';
require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";


$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$diffList = new diffusion_list();

$objectType = $_REQUEST['objectType'];
$objectId = $_REQUEST['objectId'];
$origin = 'process';

// Get listmodel_parameters
$_SESSION[$origin]['difflist_type'] = $diffList->get_difflist_type($objectType);

if ($objectId <> '') {
    $_SESSION[$origin]['difflist_object']['object_id'] = $objectId;
    if ($objectType == 'entity_id') {
        $query = "select entity_label from entities where entity_id = '" . $objectId . "'";
        $db->query($query);
        $res = $db->fetch_object();
        if ($res->entity_label <> '') {
            $_SESSION[$origin]['difflist_object']['object_label'] = $res->entity_label;
        }
    }
}

// Fill session with listmodel
$_SESSION[$origin]['diff_list'] = $diffList->get_listmodel($objectType, $objectId);
$_SESSION[$origin]['diff_list']['difflist_type'] = $_SESSION[$origin]['diff_list']['object_type'];
$roles = $diffList->list_difflist_roles();
$circuit = $_SESSION[$origin]['diff_list'];

if ( $circuit['object_type'] == 'VISA_CIRCUIT'){
	$id_tab="tab_visaSetWorkflow";
	$id_form="form_visaSetWorkflow";
}
else{
	$id_tab="tab_avisSetWorkflow";
	$id_form="form_avisSetWorkflow";
}
$content = '';


$content = '';

$content .= '<thead><tr>';
$content .= '<th style="width:30%;" align="left" valign="bottom"><span>Visa</span></th>';
$content .= '<th style="width:5%;"></th>';
$content .= '<th style="width:5%;"></th>';
$content .= '<th style="width:5%;"></th>';
$content .= '<th style="width:5%;"></th>';
$content .= '<th style="width:45%;" align="left" valign="bottom"><span>Consigne</span></th>';
$content .= '</tr></thead>';
$content .= '<tbody>';
$color = "";
		
if (isset($circuit['visa']['users'])){
	foreach($circuit['visa']['users'] as $seq=>$step){
		if($color == ' class="col"') {
			$color = '';
		} else {
			$color = ' class="col"';
		}
		
		$content .= '<tr ' . $color . '>';
			$content .= '<td>';
			$visa = new visa();
			$tab_users = $visa->getUsersVis();
			$content .= '<select id="conseiller_'.$seq.'" name="conseiller_'.$seq.'" >';
			$content .= '<option value="" >S&eacute;lectionnez un utilisateur</option>';
			foreach($tab_users as $user){
				$selected = " ";
				if ($user['id'] == $step['user_id'])
					$selected = " selected";
				$content .= '<option value="'.$user['id'].'" '.$selected.'>'.$user['lastname'].', '.$user['firstname'].'</option>';
			}
			$content .= '</select>';
			
			$content .= '</td>';
			$up = ' style="visibility:visible"';
			$down = ' style="visibility:visible"';
			$add = ' style="visibility:hidden"';
			if ($seq == 0){
				$up = ' style="visibility:hidden"';
			}
			
			$content .= '<td><a href="javascript://"  '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" ><i class="fa fa-arrow-down fa-2x"></i></a></td>';
			$content .= '<td><a href="javascript://"   '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" ><i class="fa fa-arrow-up fa-2x"></i></a></td>';
			$content .= '<td><a href="javascript://" onclick="delRow(this.parentNode.parentNode.rowIndex,\''.$id_tab.'\')" id="suppr_'.$j.'" name="suppr_'.$j.'" style="visibility:visible;" ><i class="fa fa-user-times fa-2x"></i></a></td>';
			$content .= '<td><a href="javascript://" '.$add.'  id="add_'.$seq.'" name="add_'.$seq.'" onclick="addRow(\''.$id_tab.'\')" ><i class="fa fa-user-plus fa-2x"></i></a></td>';
			$content .= '<td><input type="text" id="consigne_'.$seq.'" name="consigne_'.$seq.'" value="" style="width:100%;"/></td>';	
		$content .= '</tr>';
	}
}

//ajout signataire
					
	$seq = count ($circuit['visa']['users']);

	if($color == ' class="col"') {
		$color = '';
	} else {
		$color = ' class="col"';
	}

	$content .= '<tr ' . $color . '>';
		$content .= '<td>';
		$tab_users = $visa->getUsersVis();
		$content .= '<select id="conseiller_'.$seq.'" name="conseiller_'.$seq.'" >';
		$content .= '<option value="" >S&eacute;lectionnez un utilisateur</option>';
		foreach($tab_users as $user){
			$selected = " ";
			if ($user['id'] == $circuit['sign']['users'][0]['user_id'])
				$selected = " selected";
			$content .= '<option value="'.$user['id'].'" '.$selected.'>'.$user['lastname'].', '.$user['firstname'].'</option>';
		}
		$content .= '</select>';
		
		$content .= '</td>';
		$up = ' style="visibility:visible"';
		$down = ' style="visibility:hidden"';
		$add = ' style="visibility:visible"';
							
		$content .= '<td><a href="javascript://"  '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" ><i class="fa fa-arrow-down fa-2x"></i></a></td>';
		$content .= '<td><a href="javascript://"   '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" ><i class="fa fa-arrow-up fa-2x"></i></a></td>';
		$content .= '<td><a href="javascript://" onclick="delRow(this.parentNode.parentNode.rowIndex,\''.$id_tab.'\')" id="suppr_'.$j.'" name="suppr_'.$j.'" style="visibility:visible;" ><i class="fa fa-user-times fa-2x"></i></a></td>';
		$content .= '<td><a href="javascript://" '.$add.'  id="add_'.$seq.'" name="add_'.$seq.'" onclick="addRow(\''.$id_tab.'\')" ><i class="fa fa-user-plus fa-2x"></i></a></td>';
		$content .= '<td><input type="text" id="consigne_'.$seq.'" name="consigne_'.$seq.'" value="" style="width:100%;"/></td>';							
	
	$content .= '</tr>';

$content .= '</tbody>';



echo "{status : 0, div_content : '" . addslashes($content.'<br>') . "'}";
exit();
