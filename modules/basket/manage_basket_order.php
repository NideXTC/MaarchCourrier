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
* @brief   Manage basket order in home page
*
*
* @file
* @author  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
$admin = new core_tools();
$admin->test_admin('admin_baskets', 'basket');

require_once 'modules/basket/class/class_admin_basket.php';
 /****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
$level = '';
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=manage_basket_order&module=basket';
$page_label = _MANAGE_BASKET_ORDER;
$page_id = 'manage_basket_order';
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$func = new functions();

$_SESSION['origin'] = 'manage_basket_order';
$bask = new admin_basket();

if ($_GET['mode'] == "reload") {
	$bask->manageBasketOrder(false);
} else {
	$bask->manageBasketOrder();
}

?>
