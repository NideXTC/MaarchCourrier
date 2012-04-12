<?php

include_once '../../core/init.php';
require_once 'core/class/class_portal.php';
require_once 'core/class/class_functions.php';
require_once 'core/class/class_db.php';
require_once 'core/class/class_core_tools.php';
require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';
require_once 'core/class/class_history.php';
require_once 'core/class/SecurityControler.php';
require_once 'core/class/class_resource.php';
require_once 'core/class/docservers_controler.php';
require_once 'core/docservers_tools.php';
require_once 'modules/content_management/class/class_content_manager_tools.php';

if (
    !isset($_SESSION['user']['UserId'])
    && empty($_SESSION['user']['UserId'])
) {
    //only for the test with the java editor
    include_once 'modules/content_management/autolog_for_test.php';
}

//Create XML
function createXML($rootName, $parameters)
{
    if ($rootName == 'ERROR') {
        $cM = new content_management_tools();
        $cM->closeReservation($_SESSION['cm']['reservationId']);
    }
    global $debug, $debugFile;
    $rXml = new DomDocument("1.0","UTF-8");
    $rRootNode = $rXml->createElement($rootName);
    $rXml->appendChild($rRootNode);
    if (is_array($parameters)) {
        foreach ($parameters as $kPar => $dPar) {
            $node = $rXml->createElement($kPar,$dPar);
            $rRootNode->appendChild($node);
        }
    } else {
        $rRootNode->nodeValue = $parameters;
    }
    if ($debug) {
        $rXml->save($debugFile);
    }
    header("content-type: application/xml");
    echo $rXml->saveXML();
    /* for the tests only
    $text = $rXml->saveXML();
    $inF = fopen('wsresult.log','a');
    fwrite($inF, $text);
    fclose($inF);*/
    exit;
}

//test if session is loaded
/*
createXML('ERROR', 'SESSION INFO ####################################'
    . $_SESSION['user']['UserId']
);
*/

$status = 'ko';
$objectType = '';
$objectTable = '';
$objectId = '';
$appPath = '';
$fileContent = '';
$fileExtension = '';
$error = '';

$cM = new content_management_tools();

if (
    !empty($_REQUEST['action'])
    && !empty($_REQUEST['objectType'])
    && !empty($_REQUEST['objectTable'])
    && !empty($_REQUEST['objectId'])
) {
    $objectType = $_REQUEST['objectType'];
    $objectTable = $_REQUEST['objectTable'];
    $objectId = $_REQUEST['objectId'];
    if ($_REQUEST['action'] == 'editObject') {
        //createXML('ERROR', $objectType . ' ' . $objectId);
        $core_tools = new core_tools();
        $core_tools->test_user();
        $core_tools->load_lang();
        $function = new functions();
        if (
            $objectType <> 'template' 
            && $objectType <> 'templateStyle'
            && $objectType <> 'attachmentFromTemplate'
            && $objectType <> 'attachment'
        ) {
            //case of res -> master or version
            include 'modules/content_management/retrieve_res_from_cm.php';
        } elseif ($objectType == 'attachment') {
            //case of res -> update attachment
            include 'modules/content_management/retrieve_attachment_from_cm.php';
        } else {
            //case of template, templateStyle, or new attachment generation
            include 'modules/content_management/retrieve_template_from_cm.php';
        }
        $status = 'ok';
        //$appPath = 'C:\programmes\openoffice\program\soffice.exe';
        $appPath = 'start';
        $content = file_get_contents($filePathOnTmp, FILE_BINARY);
        $encodedContent = base64_encode($content);
        $fileContent = $encodedContent;
        $result = array(
            'STATUS' => $status,
            'OBJECT_TYPE' => $objectType,
            'OBJECT_TABLE' => $objectTable,
            'OBJECT_ID' => $objectId,
            'APP_PATH' => $appPath,
            'FILE_CONTENT' => $fileContent,
            'FILE_EXTENSION' => $fileExtension,
            'ERROR' => '',
            'END_MESSAGE' => '',
        );
        unlink($filePathOnTmp);
        createXML('SUCCESS', $result);
    } elseif ($_REQUEST['action'] == 'saveObject') {
        if (
            !empty($_REQUEST['fileContent'])
            && !empty($_REQUEST['fileExtension'])
        ) {
            $fileEncodedContent = str_replace(
                ' ',
                '+',
                $_REQUEST['fileContent']
            );
            $fileExtension = $_REQUEST['fileExtension'];
            $fileContent = base64_decode($fileEncodedContent);
            //copy file on Maarch tmp dir
            $tmpFileName = 'cm_tmp_file_' . $_SESSION['user']['UserId']
                . '_' . rand() . '.' . strtolower($fileExtension);
            $inF = fopen($_SESSION['config']['tmppath'] . $tmpFileName, 'w');
            fwrite($inF, $fileContent);
            fclose($inF);
            $arrayIsAllowed = array();
            $arrayIsAllowed = Ds_isFileTypeAllowed(
                $_SESSION['config']['tmppath'] . $tmpFileName
            );
            if ($arrayIsAllowed['status'] == false) {
                $result = array(
                    'ERROR' => _WRONG_FILE_TYPE
                    . ' ' . $arrayIsAllowed['mime_type']
                );
                createXML('ERROR', $result);
            } else {
                //depending on the type of object, the action is not the same
                if ($objectType == 'resource') {
                    include 'modules/content_management/save_new_version_from_cm.php';
                } elseif ($objectType == 'attachmentFromTemplate') {
                    include 'modules/content_management/save_attach_res_from_cm.php';
                } elseif ($objectType == 'attachment') {
                    include 'modules/content_management/save_attach_from_cm.php';
                } elseif ($objectType == 'templateStyle' || $objectType == 'template') {
                    include 'modules/content_management/save_template_from_cm.php';
                }
                //THE RETURN
                if (!empty($_SESSION['error'])) {
                    $result = array(
                        'END_MESSAGE' => $_SESSION['error'] . _END_OF_EDITION,
                    );
                    createXML('ERROR', $result);
                } else {
                    $cM->closeReservation($_SESSION['cm']['reservationId']);
                    $result = array(
                        'END_MESSAGE' => _UPDATE_OK,
                    );
                    createXML('SUCCESS', $result);
                }
            }
        } else {
            $result = array(
                'ERROR' => _FILE_CONTENT_OR_EXTENSION_EMPTY,
            );
            createXML('ERROR', $result);
        }
        //$cM->closeReservation($_SESSION['cm']['reservationId']);
    }
} else {
    $result = array(
        'STATUS' => $status,
        'OBJECT_TYPE' => $objectType,
        'OBJECT_TABLE' => $objectTable,
        'OBJECT_ID' => $id,
        'APP_PATH' => $appPath,
        'FILE_CONTENT' => $fileContent,
        'FILE_EXTENSION' => $fileExtension,
        'ERROR' => 'missing parameters',
        'END_MESSAGE' => '',
    );
    createXML('ERROR', $result);
}