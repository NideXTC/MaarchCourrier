<?php
/*
*   Copyright 2008 - 2015 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

try {
    include('Maarch_CLITools/ArgsParser.php');
    include('LoggerLog4php.php');
    include('Maarch_CLITools/FileHandler.php');
    include('Maarch_CLITools/ConsoleHandler.php');
} catch (IncludeFileError $e) {
    echo 'Maarch_CLITools required ! \n (pear.maarch.org)\n';
    exit(106);
}
// Load tools
include('batch_tools.php');

$GLOBALS['batchName'] = 'thumbnails';
$GLOBALS['wb'] = '';
$GLOBALS['lckFile'] = '';
$totalProcessedResources = 0;

// Open Logger
$GLOBALS['logger'] = new Logger4Php();
$GLOBALS['logger']->set_threshold_level('DEBUG');

$logFile = 'log/' . date('Y-m-d_H-i-s') . '.log';

$file = new FileHandler($logFile);
$GLOBALS['logger']->add_handler($file);

//error mode and function
error_reporting(E_ERROR);
set_error_handler(errorHandler);
// global vars of the program
/**
* Name of the config (usefull for multi instance)
*/
$GLOBALS['config_name'] = "";
/**
* Path to the log file
*/
$GLOBALS['log'] = "";
/**
* User exit of the program, contains 1 if any problem appears
*/
$GLOBALS['ErrorLevel'] = 0;
/**
* Connection object to database 1
*/
$GLOBALS['db'] = "";

// Class to manage files includes errors
class IncludeFileError extends Exception
{
    public function __construct($file)
    {
        $this->file = $file;
        parent::__construct("Include File \"$file\" is missing!", 1);
    }
}

function MyInclude($file)
{
    if (file_exists($file)) {
        include_once($file);
    } else {
        throw new IncludeFileError($file);
    }
}

function r_mkdir($path, $mode = 0777, $recursive = true) {
	if(empty($path))
		return false;
	 
	if($recursive) {
		$toDo = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
		if($toDo !== '.' && $toDo !== '..')
			r_mkdir($toDo, $mode);
	}
	 
	if(!is_dir($path))
		mkdir($path, $mode);
	 
		return true;
}

/**
* Managing of errors
* @param  $errno integer number of the error
* @param  $errstr string text of the error
* @param  $errfile string file concerned with the error
* @param  $errline integer line of the error
* @param  $errcontext string context of the error
*/
function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    $GLOBALS['logger']->write('from  line ' . $errline . ' : ' . $errstr, 'WARNING', 1);
    $GLOBALS['errorLevel'] = 1;
}

// Begin
date_default_timezone_set('Europe/Paris');
if ($argc != 2) {
    echo "You must specify the configuration file." . $argc;
    exit;
}
$conf = $argv[1];
;
if(!file_exists($conf)){
	$GLOBALS['logger']->write("Can't load xml config file ! (".$conf.")","ERROR");
	exit();
}else{
	$xmlconfig = simplexml_load_file($conf);
}
foreach ($xmlconfig->CONFIG as $CONFIG) {
    $GLOBALS['config_name'] = $CONFIG->CONFIG_NAME;
   
    $GLOBALS['tablename'] = $CONFIG->TABLE_NAME;
    $GLOBALS['collection'] = $CONFIG->COLLECTION;
	
	$GLOBALS['max_batch_size'] = $CONFIG->MAX_BATCH_SIZE;
	$maarchDirectory = (string) $CONFIG->MaarchDirectory;
	$GLOBALS['core_path'] = $maarchDirectory . 'core' . DIRECTORY_SEPARATOR;
}
$GLOBALS['databasetype'] = $xmlconfig->CONFIG_BASE->databasetype;

$log4phpParams = $xmlconfig->LOG4PHP;
if ((string) $log4phpParams->enabled == 'true') {
    $GLOBALS['logger']->set_log4PhpLibrary(
        $maarchDirectory . 'apps/maarch_entreprise/tools/log4php/Logger.php'
    );
    $GLOBALS['logger']->set_log4PhpLogger((string) $log4phpParams->Log4PhpLogger);
    $GLOBALS['logger']->set_log4PhpBusinessCode((string) $log4phpParams->Log4PhpBusinessCode);
    $GLOBALS['logger']->set_log4PhpConfigPath((string) $log4phpParams->Log4PhpConfigPath);
    $GLOBALS['logger']->set_log4PhpBatchName('thumbnails');
}

$GLOBALS['logger']->write("Launch of process of thumbnails conversion");
$GLOBALS['logger']->write("Loading the xml config file");
$GLOBALS['logger']->write("Config name : " . $GLOBALS['config_name']);
$GLOBALS['logger']->write("Conversion launched for table : " . $GLOBALS['tablename']);

require($GLOBALS['core_path']."class/class_functions.php");
require($GLOBALS['core_path']."class/class_db_pdo.php");
	
$GLOBALS['db'] = new Database($conf);


Bt_getWorkBatch();

$query = "select priority_number, docserver_id from docservers where is_readonly = 'N' and "
	   . " enabled = 'Y' and coll_id = ? and docserver_type_id = 'TNL' order by priority_number";
	   
$stmt1 = $GLOBALS['db']->query($query, array($GLOBALS['collection']));
if($res = $stmt1->fetchObject()){
	$docserverId = $res->docserver_id;
}else{
	$docserverId='';
}

if($docserverId <> ''){
	$GLOBALS['logger']->write("TNL docserver found !");
}else{
	$GLOBALS['logger']->write("TNL docserver not found ! (query : ".$query, "ERROR");
	exit();
}

$docserversList = array();
$docServers = "select docserver_id, path_template, device_label from docservers";
$stmt1 = $GLOBALS['db']->query($docServers);
while ($queryResult = $stmt1->fetchObject()) {
  $pathToDocServer[$queryResult->docserver_id] = $queryResult->path_template;
  $docserversList[] = $queryResult->docserver_id;
}
$docserversList = implode(', ', $docserversList);
//$GLOBALS['logger']->write("List of docServers : " . $docserversList);


if (is_dir($pathToDocServer[(string)$docserverId])){
	$pathOutput = $pathToDocServer[(string)$docserverId];
	$GLOBALS['logger']->write("TNL path: ".$pathOutput);
}
else {
	$pathOutput = $pathToDocServer[(string)$docserverId];
	$GLOBALS['logger']->write("Wrong TNL path ! (".$pathOutput.")","ERROR");
	exit();
}
$cpt_batch_size=0;

$queryMakeThumbnails = "select res_id, docserver_id, path, filename, format from "
    . $GLOBALS['tablename'] . " where tnl_filename = '' or tnl_filename is null ";

$stmt1 = $GLOBALS['db']->query($queryMakeThumbnails);

if ($stmt1->rowCount() === 0) {
    Bt_exitBatch(0, 'No document to process');
}else{
	$GLOBALS['logger']->write($stmt1->rowCount()." document(s) to process...");
}

$i = 1;
$err = 0;
$errInfo = '';
while ($queryResult=$stmt1->fetchObject()) {
	if ($GLOBALS['max_batch_size'] >= $cpt_batch_size) {
		$fileFormat = $queryResult->format;
		$pathToFile = $pathToDocServer[$queryResult->docserver_id] 
			. str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path)
        	. $queryResult->filename;
		$outputPathFile  = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path) 
			. str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult->filename);
		
		$GLOBALS['logger']->write('Process n°'.$i.'/'.$stmt1->rowCount().' (RES_ID => '.$queryResult->res_id.', FORMAT => '.$fileFormat.', PATH => '.$pathToFile.')');
    	
		
       	if (
       		strtoupper($fileFormat) <> 'PDF' 
       		&& strtoupper($fileFormat) <> 'HTML'
       		&& strtoupper($fileFormat) <> 'MAARCH'
       	) {
			$errTxt = "file format not allowed : " . $fileFormat;
			$errInfo = ' (Last Error : '.$errTxt.')';

			$stmt2 = $GLOBALS['db']->query("UPDATE ".$GLOBALS['tablename']." SET tnl_path = 'ERR', tnl_filename = 'ERR' WHERE res_id = ?", array($queryResult->res_id));
		   	$GLOBALS['logger']->write('document not converted ! ('.$errTxt.')',"ERROR");
			$err++;
       	} else {
			$racineOut = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path);
			if (!is_dir($racineOut)){
				r_mkdir($racineOut,0777);
				$GLOBALS['logger']->write("Create $racineOut directory ");
			}
			
			$command = '';
			if (strtoupper($fileFormat) == 'PDF') {
				$command = "convert -thumbnail 400x600 -background white -alpha remove " . escapeshellarg($pathToFile) . "[0] "
					. escapeshellarg($outputPathFile);
			} else {
				$posPoint = strpos($pathToFile, '.');
				$extension = substr($pathToFile, $posPoint);
				$chemin = substr($pathToFile, 0, $posPoint);
				if($extension == '.maarch'){
					if (!copy($pathToFile, $chemin.'.html')) {
					    echo "La copie $pathToFile du fichier a échoué...\n";
					}else{
						echo "La copie $pathToFile du fichier a réussi...\n";
						$cheminComplet = $chemin.".html";
						$command = "wkhtmltoimage --width 400 --height 600 --quality 100 --zoom 0.2 " . escapeshellarg($cheminComplet) . " "
						. escapeshellarg($outputPathFile);

					}
				}else{
					$command = "wkhtmltoimage --width 400 --height 600 --quality 100 --zoom 0.2 " . escapeshellarg($pathToFile) . " "
					. escapeshellarg($outputPathFile);
				}
			}
			exec($command.' 2>&1', $output, $result);
			if($result > 0)
			{
			   $err++;
			   $errInfo = ' (Last Error : '.$output[0].')';
			   $GLOBALS['logger']->write('document not converted ! ('.$output[0].')',"ERROR");
			}else{
				$stmt2 = $GLOBALS['db']->query("UPDATE ".$GLOBALS['tablename']." SET tnl_path = ?, tnl_filename = ? WHERE res_id = ?", array($queryResult->path, str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult->filename), $queryResult->res_id));	
				$GLOBALS['logger']->write('document converted');
			}
		}	
	} else {
		$converted_doc = $i - $err;
        $GLOBALS['logger']->write($converted_doc.' document(s) converted, MAX BATCH SIZE EXCEDEED !'.$errInfo);
        Bt_logInDataBase(
		    $i, $err, $i.' document(s) converted, MAX BATCH SIZE EXCEDEED !'.$errInfo
		);
        break;
  }
  $i++;
  $cpt_batch_size++;
}
$i--;

$converted_doc = $i - $err;
$GLOBALS['logger']->write($converted_doc.' document(s) converted');

Bt_logInDataBase(
    $i, $err, $converted_doc.' document(s) converted'.$errInfo
);

Bt_updateWorkBatch();
$GLOBALS['logger']->write("End of application !");

exit();
