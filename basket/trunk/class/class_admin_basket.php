<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief   Module Basket :  Administration of the baskets
*
* Forms and process to add, modify and delete baskets
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/


/**
* @brief   Module Basket : Administration of the baskets
*
* Forms and process to add, modify and delete baskets
*
* @ingroup basket
*/
class admin_basket extends dbquery
{
   /**
    * Loads data from the groupbasket table in the session ( $_SESSION['m_admin']['basket']['groups']  array)
	*
	* @param  $id  string  basket identifier
	*/
	private function load_groupbasket($id)
	{
		$this->connect();
		$_SESSION['m_admin']['basket']['groups'] = array();
		$i =0;
		$default_action_list = '';
		$db = new dbquery();
		$db->connect();

		$this->query("select gb.group_id,  gb.sequence, gb.result_page, u.group_desc from ".$_SESSION['tablename']['bask_groupbasket']." gb, ".$_SESSION['tablename']['usergroups']." u where gb.basket_id = '".$id."' and gb.group_id = u.group_id order by u.group_desc");
		while($line2 = $this->fetch_object())
		{
			$db->query("select agb.group_id, agb.basket_id, agb.id_action, agb.where_clause,  ba.label_action, agb.used_in_basketlist as mass, agb.used_in_action_page as page, agb.default_action_list from ".$_SESSION['tablename']['bask_actions_groupbaskets']." agb, ".$_SESSION['tablename']['actions']." ba
			where ba.id = agb.id_action and agb.group_id = '".$line2->group_id."' and agb.basket_id = '".$id."'" );
			$basketlist = $line2->redirect_basketlist;
			$grouplist = $line2->redirect_grouplist;

			$actions = array();
			while($res = $db->fetch_object())
			{
				if($res->default_action_list == 'Y')
				{
					$default_action_list = $res->id_action;
				}
				else
				{
					array_push($actions, array('ID_ACTION' => $res->id_action, 'LABEL_ACTION' => $res->label_action, 'WHERE' => $res->where_clause, 'MASS_USE' => $res->mass, 'PAGE_USE' => $res->page));
				}
			}

			$_SESSION['m_admin']['basket']['groups'][$i] = array("GROUP_ID" => $line2->group_id , "GROUP_LABEL" => $this->show_string($line2->group_desc), "SEQUENCE" => $line2->sequence,	"RESULT_PAGE" => $line2->result_page, 'DEFAULT_ACTION' => $default_action_list,  'ACTIONS' => $actions);
			$i++;
		}
		$_SESSION['m_admin']['groupbasket'] = false ;
	}

	/**
	* Form for the management of the basket : used to add a new basket or to modify one
	*
	* @param   $mode  string "up" to modify a basket and "add" to add a new one
	* @param   $id  string Basket identifier (empty by default), must be set in "up" mode
	*/
	public function formbasket($mode,$id = "")
	{
		$state = true;
		require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
		$core_tools = new core_tools();
		$this->connect();

		// If mode "Up", Loading the informations of the basket in session
		if($mode == "up")
		{
			echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'basket_up.php', "include");
			$_SESSION['m_admin']['mode'] = "up";
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query("select * from ".$_SESSION['tablename']['bask_baskets']." where basket_id = '".$id."' and enabled= 'Y'");
				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _BASKET_MISSING;
					$state = false;
				}
				else
				{
					$_SESSION['m_admin']['basket']['basketId'] = $this->show_string($id);
					$line = $this->fetch_object();
					$_SESSION['m_admin']['basket']['desc'] = $this->show_string($line->basket_desc);
					$_SESSION['m_admin']['basket']['name'] = $this->show_string($line->basket_name);
					$_SESSION['m_admin']['basket']['clause'] = $this->show_string($line->basket_clause);
					$_SESSION['m_admin']['basket']['is_generic'] = $this->show_string($line->is_generic);
					$_SESSION['m_admin']['basket']['coll_id'] = $this->show_string($line->coll_id);
					if (! isset($_SESSION['m_admin']['load_groupbasket']) || $_SESSION['m_admin']['load_groupbasket'] == true)
					{
						$this->load_groupbasket($id);
						$_SESSION['m_admin']['groupbasket'] = false ;
						$_SESSION['service_tag'] = 'load_basket_session';
						echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'load_groupbasket', "include");
						$_SESSION['service_tag'] = '';
					}
				}
			}
		}
		// The title is different according the mode
		if($mode == "add")
		{
			$_SESSION['m_admin']['basket']['coll_id'] = $_SESSION['collections'][0]['id'];
			echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'basket_add.php', "include");
			echo '<h1><img src="'.$_SESSION['urltomodules'].'basket/img/picto_basket_b.gif" alt="" /> '._BASKET_ADDITION.'</h1>';
		}
		elseif($mode == "up")
		{
			echo '<h1><img src="'.$_SESSION['urltomodules'].'basket/img/picto_basket_b.gif" alt="" /> '._BASKET_MODIFICATION.'</h1>';
		}
		?>
		<div id="inner_content" class="clearfix">
			<div id="add_box" class="bloc">
				<div class="block">
				<p><iframe name="groupbasket_form" id="groupbasket_form" src="<?php echo $_SESSION['urltomodules']."basket/groupbasket_form.php";?>"  frameborder="0" class="frameform2" width="280px"></iframe></p>
				</div>
			<div class="block_end">&nbsp;</div>
			</div>

			<?php
			if($state == false)
			{
					echo "<br /><br /><br /><br />"._BASKET.' '._UNKNOWN."<br /><br /><br /><br />";
			}
			else
			{
			?>
			<form name="formbasket" id="formbasket" method="post" action="<?php if($mode == "up") { echo $_SESSION['urltomodules']."basket/basket_up_db.php"; } elseif($mode == "add") { echo $_SESSION['urltomodules']."basket/basket_add_db.php"; } ?>" class="forms addforms">
				<p>
					<label><?php echo _ID;?> : </label>
					<input name="basketId" id="basketId" type="text" value="<?php echo $_SESSION['m_admin']['basket']['basketId']; ?>" <?php if($mode == "up") { echo 'readonly="readonly" class="readonly"';} ?> />
				<input type="hidden"  name="id" value="<?php echo $id; ?>" />
				</p>
				<p>
					<label><?php echo _BASKET; ?> : </label>
					<input name="basketname"  type="text" id="basketname" value="<?php echo $_SESSION['m_admin']['basket']['name']; ?>" />
				</p>
				<p>
					<label><?php echo _DESC; ?> : </label>
					<textarea  cols="30" rows="4"  name="basketdesc"  id="basketdesc" ><?php echo $_SESSION['m_admin']['basket']['desc']; ?></textarea>
				</p>
				<?php if($_SESSION['m_admin']['basket']['is_generic'] == 'Y')
				{
					?>
					<p>
						<em><?php echo _SYSTEM_BASKET_MESSAGE;?>.</em>
					</p>
				<?php } ?>
				<p>
					<label><?php echo _COLLECTION;?> : </label>
					<select name="collection" id="collection">
						<option value=""><?php echo _CHOOSE_COLLECTION;?></option>
						<?php for($i=0; $i<count($_SESSION['collections']);$i++)
						{
						?>
							<option value="<?php echo $_SESSION['collections'][$i]['id'];?>" <?php if(count($_SESSION['collections']) == 1 || $_SESSION['collections'][$i]['id'] == $_SESSION['m_admin']['basket']['coll_id']) { echo 'selected="selected"';}?>><?php echo $_SESSION['collections'][$i]['label'];?></option>
						<?php
						}?>
					</select>
				</p>
				<p>
					<label><?php echo _BASKET_VIEW;?> : </label>
					<textarea  cols="30" rows="4"  name="basketclause" <?php if($_SESSION['m_admin']['basket']['basketId'] == 'CopyMailBasket' || $_SESSION['m_admin']['basket']['basketId'] == 'DepartmentBasket') { echo 'readonly="readonly" class="readonly"';} ?> id="basketclause" ><?php echo $_SESSION['m_admin']['basket']['clause']; ?></textarea> <a href="javascript::" onclick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>keywords_help.php','modify','toolbar=no,status=no,width=400,height=450,left=500,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');"><img src = "<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/picto_menu_help.gif" alt="<? echo _HELP_KEYWORDS; ?>" title="<? echo _HELP_KEYWORDS; ?>" /></a>
				</p>
				<p class="buttons">
					<input type="submit" name="Submit" value="<?php echo _VALIDATE; ?>" class="button" />
					<input type="button" name="cancel" value="<?php echo _CANCEL; ?>" class="button"  onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=basket&amp;module=basket';"/>
				</p>
			</form>
		<?php
		}
		?>
		</div>
	<?php
	}

	/**
	* Validates the  informations returned by the form of the formgroups() function, in case of error writes in the $_SESSION['error'] var
	*
	* @param   $mode  string Administrator mode "add" or "up"
	*/
	private function basketinfo($mode)
	{

		if($mode == "add")
		{
			$_SESSION['m_admin']['basket']['basketId'] = $this->wash($_REQUEST['basketId'], "nick", _THE_ID, 'yes', 0, 32);
		}
		if($mode == "up")
		{
			$_SESSION['m_admin']['basket']['basketId']  = $this->wash($_REQUEST['id'], "nick", _THE_ID, 'yes', 0, 32);
		}
		if(isset($_REQUEST['basketname']) && !empty($_REQUEST['basketname']))
		{
			$_SESSION['m_admin']['basket']['name'] = $this->wash($_REQUEST['basketname'], "no", _THE_BASKET, 'yes', 0, 255);
		}
		if (isset($_REQUEST['basketdesc']) && !empty($_REQUEST['basketdesc']))
		{
			$_SESSION['m_admin']['basket']['desc'] = $this->wash($_REQUEST['basketdesc'], "no", _THE_DESC, 'yes', 0, 255);
		}
		if ( isset($_REQUEST['collection']) && !empty($_REQUEST['collection']))
		{
			$_SESSION['m_admin']['basket']['coll_id'] = $this->wash($_REQUEST['collection'], "no", _THE_COLLECTION, 'yes', 0, 32);
		}
		if (isset($_REQUEST['basketclause']) && !empty($_REQUEST['basketclause']))
		{
			$_SESSION['m_admin']['basket']['clause'] = trim($_REQUEST['basketclause']);
		}
		if(count($_SESSION['m_admin']['basket']['groups']) < 1)
		{
			$this->add_error(_BELONGS_TO_NO_GROUP, "");
		}
	}

	/**
	* After the validation made by the basketinfo() function, according the mode update the basket table or insert a new basket
	*
	* @param  $mode  string Mode "up" or "add"
	*/
	public function addupbasket($mode)
	{

		// Checks the session values
		$this->basketinfo($mode);

		// If error redirection to the form page and shows the error
		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['basket']['basketId']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket_up&id=".$_SESSION['m_admin']['basket']['basketId']."&module=basket");
					exit();
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
					exit();
				}
			}
			elseif($mode == "add")
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket_add&module=basket");
				exit();
			}
		}
		else
		{
			$this->connect();
			// Add Mode
			if($mode == "add")
			{
				$this->query("select basket_id from ".$_SESSION['tablename']['bask_baskets']." where basket_id= '".$_SESSION['m_admin']['basket']['basketId']."'");

				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = $_SESSION['m_admin']['basket']['basketId']." "._ALREADY_EXISTS."<br />";
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket_add&module=basket");
					exit();
				}
				else
				{
					$tmp = $this->protect_string_db($_SESSION['m_admin']['basket']['clause']);
					// Checks the where clause syntax
					$syntax =  $this -> where_test($_SESSION['m_admin']['basket']['clause']);
					if($syntax <> true)
					{
					 	$_SESSION['error'] .= " : "._SYNTAX_ERROR_WHERE_CLAUSE."." ;
						header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket_up&id=".$_SESSION['m_admin']['basket']['basketId']."&module=basket");
						exit();
					}
					$this->query("INSERT INTO ".$_SESSION['tablename']['bask_baskets']." ( coll_id, basket_id, basket_name, basket_desc , basket_clause ) VALUES ( '".$_SESSION['m_admin']['basket']['coll_id']."', '".$_SESSION['m_admin']['basket']['basketId']."', '".$this->protect_string_db($_SESSION['m_admin']['basket']['name'])."', '".$this->protect_string_db($_SESSION['m_admin']['basket']['desc'])."','".$tmp." ')", "no");
					$this->load_db();

					// Log in database if required
					if($_SESSION['history']['basketadd'] == "true")
					{
						require_once($_SESSION['pathtocoreclass']."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['bask_baskets'], $_SESSION['m_admin']['basket']['basketId'],"ADD",_BASKET_ADDED." : ".$_SESSION['m_admin']['basket']['basketId'], $_SESSION['config']['databasetype'], 'basket');
					}

					// Empties the basket administration session var and redirect to baskets list
					$this->clearbasketinfos();
					$_SESSION['error'] = _BASKET_ADDED;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
					exit();
				}
			}
			// Up Mode
			elseif($mode == "up")
			{
				$clause = "";
				$tmp = '';
				if($_SESSION['m_admin']['basket']['clause'] <> "")
				{
					$tmp =  $this->protect_string_db($_SESSION['m_admin']['basket']['clause']);
					$clause = ", basket_clause = '".$tmp."'";
				}

				// Checks the where clause syntax
				$syntax =  $this->where_test($_SESSION['m_admin']['basket']['clause']);
				if($syntax <> true)
				{
					 $_SESSION['error'] .= " : "._SYNTAX_ERROR_WHERE_CLAUSE."." ;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket_up&id=".$_SESSION['m_admin']['basket']['basketId']."&module=basket");
					exit();
				}

				$this->query("UPDATE ".$_SESSION['tablename']['bask_baskets']." set basket_name = '".$this->protect_string_db($_SESSION['m_admin']['basket']['name'])."' , coll_id = '".$_SESSION['m_admin']['basket']['coll_id']."', basket_desc = '".$this->protect_string_db($_SESSION['m_admin']['basket']['desc'])."' ".$clause." where basket_id= '".$_SESSION['m_admin']['basket']['basketId']."'");
				$this->load_db();

				// Log in database if required
				if($_SESSION['history']['basketup'] == "true")
				{
					require_once($_SESSION['pathtocoreclass']."class_history.php");
					$hist = new history();
					$hist->add($_SESSION['tablename']['bask_baskets'], $_SESSION['m_admin']['basket']['basketId'],"UP",_BASKET_UPDATE." : ".$_SESSION['m_admin']['basket']['basketId'], $_SESSION['config']['databasetype'], 'basket');
				}

				// Empties the basket administration session var and redirect to baskets list
				$this->clearbasketinfos();
				$_SESSION['error'] = _BASKET_UPDATED;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
				exit();
			}
		}
	}

	/**
	* Cleans the $_SESSION['m_admin']['basket'] array
	*/
	private function clearbasketinfos()
	{
		unset($_SESSION['m_admin']);
	}

	/**
	* Check the basket where clause syntax
	*
	* @param  $where_clause   string The where clause to check
	* @return bool true if the syntax is correct, false otherwise
	*/
	public function where_test( $where_clause)
	{
		$where = "";
		$res2 = true;

		if( !empty ($where_clause))
		{
			require_once($_SESSION['pathtocoreclass']."class_security.php");
			$sec = new security();
			$where = $sec->process_security_where_clause($where, $_SESSION['user']['UserId']);
		 }
		// Gets the basket collection
		$ind = -1;
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['m_admin']['basket']['coll_id'] == $_SESSION['collections'][$i]['id'])
			{
				$ind = $i;
				break;
			}
		}

		if($ind == -1)
		{
			$_SESSION['error'] .= " ".$_SESSION['m_admin']['basket']['coll_id'];
			$res2 = false;
		}
		else // Launches the query in quiet mode
		{
			$this->connect();
			$res = $this->query("select count(*) from ".$_SESSION['collections'][$ind]['view']." ".$where, true);

		}
		if(!$res )
		{
			$_SESSION['error'] .= " ".$_SESSION['m_admin']['basket']['coll_id'];
			$res2 = false;
		}
		return $res2;
	}

	/**
	* Update the groupbasket and actions_groupbasket tables
	*/
	private function load_db()
	{
		$this->connect();
		// Empties the tables from the existing data about the current basket ($_SESSION['m_admin']['basket']['basketId'])
		$this->query("DELETE FROM ".$_SESSION['tablename']['bask_groupbasket'] ." where basket_id= '".$_SESSION['m_admin']['basket']['basketId']."'");
		$this->query("DELETE FROM ".$_SESSION['tablename']['bask_actions_groupbaskets'] ." where basket_id= '".$_SESSION['m_admin']['basket']['basketId']."'");
		$grouplistetmp ="";

		// Browses the $_SESSION['m_admin']['basket']['groups']
		for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'] ); $i++)
		{
			// Update groupbasket table
			$this->query("INSERT INTO ".$_SESSION['tablename']['bask_groupbasket']." (group_id, basket_id, sequence,  result_page)
			VALUES ('".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'])."', '".$this->protect_string_db($_SESSION['m_admin']['basket']['basketId'])."',
			".$_SESSION['m_admin']['basket']['groups'][$i]['SEQUENCE']." , '".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['RESULT_PAGE'])."' )");

			// Browses the actions array for the current basket - group couple and inserts the action in actions_groupbasket table  if needed
			for($j=0; $j < count($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS']); $j++)
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['bask_actions_groupbaskets']." (group_id, basket_id, where_clause, used_in_basketlist, used_in_action_page, id_action )
			VALUES ('".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'])."', '".$this->protect_string_db($_SESSION['m_admin']['basket']['basketId'])."',
			'".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['WHERE'])."',
			'".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['MASS_USE'])."',
			'".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['PAGE_USE'])."', ".$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].")");
			}
			// Inserts in actions_groupbasket table the default action if set
			if(isset($_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION']) && !empty($_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION']))
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['bask_actions_groupbaskets']." (group_id, basket_id, where_clause, used_in_basketlist, used_in_action_page, id_action, default_action_list)
			VALUES ('".$this->protect_string_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'])."', '".$this->protect_string_db($_SESSION['m_admin']['basket']['basketId'])."',
			'',
			'N',
			'N', ".$_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'].", 'Y')");
			}
		}

		$_SESSION['service_tag'] = 'load_basket_db';
		$core = new core_tools();
		echo $core->execute_modules_services($_SESSION['modules_services'], 'load_groupbasket_db', "include");
		$_SESSION['service_tag'] = '';
	}

	/**
	* Allows, suspends or deletes a basket in the database
	*
	* @param   $id  string Basket identifier
	* @param  $mode  string  "allow", "ban" or "del", but only "allow" and "ban" are deprecated
	*/
	public function adminbasket($id,$mode)
	{
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select basket_id from ".$_SESSION['tablename']['bask_baskets']." where basket_id= '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _BASKET_MISSING;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
				exit();
			}
			else
			{
				$info = $this->fetch_object();

				// Mode allow : not used
				if($mode == "allow")
				{
					$this->query("Update ".$_SESSION['tablename']['bask_baskets']." set enabled = 'Y' where basket_id= '".$id."'");
					if($_SESSION['history']['basketval'] == "true")
					{
						require_once($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['bask_baskets'], $id,"VAL",_BASKET_AUTORIZATION." : ".$id, $_SESSION['config']['databasetype'] ,'basket');
					}
					$_SESSION['error'] = _AUTORIZED_BASKET;
				}
				// Mode ban : not used
				elseif($mode == "ban")
				{
					$this->query("Update ".$_SESSION['tablename']['bask_baskets']." set enabled = 'N' where basket_id = '".$id."'");
					if($_SESSION['history']['basketban'] == "true")
					{
						require_once($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['bask_baskets'], $id,"BAN",_BASKET_SUSPENSION." : ".$id, $_SESSION['config']['databasetype'], 'basket');
					}
					$_SESSION['error'] = _SUSPENDED_BASKET;

				}
				// Mode delete  : delete a basket and all its setting
				elseif($mode == "del" )
				{
					$this->query("delete from ".$_SESSION['tablename']['bask_baskets']."  where basket_id = '".$id."'");
					$this->query("delete from ".$_SESSION['tablename']['bask_groupbasket']."  where basket_id = '".$id."'");
					$this->query("delete from ".$_SESSION['tablename']['bask_actions_groupbaskets']."  where basket_id = '".$id."'");

					$_SESSION['service_tag'] = 'del_basket';
					$_SESSION['temp_basket_id'] = $id;
					require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
					$core = new core_tools();
					echo $core->execute_modules_services($_SESSION['modules_services'], 'del_basket', "include");

					// Log in database if needed
					if($_SESSION['history']['basketdel'] == "true")
					{
						require_once($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['bask_baskets'], $id,"DEL",_BASKET_DELETION." : ".$id, $_SESSION['config']['databasetype'],  'basket');
					}
					$_SESSION['error'] = _BASKET_DELETION;
				}

				// Redirection to the baskets list page
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=basket&module=basket");
				exit();
			}
		}
	}

	/**
	* Checks if an action is defined for a given usergroup
	*
	* @param  $id_action  string Action identifier
	* @param  $ind_group_session  string Indice of the group in the $_SESSION['m_admin']['basket']['groups'] array
	*/
	public function is_action_defined_for_the_group($id_action, $ind_group_session)
	{
		for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'][$ind_group_session]['ACTIONS']); $i++)
		{
			if($id_action == $_SESSION['m_admin']['basket']['groups'][$ind_group_session]['ACTIONS'][$i]['ID_ACTION'])
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Checks if an action is allowed in a mode for a given group
	*
	* @param  $ind_group_session  string Indice of the group in the $_SESSION['m_admin']['basket']['groups'] array
	* @param  $id_action  string Action identifier
	* @param  $what  string Action  mode : "MASS_USE" or "PAGE_USE"
	* @return string 'Y' if the action is allowed in the mode, 'N' if not allowed, empty string otherwise
	*/
	public function get_infos_groupbasket_session($ind_group, $id_action, $what)
	{
		for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'][$ind_group]['ACTIONS']); $i++)
		{
			if($id_action == $_SESSION['m_admin']['basket']['groups'][$ind_group]['ACTIONS'][$i]['ID_ACTION'])
			{
				if(isset($_SESSION['m_admin']['basket']['groups'][$ind_group]['ACTIONS'][$i][$what]))
				{
					return $_SESSION['m_admin']['basket']['groups'][$ind_group]['ACTIONS'][$i][$what];
				}
				else
				{
					if($what == 'MASS_USE' || $what == 'PAGE_USE')
					{
						return 'N';
					}
					else
					{
						return '';
					}
				}
			}
		}
		if($what == 'MASS_USE' || $what == 'PAGE_USE')
		{
			return 'N';
		}
		else
		{
			return '';
		}
	}

	/**
	* Gets the data of an action if there is an error in the basket - group setting
	*
	* @param  $id_action   string Action identifier
	* @param  $what  string Action mode : "MASS_USE" or "PAGE_USE"
	* @return string 'N' or empty string
	*/
	public function get_data_from_error($id_action, $what)
	{
		if(isset($_SESSION['m_admin']['basket']['error']) && count($_SESSION['m_admin']['basket']['error']) < 1)
		{
			if($what == 'WHERE')
			{
				return '';
			}
			else
			{
				return 'N';
			}
		}
		for($i=0; $i <count($_SESSION['m_admin']['basket']['error']); $i++)
		{
			if($_SESSION['m_admin']['basket']['error'][$i]['ID_ACTION'] == $id_action)
			{
				if(isset($_SESSION['m_admin']['basket']['error'][$i][$what]))
				{
					return $_SESSION['m_admin']['basket']['error'][$i][$what];
				}
				else
				{
					if($what == 'WHERE')
					{
						return '';
					}
					else
					{
						return 'N';
					}
				}
			}
		}
		if($what == 'WHERE')
		{
			return '';
		}
		else
		{
			return 'N';
		}
	}
}