<?php
/*
*    Copyright 2008,2015 Maarch
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
* File : subfolders_list_by_name.php
*
* List of subfolders for autocompletion
*
* @package  Maarch Framework 3.0
* @version 3
* @since 10/2005
* @license GPL
* @author Laurent Giovannoni <dev@maarch.org>
*/
$db = new Database();

$stmt = $db->query("SELECT doctypes_second_level_label as tag FROM "
	.$_SESSION['tablename']['doctypes_second_level']
	." WHERE lower(doctypes_second_level_label) like lower(?) and enabled = 'Y' ORDER BY doctypes_second_level_label",
	array($_REQUEST['what'].'%'));

//$db->show();
$listArray = array();
while($line = $stmt->fetchObject())
{
	array_push($listArray, $line->tag);
}
echo "<ul>\n";
$authViewList = 0;
foreach($listArray as $what)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    if(stripos($what, $_REQUEST['what']) === 0)
    {
        echo "<li>".functions::xssafe($what)."</li>\n";
		if($flagAuthView)
		{
			echo "<li>...</li>\n";
			break;
		}
		$authViewList++;
    }
}
echo "</ul>";
