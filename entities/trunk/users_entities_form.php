<?php
/**
* File : users_entities_form.php
*
* Form to choose a entity in the user_entities management (iframe included in the user_entities management)
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  C�dric Ndoumba  <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
*/
core_tools::load_lang();
?>
<div class="block">
<form name="userEntity" method="get" action="#" >

 <h2 class="tit"> <?php  echo _USER_ENTITIES_TITLE; ?> :</h2>

<?php

	if(empty($_SESSION['m_admin']['entity']['entities'])   )
	{
		echo _USER_BELONGS_NO_ENTITY.".<br/>";
	}
	else
	{
		for($theline = 0; $theline < count($_SESSION['m_admin']['entity']['entities']) ; $theline++)
		{
				if( $_SESSION['m_admin']['entity']['entities'][$theline]['PRIMARY'] == 'Y')
				{
					?><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=arrow_primary.gif&module=entities" alt="<?php  echo _PRIMARY_ENTITY;?>" title="<?php  echo _PRIMARY_ENTITY;?>" /> <?php
				}
				else
				{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				?>
				<input type="checkbox"  class="check" name="entities[]" value="<?php  echo $_SESSION['m_admin']['entity']['entities'][$theline]['ENTITY_ID']; ?>" ><?php if(isset($_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL']) && !empty($_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL'])){ echo $_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL'] ; }else{ echo $_SESSION['m_admin']['entity']['entities'][$theline]['LABEL'];}?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><?php  echo $_SESSION['m_admin']['entity']['entities'][$theline]['ROLE']; ?></i><br/></input>
				<?php
		}
		 ?> <br/><input class="button" type="button" name="removeEntities" id="removeEntities" value="<?php  echo _DELETE_ENTITY; ?>" onclick="doActionEntity('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=remove_user_entities', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=users_entities_form');" /><br/><br/>
<?php 	}

	if (count($_SESSION['m_admin']['entity']['entities']) < $_SESSION['m_admin']['nbentities']  || empty($_SESSION['m_admin']['entity']['entities']))
	{
	?>
		<input class="button" type="button" name="addEntity" onclick="displayModal('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=add_users_entities', 'add_user_entities', 600, 150);" value="<?php  echo _ADD_TO_ENTITY; ?>" />
	<?php
	}
	?>
	<br/><br/>
	<?php  if (count($_SESSION['m_admin']['entity']['entities']) > 0)
	{
	?>
		<input type="button" class="button" name="setPrimary" value="<?php  echo _CHOOSE_PRIMARY_ENTITY; ?>" onclick="doActionEntity('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=set_primary_entity', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=users_entities_form');"/>
	<?php
	}
	?>
	</form>
	</div>
