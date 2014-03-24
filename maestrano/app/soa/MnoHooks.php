<?php

if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

Hook::register("maestrano");

function maestrano_after_insert($obj) 
{
    error_log("^^^^^^^^^^^^^^^^^^^^^^^^^insert");
    add_to_set_global_updates($obj);
}

function maestrano_after_update($obj) 
{
    error_log("^^^^^^^^^^^^^^^^^^^^^^^^^update");
    add_to_set_global_updates($obj);
}

function add_to_set_global_updates($obj) 
{
    if ($obj->push_to_maestrano) {
        error_log(__CLASS__ . " " . __FUNCTION__ . " push_to_maestrano=true");
    } else {
        error_log(__CLASS__ . " " . __FUNCTION__ . " push_to_maestrano=false");
    }
    
    global $global_updates;
    
    $obj_type_id = $obj->getObjectTypeId();
    $obj_id = $obj->getObjectId();
    
    if (!empty($obj_type_id) && !empty($obj_id)) {
        error_log("*************************obj_type_id=" . $obj_type_id . ", obj_id=" . $obj_id);
        if (!$obj->push_to_maestrano) {
            $global_updates[$obj_type_id][$obj_id] = false;
            error_log("push_to_maestrano=false");
        } else {
            if (
                array_key_exists($obj_type_id, $global_updates) && 
                array_key_exists($obj_id, $global_updates[$obj_type_id]) &&
                !$global_updates[$obj_type_id][$obj_id]
               ) {
                // prevent override of push_to_maestrano=false flag
                return;
            }
            $global_updates[$obj_type_id][$obj_id] = true;
            error_log("push_to_maestrano=true");
        }
    }
}

function maestrano_process_updates() 
{
    global $global_updates;
    
    if (empty($global_updates)) { return; }
    $error_list = error_get_last();
    error_log("error_list=" . json_encode($error_list));
    $error_list = func_get_args();
    error_log("error_list=" . json_encode($error_list));
    if (!is_null($error_list)) { 
        error_log("SHUTDOWN FUNCTION ABORTED - ERROR DETECTED"); return; 
    }
    error_log("!!!!!!!!!!!1IN SHUTDOWN global_updates=" . json_encode($global_updates));
    
    $maestrano = MaestranoService::getInstance();
    error_log("after get maestrano instance");
    
    if (!$maestrano->isSoaEnabled() || !$maestrano->getSoaUrl()) { return; }
    error_log("after check maestrano is enabled");
    
    // CONTACTS = object type 16
    if (array_key_exists('16', $global_updates)) {
        error_log("16 is in array");
        foreach ($global_updates['16'] as $key => $push_to_maestrano) {
            error_log("key = " . $key);
            if ($push_to_maestrano) {
                $contact = Contacts::findById($key);
                try {
                    if (!empty($contact)) {
                        error_log("contract is not empty");
                        if ($contact->getIsCompany()) {
                            error_log("contract is a company");
                            $mno_entity=new MnoSoaOrganization('', new MnoSoaBaseLogger());
                            $mno_entity->send($contact);
                        } else {
                            error_log("contract is a person");
                            $mno_entity=new MnoSoaPerson('', new MnoSoaBaseLogger());
                            $mno_entity->send($contact);
                        }
                    }
                } catch (Exception $ex) {
                    // skip
                }
            }
        }
    }
    
    unset($GLOBALS['global_updates']);
}

?>