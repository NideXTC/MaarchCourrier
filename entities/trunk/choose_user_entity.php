<?php
/**
* File : choose_user_entity.php
*
* Treat the add_users_entities.php form
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  C�dric Ndoumba  <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
*/

$admin = new core_tools();
//$admin->test_admin('manage_entities', 'entities');
$admin->load_lang();

if(!empty($_REQUEST['entity']) && isset($_REQUEST['entity']))
{
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
	$usersent = new users_entities();
	$usersent->connect();

	$usersent->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$_REQUEST['entity']."'");
	$res = $usersent->fetch_object();

	// on retire toute les entit�s filles de l'entit� � ajouter
	$tab = $usersent->getEntityChildren($_REQUEST['entity']);
	$usersent->remove_session($tab);

	$usersent->add_usertmp_to_entity_session( $_REQUEST['entity'], $_REQUEST['role'], $res->entity_label);
}
else
{
	$_SESSION['error'] = _NO_ENTITY_SELECTED."!";
	exit;
}
?>
<script language="javascript">
window.parent.opener.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=users_entities_form';self.close();
</script>
