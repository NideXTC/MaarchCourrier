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
* @brief  Contains the life_cycle Object (herits of the BaseObject class)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/


try {
	require_once("modules/life_cycle/class/ObjectControlerIF.php");
	require_once("modules/life_cycle/class/ClassifiedObjectControlerAbstract.php");
	require_once("modules/life_cycle/class/docserver_types.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling docserver_types objects from database
 * data, and vice-versa.
 * @author boulio
 *
 */
class docserver_types_controler extends ClassifiedObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param docserver_types $docserver_types
	 * @return boolean
	 */
	public function save($docserver_types){
		if(self::docserverTypesExists($docserver_types->docserver_type_id)){
			// Update existing docserver_types
			return self::update($docserver_types);
		} else {
			// Insert new docserver_types
			return self::insert($docserver_types);
		}
	}

///////////////////////////////////////////////////////   INSERT BLOCK	
	/**
	 * Add given docserver_types to database.
	 * @param docserver_types $docserver_types
	 */
	private function insert($docserver_types){
		// Giving automatised values
		$docserver_types->enabled="Y";
		
		// Inserting object
		$result = self::advanced_insert($docserver_types);
		return $result;
	}

///////////////////////////////////////////////////////   UPDATE BLOCK
	/**
	 * Update given docserver_types informations in database.
	 * @param docserver_types $docserver_types
	 */
	private function update($docserver_types){
		// Updating automatised values of given object
		
		// Update given docserver_types in database
		$result = self::advanced_update($docserver_types);
	}

///////////////////////////////////////////////    GET BLOCK
	
	/**
	 * Get docserver_types with given id.
	 * Can return null if no corresponding object.
	 * @param $id Id of docserver_types to get
	 * @return docserver_types 
	 */
	public function get($id) {
		return self::advanced_get($id,_DOCSERVER_TYPES_TABLE_NAME);
	}

///////////////////////////////////////////////////// DELETE BLOCK
	/**
	 * Delete given docserver_types from database.
	 * @param docserver_types $docserver_types
	 */
	public function delete($docserver_types){
		// Deletion of given docserver_types
		$result = self::advanced_delete($docserver_types);
		return $result;
	}

///////////////////////////////////////////////////// DISABLE BLOCK
	/**
	 * Disable given docserver_types from database.
	 * @param docserver_types $docserver_types
	 */
	public function disable($docserver_types){
		// Disable of given docserver_types
		$result = self::advanced_disable($docserver_types);
		return $result;
	}

///////////////////////////////////////////////////// ENABLE BLOCK
	/**
	 * Disable given docserver_types from database.
	 * @param docserver_types $docserver_types
	 */
	public function enable($docserver_types){
		// Disable of given docserver_types
		$result = self::advanced_enable($docserver_types);
		return $result;
	}

//////////////////////////////////////////////   OTHER PRIVATE BLOCK
	public function docserverTypesExists($docserver_type_id){
		if(!isset($docserver_type_id) || empty($docserver_type_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		//LKE = BULL ===== SPEC FONC : ==== Cycles de vie : docserver_types (ID1)
		// Ajout du contr�le pour v�rifier l'existence de la combinaison "docserver_type_id"			
		$query = "select docserver_type_id from "._DOCSERVER_TYPES_TABLE_NAME." where docserver_type_id = '".$docserver_type_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_type_id.' // ';
		}
		
		if(self::$db->nb_result() > 0){
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}
	
	public function getAllId($can_be_disabled = false){
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select docserver_type_id from "._DOCSERVER_TYPES_TABLE_NAME." ";
		if(!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_DOCSERVER_LOCATION.' // ';
		}
		if(self::$db->nb_result() > 0){
			$result = array();
			$cptId = 0;
			while($queryResult = self::$db->fetch_object()){
				$result[$cptId] = $queryResult->docserver_type_id;
				$cptId++;
			}
			self::$db->disconnect();
			return $result;
		} else {
			self::$db->disconnect();
			return null;
		}
	}
}

?>
