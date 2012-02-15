<?php
/**
* File : deco.php
*
* use this to terminate your session
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once 'core/class/class_history.php';
require_once 'core/core_tables.php';
$core = new core_tools();
$core->load_lang();
setcookie("maarch", "", time() - 3600000);
$_SESSION['error'] = _NOW_LOGOUT;
if (isset($_GET['abs_mode'])) {
    $_SESSION['error'] .= ', ' . _ABS_LOG_OUT;
}

if ($_SESSION['history']['userlogout'] == "true"
    && isset($_SESSION['user']['UserId'])
) {
    $hist = new history();
    $ip = $_SERVER['REMOTE_ADDR'];
    $navigateur = addslashes($_SERVER['HTTP_USER_AGENT']);
    //$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $host = $_SERVER['REMOTE_ADDR'];
    $hist->add(
        USERS_TABLE, $_SESSION['user']['UserId'], "LOGOUT",'userlogout',
        _LOGOUT_HISTORY . ' '. $_SESSION['user']['UserId'] . ' IP : ' . $ip,
        $_SESSION['config']['databasetype']
    );
}
$custom = $_SESSION['custom_override_id'];
$corePath = $_SESSION['config']['corepath'];
$appUrl = $_SESSION['config']['businessappurl'];
$appId = $_SESSION['config']['app_id'];
$_SESSION = array();
$_SESSION['custom_override_id'] = $custom;
$_SESSION['config']['corepath'] = $corePath ;
$_SESSION['config']['app_id'] = $appId ;

if (isset($_GET['logout']) && $_GET['logout']) {
    $logoutExtension = "&logout=true";
} else {
    $logoutExtension = "";
}
header(
	"location: " . $appUrl . "index.php?display=true&page=login"
    . $logoutExtension . "&coreurl=" . $_GET['coreurl']
);
exit();

