<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    // First set $conn variable (need global variable?)
    $user = Contacts::getByUsername($this->uid);
    CompanyWebsite::instance()->logUserIn($user, true);
    //var_dump($_SESSION);
    return true;
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // Build user data hash
      $userData = $this->buildLocalUser();
      
      // Create user (fengo uses top level function for that)
      $user = create_user($userData, '', true);
      $lid = $user->getId();
      
      // Update details straight away
      $this->local_id = $lid;
      if ( $lid ) {
        $result = $this->syncLocalDetails();
        var_dump($result);
      }
      
    }
    
    return $lid;
  }
  
  /**
   * Build a local user
   *
   * @return a hash ready to be used for creation
   */
  protected function buildLocalUser()
  {
    $password = $this->generatePassword();
    
    $userData = array(
      'first_name' => $this->name,
      'surname' => $this->surname,
      'email' => $this->email,
      'username' => $this->uid,
      'type' => $this->getRoleIdToAssign(),
      'password' => $password,
      'password_a' => $password,
      'display_name' => "$this->name $this->surname",
      'company_id' => 1
    );
    
    return $userData;
  }
  
  /**
   * Create the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return the ID of the role
   */
  public function getRoleIdToAssign() {
    $role_id = 4; // Executive
    
    if ($this->app_owner) {
      $role_id = 1; // Super Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role_id = 1;
        } else {
          $role_id = 4;
        }
      }
    }
    
    return $role_id;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $result = DB::execute("SELECT object_id FROM fo_contacts WHERE mno_uid = ? LIMIT 1",$this->uid)->fetchRow();
    if ($result && $result['object_id']) {
      return intval($result['object_id']);
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $result = DB::execute("SELECT contact_id FROM fo_contact_emails WHERE email_address = ? LIMIT 1",$this->email)->fetchRow();
    if ($result && $result['contact_id']) {
      return intval($result['contact_id']);
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       $upd1 = DB::execute("UPDATE fo_contacts SET first_name = ?, surname = ? WHERE object_id = ?",$this->name,$this->surname,$this->local_id);
       $upd2 = DB::execute("UPDATE fo_contact_emails SET email_address = ? WHERE contact_id = ?",$this->email,$this->local_id);
       $upd3 = DB::execute("UPDATE fo_members SET name = ? WHERE object_id = ?","$this->name $this->surname",$this->local_id);
       return $upd1 && $upd2 && $upd3;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $upd = DB::execute("UPDATE fo_contacts SET mno_uid = ? WHERE object_id = ?",$this->uid,$this->local_id);
      return $upd;
    }
    
    return false;
  }
}