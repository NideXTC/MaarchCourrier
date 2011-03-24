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
 * @brief  Advanced search form
 *
 * @file search_adv_invoices.php
 * @author Claire Figueras <dev@maarch.org>
 * @author Loïc Vinet <dev@maarch.org>
 * @date $date$
 * @version $Revision$
 * @ingroup indexing_searching_mlb
 */

require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_request.php");
require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_security.php");
require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_manage_status.php");
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . "class_indexing_searching_app.php");
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . "class_types.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

$_SESSION['search']['plain_text'] = "";
$type = new types();

$func = new functions();
$conn = new dbquery();
$conn->connect();
$search_obj = new indexing_searching_app();
$status_obj = new manage_status();
$sec = new security();
$_SESSION['indexation'] = false;

$mode = 'normal';
if (isset ($_REQUEST['mode']) && !empty ($_REQUEST['mode'])) {
    $mode = $func->wash($_REQUEST['mode'], "alphanum", _MODE);
}
if ($mode == 'normal') {
    $core_tools->test_service('adv_search_mlb', 'apps');
    /****************Management of the location bar  ************/
    $init = false;
    if (isset ($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
        $init = true;
    }
    $level = "";
    if (isset ($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)) {
        $level = $_REQUEST['level'];
    }
    $page_path = $_SESSION['config']['businessappurl'] . 'index.php?page=search_adv_invoices&dir=indexing_searching';
    $page_label = _ADV_SEARCH_INVOICES;
    $page_id = "search_adv_invoices";
    $core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
    /***********************************************************/
}
elseif ($mode == 'popup' || $mode == 'frame') {
    $core_tools->load_html();
    $core_tools->load_header('', true, false);
    $time = $core_tools->get_session_time_expire();
    ?><body>
    <div id="container">

            <div class="error" id="main_error">
                <?php  echo $_SESSION['error'];?>
            </div>
            <div class="info" id="main_info">
                <?php  echo $_SESSION['info'];?>
            </div><?php

}

// load saved queries for the current user in an array
$conn->query("select query_id, query_name from " . $_SESSION['tablename']['saved_queries'] . " where user_id = '" . $_SESSION['user']['UserId'] . "' order by query_name");
$queries = array ();
while ($res = $conn->fetch_object()) {
    array_push($queries, array (
        'ID' => $res->query_id,
        'LABEL' => $res->query_name
    ));
}

$conn->query("select user_id, firstname, lastname, status from " . $_SESSION['tablename']['users'] . " where enabled = 'Y' and status <> 'DEL' order by lastname asc");
$users_list = array ();
while ($res = $conn->fetch_object()) {
    array_push($users_list, array (
        'ID' => $conn->show_string($res->user_id),
        'NOM' => $conn->show_string($res->lastname),
        'PRENOM' => $conn->show_string($res->firstname),
        'STATUT' => $res->status
    ));
}

$coll_id = 'res_coll';
$view = $sec->retrieve_view_from_coll_id($coll_id);
$where = $sec->get_where_clause_from_coll_id($coll_id);
if (!empty ($where)) {
    $where = ' where ' . $where;
}

//Check if web brower is ie_6 or not
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
    $browser_ie = 'true';
    $class_for_form = 'form';
    $hr = '<tr><td colspan="2"><hr></td></tr>';
    $size = '';
}
elseif (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT)) {
    $browser_ie = 'true';
    $class_for_form = 'forms';
    $hr = '';
    $size = '';
} else {
    $browser_ie = 'false';
    $class_for_form = 'forms';
    $hr = '';
    $size = '';
    // $size = 'style="width:40px;"';
}

// building of the parameters array used to pre-load the category list and the search elements
$param = array ();

// Indexes specific to doctype
$indexes = $type->get_all_indexes($coll_id);

for ($i = 0; $i < count($indexes); $i++) {
    $field = $indexes[$i]['column'];
    if (preg_match('/^custom_/', $field)) {
        $field = 'doc_' . $field;
    }
    if ($indexes[$i]['type_field'] == 'select') {
        $arr_tmp = array ();
        array_push($arr_tmp, array (
            'VALUE' => '',
            'LABEL' => _CHOOSE . '...'
        ));
        for ($j = 0; $j < count($indexes[$i]['values']); $j++) {
            array_push($arr_tmp, array (
                'VALUE' => $indexes[$i]['values'][$j]['id'],
                'LABEL' => $indexes[$i]['values'][$j]['label']
            ));
        }
        $arr_tmp2 = array (
            'label' => $indexes[$i]['label'],
            'type' => 'select_simple',
            'param' => array (
                'field_label' => $indexes[$i]['label'],
                'default_label' => '',
                'options' => $arr_tmp
            )
        );
    }
    elseif ($indexes[$i]['type'] == 'date') {
        $arr_tmp2 = array (
            'label' => $indexes[$i]['label'],
            'type' => 'date_range',
            'param' => array (
                'field_label' => $indexes[$i]['label'],
                'id1' => $field . '_from',
                'id2' => $field . '_to'
            )
        );
    } else
        if ($indexes[$i]['type'] == 'string') {
            $arr_tmp2 = array (
                'label' => $indexes[$i]['label'],
                'type' => 'input_text',
                'param' => array (
                    'field_label' => $indexes[$i]['label'],
                    'other' => $size
                )
            );
        } else // integer or float
        {
            $arr_tmp2 = array (
                'label' => $indexes[$i]['label'],
                'type' => 'num_range',
                'param' => array (
                    'field_label' => $indexes[$i]['label'],
                    'id1' => $field . '_min',
                    'id2' => $field . '_max'
                )
            );
        }
    $param[$field] = $arr_tmp2;
}

//Coming date
/*
$arr_tmp2 = array (
    'label' => _DATE_START,
    'type' => 'date_range',
    'param' => array (
        'field_label' => _DATE_START,
        'id1' => 'admission_date_from',
        'id2' => 'admission_date_to'
    )
);
$param['admission_date'] = $arr_tmp2;
*/

//Loaded date
$arr_tmp2 = array (
    'label' => _REG_DATE,
    'type' => 'date_range',
    'param' => array (
        'field_label' => _REG_DATE,
        'id1' => 'creation_date_from',
        'id2' => 'creation_date_to'
    )
);
$param['creation_date'] = $arr_tmp2;


//Document date
$arr_tmp2 = array (
    'label' => _DOC_DATE,
    'type' => 'date_range',
    'param' => array (
        'field_label' => _DOC_DATE,
        'id1' => 'doc_date_from',
        'id2' => 'doc_date_to'
    )
);
$param['doc_date'] = $arr_tmp2;


/*
//destinataire
$arr_tmp = array ();
for ($i = 0; $i < count($users_list); $i++) {
    array_push($arr_tmp, array (
        'VALUE' => $users_list[$i]['ID'],
        'LABEL' => $users_list[$i]['NOM'] . " " . $users_list[$i]['PRENOM']
    ));
}
$arr_tmp2 = array (
    'label' => _PROCESS_RECEIPT,
    'type' => 'select_multiple',
    'param' => array (
        'field_label' => _PROCESS_RECEIPT,
        'label_title' => _CHOOSE_RECIPIENT_SEARCH_TITLE,
        'id' => 'destinataire',
        'options' => $arr_tmp
    )
);
$param['destinataire'] = $arr_tmp2;
*/


//destination (department)
/*
if ($core_tools->is_module_loaded('entities')) {
    $coll_id = 'res_coll';
    $where = $sec->get_where_clause_from_coll_id($coll_id);
    $table = $sec->retrieve_view_from_coll_id($coll_id);
    if (empty ($table)) {
        $table = $sec->retrieve_table_from_coll($coll_id);
    }
    if (!empty ($where)) {
        $where = ' where ' . $where;
    }
    //$conn->query("select distinct r.destination from ".$table." r join ".$_SESSION['tablename']['ent_entities']." e on e.entity_id = r.destination ".$where." group by r.destination ");
    $conn->query("select distinct r.destination, e.short_label from " . $table . " r join " . $_SESSION['tablename']['ent_entities'] . " e on e.entity_id = r.destination " . $where . " group by e.short_label, r.destination order by e.short_label");
    //  $conn->show();
    $arr_tmp = array ();
    while ($res = $conn->fetch_object()) {
        array_push($arr_tmp, array (
            'VALUE' => $res->destination,
            'LABEL' => $res->short_label
        ));
    }

    $param['destination_mu'] = array (
        'label' => _DESTINATION_SEARCH,
        'type' => 'select_multiple',
        'param' => array (
            'field_label' => _DESTINATION_SEARCH,
            'label_title' => _CHOOSE_ENTITES_SEARCH_TITLE,
            'id' => 'services',
            'options' => $arr_tmp
        )
    );

}
*/

// Folder
/*
if ($core_tools->is_module_loaded('folder')) {
    $arr_tmp2 = array (
        'label' => _MARKET,
        'type' => 'input_text',
        'param' => array (
            'field_label' => _MARKET,
            'other' => $size
        )
    );
    $param['market'] = $arr_tmp2;
    $arr_tmp2 = array (
        'label' => _PROJECT,
        'type' => 'input_text',
        'param' => array (
            'field_label' => _PROJECT,
            'other' => $size
        )
    );
    $param['project'] = $arr_tmp2;
}
*/


//status
$status = $status_obj->get_searchable_status();
$arr_tmp = array ();
for ($i = 0; $i < count($status); $i++) {
    array_push($arr_tmp, array (
        'VALUE' => $status[$i]['ID'],
        'LABEL' => $status[$i]['LABEL']
    ));
}
/*
array_push($arr_tmp, array (
    'VALUE' => 'REL1',
    'LABEL' => _FIRST_WARNING
));
array_push($arr_tmp, array (
    'VALUE' => 'REL2',
    'LABEL' => _SECOND_WARNING
));
array_push($arr_tmp, array (
    'VALUE' => 'LATE',
    'LABEL' => _LATE
));
/*
array_push($arr_tmp, array (
    'VALUE' => 'REL1',
    'LABEL' => _FIRST_WARNING
));
array_push($arr_tmp, array (
    'VALUE' => 'REL2',
    'LABEL' => _SECOND_WARNING
));
array_push($arr_tmp, array (
    'VALUE' => 'LATE',
    'LABEL' => _LATE
));
*/

// Sorts the $param['status'] array
function cmp_status($a, $b) {
    return strcmp(strtolower($a["LABEL"]), strtolower($b["LABEL"]));
}
usort($arr_tmp, "cmp_status");
$arr_tmp2 = array (
    'label' => _STATUS_PLUR,
    'type' => 'select_multiple',
    'param' => array (
        'field_label' => _STATUS,
        'label_title' => _CHOOSE_STATUS_SEARCH_TITLE,
        'id' => 'status',
        'options' => $arr_tmp
    )
);
$param['status'] = $arr_tmp2;

//doc_type
/*$conn->query("select type_id, description  from  " . $_SESSION['tablename']['doctypes'] . " where enabled = 'Y' order by description asc");
$arr_tmp = array ();
while ($res = $conn->fetch_object()) {
    array_push($arr_tmp, array (
        'VALUE' => $res->type_id,
        'LABEL' => $conn->show_string($res->description)
    ));
}
$arr_tmp2 = array (
    'label' => _DOCTYPES,
    'type' => 'select_multiple',
    'param' => array (
        'field_label' => _DOCTYPE,
        'label_title' => _CHOOSE_DOCTYPES_SEARCH_TITLE,
        'id' => 'doctypes',
        'options' => $arr_tmp
    )
);
$param['doctype'] = $arr_tmp2;
*/

/*
//category
$arr_tmp = array ();
array_push($arr_tmp, array (
    'VALUE' => '',
    'LABEL' => _CHOOSE_CATEGORY
));
foreach (array_keys($_SESSION['mail_categories']) as $cat_id) {
    array_push($arr_tmp, array (
        'VALUE' => $cat_id,
        'LABEL' => $_SESSION['mail_categories'][$cat_id]
    ));
}
$arr_tmp2 = array (
    'label' => _CATEGORY,
    'type' => 'select_simple',
    'param' => array (
        'field_label' => _CATEGORY,
        'default_label' => '',
        'options' => $arr_tmp
    )
);
$param['category'] = $arr_tmp2; //Arbox_id ; for physical_archive
*
*
*
*/
/*
if ($core_tools->is_module_loaded('physical_archive') == true) {
    //arbox_id
    $conn->query("select arbox_id, title from  " . $_SESSION['tablename']['ar_boxes'] . " where status <> 'DEL' order by description asc");
    $arr_tmp = array ();
    while ($res = $conn->fetch_object()) {
        array_push($arr_tmp, array (
            'VALUE' => $res->arbox_id,
            'LABEL' => $conn->show_string($res->title)
        ));
    }
    $arr_tmp2 = array (
        'label' => _ARBOXES,
        'type' => 'select_multiple',
        'param' => array (
            'field_label' => _ARBOXES,
            'label_title' => _CHOOSE_BOXES_SEARCH_TITLE,
            'id' => 'arboxes',
            'options' => $arr_tmp
        )
    );
    $param['arbox_id'] = $arr_tmp2;

    $arr_tmp2 = array (
        'label' => _ARBATCHES,
        'type' => 'input_text',
        'param' => array (
            'field_label' => _ARBATCHES,
            'other' => $size
        )
    );
    $param['arbatch_id'] = $arr_tmp2;

}
*/


// Sorts the param array
function cmp($a, $b) {
    return strcmp(strtolower($a["label"]), strtolower($b["label"]));
}
uasort($param, "cmp");

$tab = $search_obj->send_criteria_data($param);
//$conn->show_array($param);
//$conn->show_array($tab);

// criteria list options
$src_tab = $tab[0];

$core_tools->load_js();
?>
<?php

?>


<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=search_adv.js" ></script>
<script type="text/javascript">
<!--
var valeurs = { <?php echo $tab[1];?>};
var loaded_query = <?php

if (isset ($_SESSION['current_search_query']) && !empty ($_SESSION['current_search_query'])) {
    echo $_SESSION['current_search_query'];
} else {
    echo '{}';
}
?>;

function del_query_confirm()
{
    if(confirm('<?php echo _REALLY_DELETE.' '._THIS_SEARCH.'?';?>'))
    {
        del_query_db($('query').options[$('query').selectedIndex], 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');
        return false;
    }
}
-->
</script>

<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_search_b.gif" alt="" /> <?php echo _ADV_SEARCH_INVOICES; ?></h1>
<div id="inner_content">

<?php

if (count($queries) > 0) {
    ?>
<form name="choose_query" id="choose_query" action="#" method="post" >
<div align="center" style="display:block;" id="div_query">

<label for="query"><?php echo _MY_SEARCHES;?> : </label>
<select name="query" id="query" onchange="load_query_db(this.options[this.selectedIndex].value, 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');return false;" >
    <option id="default_query" value=""><?php echo _CHOOSE_SEARCH;?></option>
    <?php

for ($i = 0; $i < count($queries); $i++) {
    ?><option value="<?php echo $queries[$i]['ID'];?>" id="query_<?php echo $queries[$i]['ID'];?>"><?php echo $queries[$i]['LABEL'];?></option><?php }?>
</select>

<input name="del_query" id="del_query" value="<?php echo _DELETE_QUERY;?>" type="button"  onclick="del_query_confirm();" class="button" style="display:none" />
</div>
</form>
<?php } ?>
<form name="frmsearch2" method="get" action="<?php if($mode == 'normal') {echo $_SESSION['config']['businessappurl'].'index.php'; } elseif($mode == 'frame' || $mode == 'popup'){ echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result_invoices';}?>"  id="frmsearch2" class="<?php echo $class_for_form; ?>">
<input type="hidden" name="dir" value="indexing_searching" />
    <input type="hidden" name="page" value="search_adv_result_invoices" />
<input type="hidden" name="mode" value="<?php echo $mode;?>" />
<?php if($mode == 'frame' || $mode == 'popup'){?>
    <input type="hidden" name="display" value="true" />
    <input type="hidden" name="action_form" value="<?php echo $_REQUEST['action_form'];?>" />
    <input type="hidden" name="modulename" value="<?php echo $_REQUEST['modulename'];?>" />
<?php

}
if (isset ($_REQUEST['nodetails'])) {
    ?>
<input type="hidden" name="nodetails" value="true" />
<?php

}
?>
<table align="center" border="0" width="100%">
    <tr>
        <td align="left"><a href="#" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();"><img src="<?php  echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";?>" alt="<?php echo _CLEAR_SEARCH;?>" /> <?php  echo _CLEAR_SEARCH; ?></a></td>
        <td  width="75%" align="right" ><?php /* if($core_tools->is_module_loaded("basket") == true){?><span class="bold"><?php echo _SPREAD_SEARCH_TO_BASKETS;?></span>
            <input type="hidden" name="meta[]" value="baskets_clause#baskets_clause_false,baskets_clause_true#radio" />
            <input type="radio" name="baskets_clause" id="baskets_clause_false" class="check"  value="false" checked="checked" /><?php echo _NO;?>
            <input type="radio" name="baskets_clause" id="baskets_clause_true" class="check"  value="true"  /><?php echo _YES; }*/?>
        </td>
    </tr>
</table>
<table align="center" border="0" width="100%">
    <tr>
        <td colspan="2" ><h2><?php echo _FILE_DATA; ?></h2></td>
    </tr>
    <tr >
        <td >
        <div class="block">
            <table border = "0" width="100%">
                <!--<tr>
                    <td width="40%"><label for="type" class="bold" ><?php echo _DOCTYPE;?>:</label></td>
                        <td></td>
                        <td><select name="type" id="type">
                        <option value=""><?php echo _ALL_DOCTYPES; ?></option>
                        <?php
                        foreach($param['doctype']['param']['options'] as $type){
                            echo '<option value="'.$type['VALUE'].'">'.$type['LABEL'].'</option>';
                        }
                        ?>
                        </select>

                        <input type="hidden" name="meta[]" value="type#type#select_simple" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>-->
                <!--
                <tr>
                    <td width="40%"><label for="subject" class="bold" ><?php echo _NUMMDT;?>:</label></td>
                        <td></td>
                        <td><input type="text" name="nummdt" id="nummdt" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="nummdt#nummdt#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>
                 <tr>
                    <td width="40%"><label for="subject" class="bold" ><?php echo _LIBMDT;?>:</label></td>
                        <td></td>
                        <td><input type="text" name="libmdt" id="libmdt" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="libmdt#libmdt#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>
                <tr>
                    <td width="40%"><label for="subject" class="bold" ><?php echo _NUMCPT;?>:</label></td>
                        <td></td>
                        <td><input type="text" name="numcpt" id="numcpt" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="numcpt#numcpt#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>
                <tr>
                    <td width="40%"><label for="subject" class="bold" ><?php echo _LIBCPT;?>:</label></td>
                        <td></td>
                        <td><input type="text" name="libcpt" id="libcpt" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="libcpt#libcpt#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>

                <tr>
                    <td width="40%"><label for="subject" class="bold" ><?php echo _LIBTRAIT;?>:</label></td>
                        <td></td>
                        <td><input type="text" name="libtrt" id="libtrt" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="libtrt#libtrt#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>
-->
                <tr>
                    <td width="40%"><label for="numged" class="bold"><?php echo _N_GED;?>:</label>
                        <td ></td>
                        <td><input type="text" name="numged" id="numged" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="numged#numged#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>
                <!--<tr>
                    <td width="40%"><label for="numged" class="bold"><?php echo _IDENTIFIER;?>:</label>
                        <td ></td>
                        <td><input type="text" name="identifier" id="identifier" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="identifier#identifier#input_text" />
                    </td>
                    <td><em><?php echo ''; ?></em></td>
                </tr>-->
            </table>
            </div>
            <div class="block_end">&nbsp;</div>
        </td>
        <td>
            <p align="center">
            <input class="button_search_adv" name="imageField" type="button" value="" onclick="valid_search_form('frmsearch2');this.form.submit();"  />
            <input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH; ?>" onclick="valid_search_form('frmsearch2');this.form.submit();" /></p>
         </td>
    </tr>
    <tr><td colspan="2"><hr/></td></tr>
<tr>
<td  >
<div class="block">
 <table border = "0" width="100%">
       <tr>
     <td width="70%">
        <label class="bold"><?php echo _ADD_PARAMETERS; ?>:</label>
        <select name="select_criteria" id="select_criteria" style="display:inline;" onchange="add_criteria(this.options[this.selectedIndex].id, 'frmsearch2', <?php echo $browser_ie;?>, '<?php echo _ERROR_IE_SEARCH;?>');">
            <?php echo $src_tab; ?>
        </select>
     </td>

        <td width="30%"><em><?php echo _ADD_PARAMETERS_HELP; ?></em></td>
        </tr>
 </table>
 </div>
 <div class="block_end">&nbsp;</div>
</td></tr>
</table>

</form>
<br/>
<div align="right">
<!--<input class="button" name="submit" type="button" value="<?php echo _SEARCH;?>" onclick="valid_search_form('frmsearch2');document.getElementById('frmsearch2').submit();"  />-->
 <!--<input class="button" name="clear" type="button" value="<?php echo _CLEAR_SEARCH;?>" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();"  />-->
</div>
 </div>
<script type="text/javascript">
load_query(valeurs, loaded_query, 'frmsearch2', '<?php echo $browser_ie;?>, <?php echo _ERROR_IE_SEARCH;?>');
<?php

if (isset ($_REQUEST['init_search'])) {
    ?>clear_search_form('frmsearch2','select_criteria');clear_q_list(); <?php

}
?>
</script>
<?php

if ($mode == 'popup' || $mode == 'frame') {
    echo '</div>';
    if ($mode == 'popup') {
        ?><br/><div align="center"><input type="button" name="close" class="button" value="<?php echo _CLOSE_WINDOW;?>" onclick="self.close();" /></div> <?php

}
echo '</body></html>';
}
