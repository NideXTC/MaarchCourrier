<?php

try {
	require_once("modules/life_cycle/class/ObjectControlerIF.php");
	require_once("modules/life_cycle/class/ClassifiedObjectControlerAbstract.php");
	require_once("modules/life_cycle/class/docserver_locations.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling docserver_locations objects from database
 * data, and vice-versa.
 * @author boulio
 *
 */
class docserver_locations_controler extends ClassifiedObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param docserver_locations $docserver_locations
	 * @return boolean
	 */
	public function save($docserver_locations){
		if(self::docserverLocationsExists($docserver_locations->docserver_locations_id)){
			// Update existing docserver_locations
			return self::update($docserver_locations);
		} else {
			// Insert new docserver_locations
			return self::insert($docserver_locations);
		}
	}

///////////////////////////////////////////////////////   INSERT BLOCK	
	/**
	 * Add given docserver_locations to database.
	 * @param docserver_locations $docserver_locations
	 */
	private function insert($docserver_locations){
		// Giving automatised values
		$docserver_locations->enabled="Y";
		
		// Inserting object
		$result = self::advanced_insert($docserver_locations);
		return $result;
	}

///////////////////////////////////////////////////////   UPDATE BLOCK
	/**
	 * Update given docserver_locations informations in database.
	 * @param docserver_locations $docserver_locations
	 */
	private function update($docserver_locations){
		// Updating automatised values of given object
		
		// Update given docserver_locations in database
		$result = self::advanced_update($docserver_locations);
	}

///////////////////////////////////////////////    GET BLOCK
	
	/**
	 * Get docserver_locations with given id.
	 * Can return null if no corresponding object.
	 * @param $id Id of docserver_locations to get
	 * @return docserver_locations 
	 */
	public function get($id) {
		return self::advanced_get($id,_DOCSERVER_LOCATIONS_TABLE_NAME);
	}

///////////////////////////////////////////////////// DELETE BLOCK
	/**
	 * Delete given docserver_locations from database.
	 * @param docserver_locations $docserver_locations
	 */
	public function delete($docserver_locations){
		// Deletion of given docserver_locations
		$result = self::advanced_delete($docserver_locations);
		return $result;
	}

///////////////////////////////////////////////////// DISABLE BLOCK
	/**
	 * Disable given docserver_locations from database.
	 * @param docserver_locations $docserver_locations
	 */
	public function disable($docserver_locations){
		// Disable of given docserver_locations
		$result = self::advanced_disable($docserver_locations);
		return $result;
	}

///////////////////////////////////////////////////// ENABLE BLOCK
	/**
	 * Disable given docserver_locations from database.
	 * @param docserver_locations $docserver_locations
	 */
	public function enable($docserver_locations){
		// Disable of given docserver_locations
		$result = self::advanced_enable($docserver_locations);
		return $result;
	}

//////////////////////////////////////////////   OTHER PRIVATE BLOCK
	public function docserverLocationsExists($docserver_locations_id){
		if(!isset($docserver_locations_id) || empty($docserver_locations_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select docserver_locations_id from "._DOCSERVER_LOCATIONS_TABLE_NAME." where docserver_locations_id = '".$docserver_locations_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_locations_id.' // ';
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
		$query = "select docserver_locations_id from "._DOCSERVER_LOCATIONS_TABLE_NAME." ";
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
				$result[$cptId] = $queryResult->docserver_locations_id;
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
