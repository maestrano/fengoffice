<?php

/**
 * Mno Organization Class
 */
class MnoSoaPerson extends MnoSoaBasePerson
{
    protected $_local_entity_name = "contact";
    
    // DONE
    protected function pushId() 
    {
        $this->_log->debug(__FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
        $this->_log->debug(__FUNCTION__ . " localentityidentifier=".$id);

	if (!empty($id)) {
	    $this->_log->debug(__FUNCTION__ . " this->_local_entity->id = " . $id);
	    $mno_id = $this->getMnoIdByLocalId($id);

	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullId() {
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
                $this->_local_entity->setIsCompany(false);
                $this->pullName();
                $this->_local_entity->save(false);
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        
        $this->_log->debug(__FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    // DONE
    protected function pushName() {
        $this->_log->debug(__FUNCTION__ . " start");
        $this->_name->givenNames = $this->push_set_or_delete_value($this->_local_entity->getFirstname());
        $this->_name->familyName = $this->push_set_or_delete_value($this->_local_entity->getSurname());
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullName() {
        $this->_log->debug(__FUNCTION__ . " start");
        $this->_local_entity->setFirstName($this->pull_set_or_delete_value($this->_name->givenNames));
        $this->_local_entity->setSurname($this->pull_set_or_delete_value($this->_name->familyName));
        $this->_local_entity->setObjectName();        
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    // DONE
    protected function pushBirthDate() {
        $this->_birth_date = $this->push_set_or_delete_value($this->_local_entity->getBirthday());
    }
    
    // DONE
    protected function pullBirthDate() {
        $this->_local_entity->setBirthday($this->pull_set_or_delete_value($this->_birth_date));
    }
    
    // DONE
    protected function pushGender() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullGender() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushAddresses() {
        $this->_log->debug(__FUNCTION__ . " start ");
        
        $query = "SELECT * FROM fo_contact_addresses WHERE contact_id = " . $this->getLocalEntityIdentifier();
	$result = DB::executeAll($query);
        error_log(json_encode($result));
        
        if (empty($result)) {
            return;
        }
        
        // WORK ADDRESS -> WORK POSTAL ADDRESS
        $work_address = $this->_local_entity->getAddress('work');
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
        
        $this->_address->work->postalAddress->streetAddress = $this->push_set_or_delete_value($streetAddress, "");
        $this->_address->work->postalAddress->locality = $this->push_set_or_delete_value($locality, "");
        $this->_address->work->postalAddress->region = $this->push_set_or_delete_value($region, "");
        $this->_address->work->postalAddress->postalCode = $this->push_set_or_delete_value($postalCode, "");
        $this->_address->work->postalAddress->country = strtoupper($this->push_set_or_delete_value($country, ""));
        
        // HOME ADDRESS -> HOME POSTAL ADDRESS
        $home_address = $this->_local_entity->getAddress('home');
        $streetAddress = ""; $locality = ""; $region = ""; $postalCode = ""; $country = "";
        
        if ($home_address) {
            $this->_log->debug(__FUNCTION__ . " home address not empty ");
            $streetAddress = $home_address->getStreet();
            $locality = $home_address->getCity();
            $region = $home_address->getState();
            $postalCode = $home_address->getZipcode();
            $country = $home_address->getCountry();
        } else {
            $this->_log->debug(__FUNCTION__ . " home address is empty ");
        }
        
        $this->_log->debug(__FUNCTION__ . " after getAddress fields ");
        
        $this->_address->home->postalAddress->streetAddress = $this->push_set_or_delete_value($streetAddress, "");
        $this->_address->home->postalAddress->locality = $this->push_set_or_delete_value($locality, "");
        $this->_address->home->postalAddress->region = $this->push_set_or_delete_value($region, "");
        $this->_address->home->postalAddress->postalCode = $this->push_set_or_delete_value($postalCode, "");
        $this->_address->home->postalAddress->country = strtoupper($this->push_set_or_delete_value($country, ""));
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullAddresses() {
        $this->_log->debug(__FUNCTION__ . " start ");
	// WORK POSTAL ADDRESS -> WORK ADDRESS
        $street = $this->pull_set_or_delete_value($this->_address->work->postalAddress->streetAddress, "");
        $zipCode = $this->pull_set_or_delete_value($this->_address->work->postalAddress->postalCode, "");
        $city = $this->pull_set_or_delete_value($this->_address->work->postalAddress->locality, "");
        $state = $this->pull_set_or_delete_value($this->_address->work->postalAddress->region, "");
        $country = $this->pull_set_or_delete_value($this->_address->work->postalAddress->country, "");
        $address_type = 'work';
        
        if (!empty($country)) {
            $country = strtolower($country);
        }
        
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
        
        // HOME POSTAL ADDRESS -> HOME ADDRESS
        $street = $this->pull_set_or_delete_value($this->_address->home->postalAddress->streetAddress, "");
        $zipCode = $this->pull_set_or_delete_value($this->_address->home->postalAddress->postalCode, "");
        $city = $this->pull_set_or_delete_value($this->_address->home->postalAddress->locality, "");
        $state = $this->pull_set_or_delete_value($this->_address->home->postalAddress->region, "");
        $country = $this->pull_set_or_delete_value($this->_address->home->postalAddress->country, "");
        $address_type = 'home';
        
        if (!empty($country)) {
            $country = strtolower($country);
        }
        
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
        $personal_emails = $this->_local_entity->getContactEmails('personal');
        $email = $this->_local_entity->getEmailAddress();
        if ($personal_emails) {
            $email2 = !is_null($personal_emails) && isset($personal_emails[0]) ? $personal_emails[0]->getEmailAddress() : '';
            $email3 = !is_null($personal_emails) && isset($personal_emails[1])? $personal_emails[1]->getEmailAddress() : '';
        }
	$this->_email->emailAddress = $this->push_set_or_delete_value($email, "");			
        $this->_email->emailAddress2 = $this->push_set_or_delete_value($email2, "");
        $this->_email->emailAddress3 = $this->push_set_or_delete_value($email3, "");
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullEmails() {      
	$this->_log->debug(__FUNCTION__ . " start ");
        
        $mno_email = $this->pull_set_or_delete_value($this->_email->emailAddress, "");
        $mno_email2 = $this->pull_set_or_delete_value($this->_email->emailAddress2, "");
        $mno_email3 = $this->pull_set_or_delete_value($this->_email->emailAddress3, "");
        
        $personal_email_type_id = EmailTypes::getEmailTypeId('personal');
        $main_emails = ContactEmails::getContactMainEmails($this->_local_entity, $personal_email_type_id);
        $personal_emails = $this->_local_entity->getContactEmails('personal');    
        $mail = $main_emails[0];
        
        // email #1
        if ($mail) {
            $mail->editEmailAddress($mno_email);
        } else {
            $this->_local_entity->addEmail($mno_email, 'personal' , true);
        }
        
        // email #2     
        $mail2 = !is_null($personal_emails) && isset($personal_emails[0])? $personal_emails[0] : null;
        if ($mail2) {
            $mail2->editEmailAddress($mno_email2);
        } else { 
            $this->_local_entity->addEmail($mno_email2, 'personal');
        }

        $mail3 = !is_null($personal_emails) && isset($personal_emails[1])? $personal_emails[1] : null;
        if ($mail3) {
            $mail3->editEmailAddress($mno_email3);
        } else { 
            $this->_local_entity->addEmail($mno_email3, 'personal');
        }
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $workPhone1 = $this->_local_entity->getPhone('work',true) ? $this->_local_entity->getPhoneNumber('work',true) : '';
        $workPhone2 = $this->_local_entity->getPhone('work') ? $this->_local_entity->getPhoneNumber('work') : '';
        $homePhone1 = $this->_local_entity->getPhone('home',true) ? $this->_local_entity->getPhoneNumber('home',true) : '';
        $homePhone2 = $this->_local_entity->getPhone('home') ? $this->_local_entity->getPhoneNumber('home') : '';
        $mobilePhone = $this->_local_entity->getPhone('mobile') ? $this->_local_entity->getPhoneNumber('mobile') : '';
        $fax1 = $this->_local_entity->getPhone('fax') ? $this->_local_entity->getPhoneNumber('fax') : '';
        $fax2 = $this->_local_entity->getPhone('fax',true) ? $this->_local_entity->getPhoneNumber('fax',true) : '';
        $pager = $this->_local_entity->getPhone('pager') ? $this->_local_entity->getPhoneNumber('pager') : '';      
                
        $this->_telephone->work->voice = $this->push_set_or_delete_value($workPhone1, "");
        $this->_telephone->work->voice2 = $this->push_set_or_delete_value($workPhone2, "");
        $this->_telephone->home->voice = $this->push_set_or_delete_value($homePhone1, "");
        $this->_telephone->home->voice2 = $this->push_set_or_delete_value($homePhone2, "");
        $this->_telephone->home->mobile = $this->push_set_or_delete_value($mobilePhone, "");
        $this->_telephone->home->fax = $this->push_set_or_delete_value($fax1, "");
        $this->_telephone->work->fax = $this->push_set_or_delete_value($fax2, "");
        $this->_telephone->home->pager = $this->push_set_or_delete_value($pager, "");
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");                        
        $mainPone = $this->_local_entity->getPhone('work', true);
        if ($mainPone) {
            $mainPone->editNumber($this->pull_set_or_delete_value($this->_telephone->work->voice, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->work->voice, ""), 'work', true);
        }
        $pone2 = $this->_local_entity->getPhone('work');
        if ($pone2) {
            $pone2->editNumber($this->pull_set_or_delete_value($this->_telephone->work->voice2, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->work->voice2, ""), 'work');
        }
        $h_phone = $this->_local_entity->getPhone('home', true);
        if ($h_phone) {
            $h_phone->editNumber($this->pull_set_or_delete_value($this->_telephone->home->voice, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->home->voice, ""), 'home', true);
        }
        $h_phone2 = $this->_local_entity->getPhone('home');
        if ($h_phone2) {
            $h_phone2->editNumber($this->pull_set_or_delete_value($this->_telephone->home->voice2, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->home->voice2, ""), 'home');
        }
        $faxPhone = $this->_local_entity->getPhone('fax',true);
        if ($faxPhone) {
            $faxPhone->editNumber($this->pull_set_or_delete_value($this->_telephone->work->fax, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->work->fax, ""), 'fax', true);
        }
        $h_faxPhone = $this->_local_entity->getPhone('fax');
        if ($h_faxPhone) {
            $h_faxPhone->editNumber($this->pull_set_or_delete_value($this->_telephone->home->fax, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->home->fax, ""), 'fax');
        }
        $h_mobilePhone =  $this->_local_entity->getPhone('mobile');
        if($h_mobilePhone){
            $h_mobilePhone->editNumber($this->pull_set_or_delete_value($this->_telephone->home->mobile, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->home->mobile, ""), 'mobile');
        }
        $h_pagerPhone =  $this->_local_entity->getPhone('pager');
        if($h_pagerPhone){
            $h_pagerPhone->editNumber($this->pull_set_or_delete_value($this->_telephone->home->pager, ""));
        } else {
            $this->_local_entity->addPhone($this->pull_set_or_delete_value($this->_telephone->home->pager, ""), 'pager');
        }
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushWebsites() {
	$this->_log->debug(__FUNCTION__ . " start ");
        $url1 = $this->_local_entity->getWebpage('work') ? cleanUrl($this->_local_entity->getWebpageUrl('work'), false) : '';
        $url2 = $this->_local_entity->getWebpage('personal') ? cleanUrl($this->_local_entity->getWebpageUrl('personal'), false) : '';
        
        $this->_website->url = $this->push_set_or_delete_value($url1, "");
        $this->_website->url2 = $this->push_set_or_delete_value($url2, "");
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullWebsites() {
        $this->_log->debug(__FUNCTION__ . " start ");
        
        $web1 = $this->pull_set_or_delete_value($this->_website->url);
        $web1 = !empty($web1) ? cleanUrl($web1, false) : '';
        
        $w_homepage = $this->_local_entity->getWebpage('work');
        if($w_homepage){
            $w_homepage->editWebpageURL($web1);
        } else {
            $w_homepage = $this->_local_entity->addWebpage($web1, 'work');
        }
        
        $web2 = $this->pull_set_or_delete_value($this->_website->url2);
        $web2 = !empty($web2) ? cleanUrl($web2, false) : '';
        
        $p_homepage = $this->_local_entity->getWebpage('personal');
        if($p_homepage){
            $p_homepage->editWebpageURL($web2);
        } else {
            $p_homepage = $this->_local_entity->addWebpage($web2, 'personal');
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
    protected function pushRole() {
        $this->_log->debug(__FUNCTION__ . " start ");
        
        $local_id = $this->_local_entity->getCompanyId();
        
        if (!empty($local_id)) {
            $mno_id = $this->getMnoIdByLocalId($local_id);
	    $this->_log->debug(__FUNCTION__ . " mno_id = " . json_encode($mno_id));
            
	    if ($this->isValidIdentifier($mno_id)) {    
                $this->_log->debug("is valid identifier");
		$this->_role->organization->id = $mno_id->_id;
                $this->_role->title = $this->push_set_or_delete_value($this->_local_entity->getJobTitle(), "");
            } else if ($this->isDeletedIdentifier($mno_id)) {
                $this->_log->debug(__FUNCTION__ . " deleted identifier");
                // do not update
                return;
	    } else {
                $this->_log->debug("before contacts find by id=" . json_encode($local_id));
                $org_contact = Contacts::findById($local_id);
                $this->_log->debug("after contacts find by id=" . json_encode($local_id));
                
                $organization = new MnoSoaOrganization($this->_db, $this->_log);		
                $status = $organization->send($org_contact);
                $this->_log->debug("after mno soa organization send");
                
				if ($status) {
	                $mno_id = $this->getMnoIdByLocalId($local_id);

    	            if ($this->isValidIdentifer($mno_id)) {
						$this->_role->organization->id = $mno_id->_id;
    	                $this->_role->title = $this->push_set_or_delete_value($this->_local_entity->getJobTitle());
                    }
                }
            }
            
	} else {
            $this->_role = (object) array();
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullRole() {
        if (empty($this->_role->organization->id)) {
            $this->_local_entity->setCompanyId(0);
            $this->_local_entity->setJobTitle("");
        } else {
            $local_id = $this->getLocalIdByMnoIdName($this->_role->organization->id, "organizations");
            if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " local_id = " . json_encode($local_id));
                $this->_local_entity->setCompanyId($local_id->_id);
                $this->_local_entity->setJobTitle($this->pull_set_or_delete_value($this->_role->title, ""));
            } else if ($this->isDeletedIdentifier($local_id)) {
                // do not update
                return;
            } else {
                $notification->entity = "organizations";
                $notification->id = $this->_role->organization->id;
                $organization = new MnoSoaOrganization($this->_db, $this->_log);		
                $status = $organization->receiveNotification($notification);
				if ($status) {
					$local_id = $this->getLocalIdByMnoIdName($this->_role->organization->id, "organizations");
					if ($this->isValidIdentifier($local_id)) {
						$this->_local_entity->setCompanyId($organization->getLocalEntityIdentifier());
						$this->_local_entity->setJobTitle($this->pull_set_or_delete_value($this->_role->title));
					}
				}
            }
        }
    }
    
    // DONE
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_log->debug(__FUNCTION__ . " status=" . $status);
        $this->_log->debug(__FUNCTION__ . " push_to_maestrano=" . $push_to_maestrano);
        if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
            $this->_local_entity->save($push_to_maestrano);
        }
        
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    // DONE
    public function getLocalEntityIdentifier() {
        return $this->_local_entity->getId();
    }
    
    // DONE
    protected function mapHonorificPrefixToSalutation($in) {
        $in_form = strtoupper(trim($in));
        
        switch ($in_form) {
            case "MR": return "MR";
            case "MS": return "MLE";
            case "MRS": return "MME";
            case "DR": return "DR";
            case "MASTER": return "MTRE";
            default: return null;
        }
    }
}

?>