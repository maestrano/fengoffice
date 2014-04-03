<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaEntity extends MnoSoaBaseEntity {    
    public function getUpdates($timestamp)
    {
        $this->_log->info(__FUNCTION__ .  " start getUpdates (timestamp=" . $timestamp . ")");
        $msg = $this->callMaestrano("GET", "updates" . '/' . $timestamp);
        if (empty($msg)) { return false; }
        $this->_log->debug(__FUNCTION__ .  " after maestrano call");
        if (!empty($msg->organizations) && class_exists('MnoSoaOrganization')) {
            $this->_log->debug(__FUNCTION__ .  " has organizations");
            foreach ($msg->organizations as $organization) {
                $this->_log->debug(__FUNCTION__ .  " org id = " . $organization->id);
				try {
	                $mno_org = new MnoSoaOrganization('', $this->_log);
					$mno_org->receive($organization);
				} catch (Exception $e) {
				}
            }
        }
        if (!empty($msg->persons) && class_exists('MnoSoaPerson')) {
            $this->_log->debug(__FUNCTION__ . " has persons");
            foreach ($msg->persons as $person) {
                $this->_log->debug(__FUNCTION__ .  " person id = " . $person->id);
				try {
	                $mno_person = new MnoSoaPerson('', $this->_log);
	                $mno_person->receive($person);
				} catch (Exception $e) {
                                    error_log("Exception caught " . $e);
				}
            }
        }
        $this->_log->info(__FUNCTION__ .  " getUpdates successful (timestamp=" . $timestamp . ")");
    }
}
