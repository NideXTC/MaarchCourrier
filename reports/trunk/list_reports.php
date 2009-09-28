<?php
/*
*    Copyright 2009 Maarch
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
* @brief list available reports
*
* @file
* @author Yves Christian KPAKPO <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup reports
*/

session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtomodules']."reports".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_reports.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

//Group Id
if(isset($_REQUEST['group']) && !empty($_REQUEST['group']))
{
	$groupeid = $_REQUEST['group'];
	$admin_reports = new admin_reports();
	$admin_reports->groupreports($groupeid);
}
?>
