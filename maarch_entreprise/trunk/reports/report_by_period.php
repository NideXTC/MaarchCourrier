<?php

require_once('modules'.DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$core_tools = new core_tools();
$rep = new reports();
$core_tools->load_lang();
$id = '';
if(isset($_REQUEST['arguments']) && !empty($_REQUEST['arguments']))
{
	$id = $rep->get_arguments_for_report($_REQUEST['arguments'], 'id');
}
if(empty($id))
{
	echo _ID.' '._IS_MISSING;
}
$content = '';
$content .='<div id="params">';
	$content .='<form id="report_by_period_form" name="report_by_period_form" method="get" action="">';
	$content .='<input type="hidden" name="id_report" id="id_report" value="'.$id.'" />';
	$content .='<table width="100%" border="0">';
        $content .='<tr>';
          $content .='<td><img src="'.$_SESSION['config']['img'].'/stats_parameters.gif" alt="'._ADV_OPTIONS.'"  /></td>';
          $content .='<td align="left">';
          $content .='<p>';
            	$content .='<span>'._SHOW_FORM_RESULT.' : </span> <input type="radio" name="form_report" id="report_graph"  value="graph" checked="checked" /> '._GRAPH.' <input type="radio" name="form_report" id="report_array" value="array" /> '. _ARRAY;
            $content .='</p>';
            $content .='<br/>';
           $content .='<p class="double">';
             $content .='<input type="radio" name="type_period" id="period_by_year" value="year" checked="checked" />';
            $content .= _SHOW_YEAR_GRAPH;
	 		$content .='<select name="the_year" id="the_year">';
            $year=date("Y");
			$i_current=date("Y'");
			while ($year <> ($i_current-5))
			{
             	$content .= '<option value = "'.$year.'">'.$year.'</option>';
             	$year= $year-1;
			}
            $content .='</select>';
            $content .='</p>';

             $content .='<p class="double">';
               $content .='<input type="radio" name="type_period" id="period_by_month" value="month" />';
               $content .= _SHOW_GRAPH_MONTH;
   				$content .='<select name="the_month" id="the_month">';
              		$content .='<option value ="01"> '. _JANUARY.' </option>';
                  	$content .='<option value ="02"> '._FEBRUARY.' </option>';
                 	$content .='<option value ="03"> '._MARCH.' </option>';
                 	$content .='<option value ="04"> '._APRIL.' </option>';
                 	$content .='<option value ="05"> '._MAY.' </option>';
                 	$content .='<option value ="06"> '._JUNE.' </option>';
                 	$content .='<option value ="07"> '._JULY.' </option>';
                 	$content .='<option value ="08"> '._AUGUST.' </option>';
                	$content .='<option value ="09"> '._SEPTEMBER.' </option>';
                	$content .='<option value ="10"> '._OCTOBER.'</option>';
                 	$content .='<option value ="11"> '._NOVEMBER.' </option>';
                 	$content .='<option value ="12"> '._DECEMBER.' </option>';
               	$content .='</select>';
	          $content .= _OF_THIS_YEAR.'.</p>';
	   		if($id <> 'process_delay')
	    	{
	           	$content .='<p class="double">';
              	$content .='<input type="radio" id="custom_period" name="type_period" value="custom_period" /><label for="period">'._TITLE_STATS_CHOICE_PERIOD.'.&nbsp;</label>'._SINCE.'&nbsp;:&nbsp;<input name="datestart" type="text"  id="datestart" onclick="showCalender(this);" />&nbsp;'._FOR.'&nbsp;:&nbsp;<input name="dateend" type="text"  id="dateend" onclick="showCalender(this);" /></p>';
        	}
        $content .='</td>';
        $content .='<td><input type="button" name="validate" value="'._VALIDATE.'" class="button" onclick="valid_report_by_period(\''.$_SESSION['config']['businessappurl'].'reports/get_report_by_period_val.php\');" /></td>';
        $content .='</tr>';
       $content .='</table>';
	$content .='</form>';
$content .='</div>';
$content .='<div id="result_period_report"></div>';
$js ='valid_report_by_period(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=reports&page=get_report_by_period_val\');';

echo "{content : '".addslashes($content)."', exec_js : '".addslashes($js)."'}";
exit();
