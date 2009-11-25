<?php
/**
* File : note_add.php
*
* Popup add a note
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");

$core_tools = new core_tools();
$sec = new security();
//here we loading the lang vars
$core_tools->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_ADD_NOTE);

$identifier = '';
$table = '';
$coll_id = '';

if ($_REQUEST['size'] == "full")
{
	$extend_url = "&size=full";
	$extend_url_value = $_REQUEST['size'];
}

if(isset($_REQUEST['identifier']) && !empty($_REQUEST['identifier']))
{
	$identifier = trim($_REQUEST['identifier']);
}
if(isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id']))
{
	$coll_id = trim($_REQUEST['coll_id']);
	$view = $sec->retrieve_view_from_coll_id($coll_id);
}

if(isset($_REQUEST['table']) && !empty($_REQUEST['table']))
{
	$table = trim($_REQUEST['table']);
}
?>
<body id="pop_up">
<?php

if (isset($_REQUEST['notes'])&& !empty($_REQUEST['notes']))
{

	$db->query("INSERT INTO ".$_SESSION['tablename']['not_notes']."
		( identifier, note_text, date, user_id, coll_id, tablename) VALUES (".$identifier.", '".$db->protect_string_db($_REQUEST['notes'])."', '".date("Y")."-".date("m")."-".date("d")." ".date("H:i:s")."', '".$_SESSION['user']['UserId']."', '".$coll_id."', '".$table."')");

		if($_SESSION['history']['noteadd'])
		{

			require_once("core/class/class_history.php");
			$hist = new history();
			$hist->add($view, $identifier ,"ADD", _ADDITION_NOTE._ON_DOC_NUM.$identifier, $_SESSION['config']['databasetype'], 'notes');

		}
		if($_SESSION['origin'] <> 'valid' && $_SESSION['origin'] <> 'qualify')
		{
			//$_SESSION['error'] = _ADDITION_NOTE;
		}
		?>
		<script language="javascript" type="text/javascript">
		<?php  if($_SESSION['origin'] == "process")
		{?>
			var eleframe1 =  window.opener.top.frames['process_frame'].document.getElementById('list_notes_doc');
		<?php  }
		elseif($_SESSION['origin'] == "valid" || $_SESSION['origin'] == 'qualify')
		{
		?>
			var eleframe1 = window.opener.top.frames['index'].document.frames['myframe'].document.getElementById('list_notes_doc');
		<?php
		}
		else
		{?>
			var eleframe1 =  window.opener.top.document.getElementById('list_notes_doc');
	<?php 	}

		?>
		eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=notes&page=frame_notes_doc<? echo $extend_url;?>';
		window.top.close();
		</script>

<?php
	//	}
 }
else
{
?>
<h2 class="tit" style="padding:10px;"><img src="<? echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img']; ?>/picto_add_b.gif" alt=""/> <?php  echo _ADD_NOTE;?> </h2>
 
	<div class="block" style="padding:10px;">
      <form name="form1" method="post" action="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=notes&page=note_add&identifier=<?php  echo $_GET['identifier'];?>&table=<?php  echo $table;?>&coll_id=<?php  echo $coll_id;?>" >
		<input type="hidden" name="display" value="true" />
		<input type="hidden" name="modules" value="notes" />
		<input type="hidden" name="page" value="note_add" />
		<input type="hidden" value="<?php  echo $identifier;?>" name="identifier" id="identifier">
		<input type="hidden" value="<?php  echo $extend_url_value;?>" name="size" id="size">
<?php  /* if($_REQUEST['validation'] == 1){?><input type="hidden" value="<?php  echo $_GET['validation'];?>" name="validation"><?php  } */?>
		<textarea  cols="65" rows="10"  name="notes"  id="notes" ></textarea>
	   <br/>
        <p class="buttons">
				<input type="submit" name="Submit" value="<?php  echo _ADD_NOTE;?>" class="button"/>
             	<input type="submit" name="Submit2" value="<?php  echo _CANCEL;?>" onClick="javascript:self.close();" class="button"/>
            </p>

      </form>
	</div>
	<div class="block_end">&nbsp;</div>
 <?php  } ?>

</body>
</html>