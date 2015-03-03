<?php
/**
* File : view_attachement.php
*
* View a document
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once "core/class/class_security.php";
require_once 'modules/attachments/attachments_tables.php';
require_once 'core/core_tables.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_indexing_searching_app.php';
require_once "core/class/class_history.php";

$core = new core_tools();
$core->test_user();
$core->load_lang();
$function = new functions();
$sec = new security();
$_SESSION['error'] = "";
if (isset($_GET['id'])) {
    $sId = $_GET['id'];
} else {
    $sId = "";
}
$sId = $function->wash($_GET['id'], "num", _THE_DOC);
if (! empty($_SESSION['error'])) {
    header("location: " . $_SESSION['config']['businessappurl'] . "index.php");
    exit();
} else {
    $db = new dbquery();
    $db->connect();

    $db->query(
        "SELECT coll_id, res_id_master 
            FROM res_view_attachments 
            WHERE (res_id = " . $sId . " OR res_id_version = ".$sId.") AND res_id_master = ".$_REQUEST['res_id_master']
            ." ORDER BY relation desc"
    );
    $res = $db->fetch_object();
    $collId = $res->coll_id;
    $resIdMaster = $res->res_id_master;

    $where2 = "";
    foreach (array_keys($_SESSION['user']['security']) as $key) {
        if ($collId == $key) {
            $where2 = " and ( " . $_SESSION['user']['security'][$key]['DOC']['where']
                    . " ) ";
        }
    }

    $table = $sec->retrieve_table_from_coll($collId);
    $db->query(
        "SELECT res_id FROM " . $table . " WHERE res_id = " . $resIdMaster
    );
    //$db->show();
    if ($db->nb_result() == 0) {
        $_SESSION['error'] = _THE_DOC . " " . _EXISTS_OR_RIGHT . "&hellip;";
        header(
            "location: " . $_SESSION['config']['businessappurl'] . "index.php"
        );
        exit();
    } else {
        $db->query(
            "SELECT docserver_id, path, filename, format 
                FROM res_view_attachments 
                WHERE (res_id = " . $sId . " OR res_id_version = ".$sId.") AND res_id_master = ".$_REQUEST['res_id_master']
                        ." ORDER BY relation desc"
        );

        if ($db->nb_result() == 0) {
            $_SESSION['error'] = _THE_DOC . " " . _EXISTS_OR_RIGHT . "&hellip;";
            header(
                "location: " . $_SESSION['config']['businessappurl']
                . "index.php"
            );
            exit();
        } else {
            $line = $db->fetch_object();
            $docserver = $line->docserver_id;
            $path = $line->path;
            $filename = $line->filename;
            $format = $line->format;
            $db->query(
                "select path_template from " . _DOCSERVERS_TABLE_NAME
                . " where docserver_id = '" . $docserver . "'"
            );
            //$db->show();
            $lineDoc = $db->fetch_object();
            $docserver = $lineDoc->path_template;
            $file = $docserver . $path . $filename;
            $file = str_replace("#", DIRECTORY_SEPARATOR, $file);

            if (strtoupper($format) == "MAARCH") {
                if (file_exists($file)) {
                    $myfile = fopen($file, "r");

                    $data = fread($myfile, filesize($file));
                    fclose($myfile);
                    $content = stripslashes($data);
                    $core->load_html();
                    $core->load_header();
                    ?>
                    <body id="validation_page" onload="javascript:moveTo(0,0);resizeTo(screen.width, screen.height);">
                    <div id="model_content" style="width:100%;"  >

                    <?php  echo $content;?>

                    </div>
                    </body>
                    </html> <?php
                } else {
                    $_SESSION['error'] = _NO_DOC_OR_NO_RIGHTS . "...";
                    ?><script type="text/javascript">window.opener.top.location.href='index.php';self.close();</script><?php
                }
            } else {
                require_once 'core/docservers_tools.php';
                $arrayIsAllowed = array();
                $arrayIsAllowed = Ds_isFileTypeAllowed($file);
                if ($arrayIsAllowed['status']) {
                    if ($_SESSION['history']['attachview'] == "true") {
                        $hist = new history();
                        $hist->add(
                            $table, $sId, "VIEW", 'attachview', _VIEW_DOC_NUM . "" . $sId,
                            $_SESSION['config']['databasetype'], 'apps'
                        );
                    }
                    header("Pragma: public");
                    header("Expires: 0");
                    header(
                        "Cache-Control: must-revalidate, post-check=0, pre-check=0"
                    );
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Type: " . $arrayIsAllowed['mime_type']);
                    header(
                        "Content-Disposition: inline; filename="
                        . basename('maarch.' . $format) . ";"
                    );
                    header("Content-Transfer-Encoding: binary");
                    readfile($file);
                    exit();
                } else {
                    echo _FORMAT . ' ' . _UNKNOWN;
                    exit();
                }
            }
        }
    }
}
