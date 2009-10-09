<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();

$what = "all";
$where = "";
$_SESSION['chosen_user'] = '';
if(isset($_GET['what']) && !empty($_GET['what']))
{
	if($_GET['what'] == "all")
	{
		$what = "all";

	}
	else
	{
		$what = addslashes($func->wash($_GET['what'], "no", "", "no"));
		$where = "(".$_SESSION['tablename']['users'].".lastname like '".strtolower($what)."%' or ".$_SESSION['tablename']['users'].".lastname like '".strtoupper($what)."%') ";
	}
}
	$db = new dbquery();
	$db->connect();

	$select[$_SESSION['tablename']['users']] = array();
	array_push($select[$_SESSION['tablename']['users']],"user_id","lastname","firstname" );

	$req = new request();

	$list=new list_show();
	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = 'lastname';
	if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
	{
		$field = trim($_REQUEST['order_field']);
	}

	$orderstr = $list->define_order($order, $field);

	$tab = $req->select($select, $where, $orderstr, $_SESSION['config']['databasetype'], $limit="500",false);

for ($i=0;$i<count($tab);$i++)
{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{


				if($tab[$i][$j][$value]== "user_id" )
				{
					$tab[$i][$j]["user_id"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _ID;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='user_id';
				}

				if($tab[$i][$j][$value]=='lastname')
				{
					$tab[$i][$j]['value']= $req->show_string($tab[$i][$j]['value']);
					$tab[$i][$j]['lastname']= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='lastname';
				}
				if($tab[$i][$j][$value]=="firstname")
				{
					$tab[$i][$j]['value']= $req->show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["info"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='firstname';
				}
			}
		}
	}

	if(isset($_REQUEST['field']) && !empty($_REQUEST['field']))
	{
		//$_SESSION['chosen_user'] = $_REQUEST['field'];
		?>
			<script language="javascript">
				var item = window.opener.$('user_id');
				if(item)
				{
					item.value = '<?php echo $_REQUEST['field'];?>';
					self.close();
				}
			</script>
			<?php
		exit();
	}

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_CHOOSE_USER2);
$time = $core_tools->get_session_time_expire();
?>
<body onLoad="javascript:setTimeout(window.close, <?php echo $time;?>*60*1000);">

<?php
$nb = count($tab);

$list->list_doc($tab, $nb, _USERS_LIST,'user_id',$name = "select_user_report",'user_id','',false,true,'get',$_SESSION['urltomodules'].'folder/select_user_report.php',_CHOOSE_USER2, false, true, true,false, true, true,  true, false, '', '',  true, _ALL_USERS,_USER);
?>
</body>
</html>