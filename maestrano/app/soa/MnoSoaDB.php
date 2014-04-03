<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaDB extends MnoSoaBaseDB {
    
    /**
    * Update identifier map table
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    * @param	string	mno_id                  Maestrano entity identifier
    * @param	string	mno_entity_name         Maestrano entity name
    *
    * @return 	boolean Record inserted
    */
            
    public function addIdMapEntry($local_id, $local_entity_name, $mno_id, $mno_entity_name) {	
	// Fetch record
	$query = "INSERT INTO mno_id_map (mno_entity_guid, mno_entity_name, app_entity_id, app_entity_name, db_timestamp) VALUES (".DB::escape($mno_id).",".DB::escape(strtoupper($mno_entity_name)).",".DB::escape($local_id).",".DB::escape(strtoupper($local_entity_name)).",UTC_TIMESTAMP)";	
        $this->_log->debug("addIdMapEntry query = ".$query);
	
        try {
            $result = DB::execute($query);
            $this->_log->debug("after insert");
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
    
    /**
    * Get Maestrano GUID when provided with a local identifier
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    *
    * @return 	boolean Record found	
    */
    
    public function getMnoIdByLocalIdName($localId, $localEntityName)
    {
        $mno_entity = null;
        $this->_log->debug("getMnoIdByLocalIdName query = ".DB::escape($localId));
	// Fetch record
	$query = "SELECT mno_entity_guid, mno_entity_name, deleted_flag from mno_id_map where app_entity_id=" . DB::escape($localId) . " and app_entity_name=" . DB::escape(strtoupper($localEntityName));
        $this->_log->debug("getMnoIdByLocalIdName query = ".$query);
	$result = DB::executeOne($query);
        $this->_log->debug("after fetch");
        
	// Return id value
	if ($result) {            
            $mno_entity_guid = trim($result['mno_entity_guid']);
            $mno_entity_name = trim($result['mno_entity_name']);
            $deleted_flag = trim($result['deleted_flag']);
            
            if (!empty($mno_entity_guid) && !empty($mno_entity_name)) {
                $mno_entity = (object) array (
                    "_id" => $mno_entity_guid,
                    "_entity" => $mno_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
        
        $this->_log->debug("returning mno_entity = ".json_encode($mno_entity));
	
	return $mno_entity;
    }
    
    public function getLocalIdByMnoIdName($mnoId, $mnoEntityName)
    {
	$local_entity = null;
        
	// Fetch record
	$query = "SELECT app_entity_id, app_entity_name, deleted_flag from mno_id_map where mno_entity_guid=". DB::escape($mnoId) ." and mno_entity_name=". DB::escape(strtoupper($mnoEntityName));
        $this->_log->debug("getLocalIdByMnoIdName query = ".$query);
	$result = DB::executeOne($query);
        $this->_log->debug("after fetch");
        
	// Return id value
	if ($result) {           
            $app_entity_id = trim($result['app_entity_id']);
            $app_entity_name = trim($result['app_entity_name']);
            $deleted_flag = trim($result['deleted_flag']);
            
            if (!empty($app_entity_id) && !empty($app_entity_name)) {
            
                $local_entity = (object) array (
                    "_id" => $app_entity_id,
                    "_entity" => $app_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
	
        $this->_log->debug("returning local_entity = ".json_encode($local_entity));
        
	return $local_entity;
    }
    
    public function deleteIdMapEntry($localId, $localEntityName) 
    {
        // Logically delete record
        $query = "UPDATE mno_id_map SET deleted_flag=1 WHERE app_entity_id=".DB::escape($localId)." and app_entity_name=".DB::escape(strtoupper($localEntityName));
        $this->_log->debug("deleteIdMapEntry query = ".$query);
        
        try {
            $result = DB::execute($query);
            $this->_log->debug("result = ".json_encode($result));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
