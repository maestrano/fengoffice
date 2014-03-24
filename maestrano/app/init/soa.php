<?php

//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------

if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

require_once MAESTRANO_ROOT . '/app/init/base.php';

?>