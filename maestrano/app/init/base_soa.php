<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

//-----------------------------------------------
// Load Libraries & Settings
//-----------------------------------------------
require_once MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require_once MAESTRANO_ROOT . '/app/init/_config_loader.php'; //configure MaestranoService

//-----------------------------------------------
// Require Maestrano app specific files
//-----------------------------------------------
define('MNO_APP_DIR', MAESTRANO_ROOT . '/app/');
require_once MNO_APP_DIR . 'soa/MnoSoaEntity.php';
require_once MNO_APP_DIR . 'soa/MnoHooks.php';
require_once MNO_APP_DIR . 'soa/MnoSoaDB.php';
require_once MNO_APP_DIR . 'soa/MnoSoaOrganization.php';
require_once MNO_APP_DIR . 'soa/MnoSoaPerson.php';
