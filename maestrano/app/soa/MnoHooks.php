<?php

if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

if (class_exists('Hook')) {
  Hook::register("maestrano");
}

function maestrano_insert_committed($obj)
{
    add_to_set_global_updates($obj);
}

function maestrano_update_committed($obj)
{
    add_to_set_global_updates($obj);
}

function add_to_set_global_updates($obj, $setToFalse=false) 
{
    global $global_updates;
    
    if (method_exists($obj, "getObjectId") && method_exists($obj, "getObjectTypeId")) {
        $obj_type_id = $obj->getObjectTypeId();
        $obj_id = $obj->getObjectId();

        if (!empty($obj_type_id) && !empty($obj_id)) {
                if (
                    $setToFalse
                   ) {
                    $global_updates[$obj_type_id][$obj_id] = false;
                    return;
                } else if (
                    array_key_exists($obj_type_id, $global_updates) && 
                    array_key_exists($obj_id, $global_updates[$obj_type_id]) &&
                    !$global_updates[$obj_type_id][$obj_id]
                   ) {
                    // prevent override of push_to_maestrano=false flag
                    return;
                }
                $global_updates[$obj_type_id][$obj_id] = true;
        }
    }
}

function maestrano_process_updates() 
{
    global $global_updates;
    
    if (empty($global_updates)) { return; }
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