<?php
/*
*    Copyright 2008,2009,2010 Maarch
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
* @brief  Contains the controler of the Tag Object (create, save, modify, etc...)
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
    require_once("core/class/class_db.php");
    require_once("modules/tags/class/Tag.php");
    require_once("modules/tags/tags_tables_definition.php");
} catch (Exception $e){
    echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Tag Object
* @ingroup core
*/
class tag_controler
    extends ObjectControler
{
    /**
     * Get event with given event_id.
     * Can return null if no corresponding object.
     * @param $id Id of event to get
     * @return event
     */
    
    public function get_by_label($tag_label, $coll_id = 'letterbox_coll')
    {
		
        if (empty($tag_label) || empty($coll_id) ) {
           
            return null;
        }

		$db = new dbquery();
		$db->connect();
        $db->query(
        	'select tag_label, coll_id from '._TAG_TABLE_NAME.' where tag_label=\''.$tag_label.'\' and'.
        		' coll_id = \''.$coll_id.'\'');
  
        self::set_specific_id('tag_label');
      
        $tag=$db->fetch_object();

        if (isset($tag)) {
            return $tag;
        } else {
            return null;
        }
    }
    
    public function get($tag_label, $coll_id, $res_id)
    {
		
        if (empty($tag_label) || empty($coll_id) || empty($res_id)) {
            return null;
        }

        self::set_specific_id('tag_label');
      
        $tag = self::advanced_get($tag_label, _TAG_TABLE_NAME);

        if (isset($tag)) {
            return $tag;
        } else {
            return null;
        }
    }
	
	
  
	public function get_by_res($res_id,$coll_id)
    {
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' "
        );
		//$db->show();
        
		
		$return = array();
		while ($res = $db->fetch_object()){
			array_push($return, $res->tag_label);
		}
        if ($return) return $return;
		else return false;
    }
  
  
  
  	public function delete_this_tag($res_id,$coll_id,$tag_label)
    {
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."' "
        );
		if ($db->nb_result()>0){
			//Lancement de la suppression de l'occurence
			$fin =$db->query(
	        	"delete from " ._TAG_TABLE_NAME
	            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."' "
      	    );
			if ($fin){ return true; }
		}
		return fasle;
		
		//$db->show();
    }
	
	public function countdocs($tag_label, $coll_id){
		$db = new dbquery();
		$db->connect();
        $db->query(
	        	"select count(res_id) as bump from " ._TAG_TABLE_NAME
	            . " where tag_label = '" . $tag_label . "' and coll_id = '".$coll_id."' ".
	            " and res_id <> 0"
        );
		
		$result = $db->fetch_object();
		$return = 0; 
		
		if ($result)
		{
			$return = $result->bump; 
		}
		
		return $return;
	}
	
	
	public function delete_tags($res_id,$coll_id)
    {
		$db = new dbquery();
		$db->connect();
        $db->query(
	        	"delete from " ._TAG_TABLE_NAME
	            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' "
        );
		//$db->show();
    }
	
	
	public function add_this_tag($res_id,$coll_id,$tag_label)
    {
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."'  "
        );
		if ($db->nb_result()==0){
			//Lancement de la suppression de l'occurence
			$fin =$db->query(
	        	"insert into " ._TAG_TABLE_NAME
	            . " (tag_label, res_id, coll_id) values ('".$tag_label."', '" . $res_id . "','".$coll_id."')  "
      	    );
			if ($fin){ return true; }
		}
		return fasle;
		
		//$db->show();
        
    }
	
	public function load_sessiontag($res_id,$coll_id)
	{
			$_SESSION['tagsuser'] = array();	
			$_SESSION['tagsuser'] =	$this->get_by_res($res_id, $coll_id);	
	}
	
	
	public function add_this_tags_in_session($tag_label)
    { //remplir le formulaire de session
    
		$ready = true;
		if ($_SESSION['tagsuser'])
		{
			//$_SESSION['taguser'] = array();	
			
			foreach($_SESSION['tagsuser'] as $this_tag){
				if ($this_tag == $tag_label){
					$ready = false;
				}	
			}	
	
		}
		
		if ($ready == true){
					
					
			if 	(!$_SESSION['tagsuser'])
			{
				$_SESSION['tagsuser'] = array();
			}
				
			array_push($_SESSION['tagsuser'], $tag_label);
			return true;
		}
		return false;
	
    }
    
    public function remove_this_tags_in_session($tag_label)
    { //remplir le formulaire de session
	
		if ($_SESSION['tagsuser'])
		{
			$ready = false;
			foreach($_SESSION['tagsuser'] as $this_tag){
				if ($this_tag == $tag_label){
					$ready = true;
				}	
			}
			
			if ($ready == true){
				
				unset($_SESSION['tagsuser'][array_search($tag_label, $_SESSION['tagsuser'])]);
				return true;
			}
			return false;
		}
		else
		{
			return false;
		}    
    }
	
	public function update_restag($res_id,$coll_id,$tag_array)
    {
  		$core_tools = new core_tools();	
  		if ($core_tools->test_service('add_tag_to_res', 'tags',false) == 1)
		{
			$this->delete_tags($res_id, $coll_id);
			foreach($tag_array as $this_taglabel)
			{
				$this->add_this_tag($res_id,$coll_id,$this_taglabel);
			}
		}
	}
  
}

?>