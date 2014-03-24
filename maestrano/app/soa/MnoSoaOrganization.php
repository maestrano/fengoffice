<?php

/**
 * Mno Organization Class
 */
class MnoSoaOrganization extends MnoSoaBaseOrganization
{
    protected $_local_entity_name = "contact";
    
    // DONE
    protected function pushId() 
    {
        $this->_log->debug(__FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
	$this->_log->debug(__FUNCTION__ . " localentityidentifier=".$id);
        
	if (!empty($id)) {
	    $this->_log->debug(__FUNCTION__ . " this->_local_entity->id = " . json_encode($id));
	    $mno_id = $this->getMnoIdByLocalId($id);
            
	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullId() 
    {
        $this->_log->debug(__FUNCTION__ . " start " . $this->_id);
        
	if (!empty($this->_id)) {            
	    $local_id = $this->getLocalIdByMnoId($this->_id);
            $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
            
	    if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " is STATUS_EXISTING_ID");
                $this->_local_entity = Contacts::findById($local_id->_id);
		return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
	    } else if ($this->isDeletedIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
                return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
            } else {
                $this->_log->debug(__FUNCTION__ . " is STATUS_NEW_ID");
		$this->_local_entity = new Contact();
                $this->_local_entity->setIsCompany(true);
                $this->pullName();
                $this->_local_entity->save(false);
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        
        $this->_log->debug(__FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    // DONE
    protected function pushName() 
    {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_name = $this->push_set_or_delete_value($this->_local_entity->getFirstname());
	$this->_log->debug(__FUNCTION__ . " end " . $this->_name);
    }
    
    // DONE
    protected function pullName() 
    {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_local_entity->setFirstName($this->pull_set_or_delete_value($this->_name));
        $this->_local_entity->setObjectName();
        $this->_log->debug(__FUNCTION__ . " fname =  " . $this->_local_entity->getFirstname());
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pullCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pushNumberOfEmployees() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullNumberOfEmployees() {
       // DO NOTHING
    }
    
    // DONE
    protected function pushAddresses() {
        $this->_log->debug(__FUNCTION__ . " start ");
        
        $work_address = ContactAddresses::getAddressByTypeId($this->_local_entity, '2');
        $streetAddress = ""; $locality = ""; $region = ""; $postalCode = ""; $country = "";
        
        if ($work_address) {
            $this->_log->debug(__FUNCTION__ . " work address not empty ");
            $streetAddress = $work_address->getStreet();
            $locality = $work_address->getCity();
            $region = $work_address->getState();
            $postalCode = $work_address->getZipcode();
            $country = $work_address->getCountry();
        } else {
            $this->_log->debug(__FUNCTION__ . " work address is empty ");
        }
        $this->_log->debug(__FUNCTION__ . " after getAddress fields ");
        
        // POSTAL ADDRESS
        $this->_address->postalAddress->streetAddress = $this->push_set_or_delete_value($streetAddress, "");
        $this->_address->postalAddress->locality = $this->push_set_or_delete_value($locality, "");
        $this->_address->postalAddress->region = $this->push_set_or_delete_value($region, "");
        $this->_address->postalAddress->postalCode = $this->push_set_or_delete_value($postalCode, "");
        $this->_address->postalAddress->country = strtoupper($this->push_set_or_delete_value($country, ""));
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullAddresses() {
        $this->_log->debug(__FUNCTION__ . " start ");
	// POSTAL ADDRESS
        $street = $this->pull_set_or_delete_value($this->_address->postalAddress->streetAddress, "");
        $zipCode = $this->pull_set_or_delete_value($this->_address->postalAddress->postalCode, "");
        $city = $this->pull_set_or_delete_value($this->_address->postalAddress->locality, "");
        $state = $this->pull_set_or_delete_value($this->_address->postalAddress->region, "");
        $country = $this->pull_set_or_delete_value($this->_address->postalAddress->country, "");
        $address_type = 'work';
        
        if (!empty($country)) {
            $country = strtolower($country);
            //$this->_log->debug("code = " . $code);
            //$country = CountryCodes::getCountryNameByCode($code);
        }
        
        $this->_log->debug("incomign street = " . $this->_address->postalAddress->streetAddress);
        
        $this->_log->debug("street = " . $street . " zipCode = " . $zipCode . " city = " . $city . " state = " . $state . " country = " . $country);
        
        $addressObj = $this->_local_entity->getAddress($address_type);
        
        if ($addressObj) {
            $this->_log->debug("address object exists = " . json_encode($addressObj));
            $address_type_code = AddressTypes::getAddressTypeId($address_type);
            $this->_log->debug("address type code = " . $address_type_code);
            $addressObj->edit($street, $city, $state, $country, $zipCode, $address_type_code, true);
            
        } else {
            $this->_log->debug("address object does not exist");
            $this->_local_entity->addAddress($street, $city, $state, $country, $zipCode, $address_type, true);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEmails() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $email = "";

        $emailObj = $this->_local_entity->getEmail('work');
        
        if (!empty($emailObj)) {
            $this->_log->debug("not empty emailObj");
            $email = $emailObj->getEmailAddress(); 
        }
        
        $this->_email->emailAddress = $this->push_set_or_delete_value($email, "");
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullEmails() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $email = $this->pull_set_or_delete_value($this->_email->emailAddress, "");
        $email_type = 'work';
        
        $emailObj = $this->_local_entity->getEmail($email_type);
        
        if ($emailObj) {
            $this->_log->debug("existing email object");
            $emailObj->editEmailAddress($email);
        } else {
            $this->_log->debug("new email object");
            $this->_local_entity->addEmail($email, $email_type, true);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_telephone->voice = $this->push_set_or_delete_value($this->_local_entity->getPhoneNumber('work', true), "");
        $this->_telephone->fax = $this->push_set_or_delete_value($this->_local_entity->getPhoneNumber('fax', true), "");
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");
        
        $number = $this->pull_set_or_delete_value($this->_telephone->voice, "");
        $phone_type = 'work';
        $workPhoneObj = $this->_local_entity->getPhone($phone_type,true);
        
        if ($workPhoneObj) {
            $workPhoneObj->editNumber($number);
        } else {
            $this->_local_entity->addPhone($number, $phone_type, true);
        }
        
        $number = $this->pull_set_or_delete_value($this->_telephone->fax, "");
        $phone_type = 'fax';
        
        $faxObj = $this->_local_entity->getPhone($phone_type,true);
        
        if ($faxObj) {
            $faxObj->editNumber($number);
        } else {
            $this->_local_entity->addPhone($number, $phone_type, true);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushWebsites() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_website->url = $this->push_set_or_delete_value($this->_local_entity->getWebpageURL('work'), "");
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullWebsites() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $url = $this->pull_set_or_delete_value($this->_website->url, "");
        $web_type = 'work';
        
        $webpageObj = $this->_local_entity->getWebpage($web_type);
        
        if ($webpageObj) {
            $webpageObj->editWebpageURL($url);
        } else {
            $this->_local_entity->addWebpage($url, $web_type);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEntity() {
        // DO NOTHING
    }
    
    // DONE
    protected function pullEntity() {
        // DO NOTHING
    }
    
    // DONE
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_log->debug(__FUNCTION__ . " status=" . $status);
        $this->_log->debug(__FUNCTION__ . " push_to_maestrano=" . $push_to_maestrano);
        if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
            $this->_local_entity->save($push_to_maestrano);
            $this->_log->debug(__FUNCTION__ . " after save call");
        }
        if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
            $object_controller = new ObjectController();
            $object_controller->add_subscribers($this->_local_entity);
            $object_controller->link_to_new_object($this->_local_entity);
            $object_controller->add_custom_properties($this->_local_entity);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function getLocalEntityIdentifier() {
        return $this->_local_entity->getId();
    }
}

?>