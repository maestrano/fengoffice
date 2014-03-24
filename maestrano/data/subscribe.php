<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../'));
}

require_once(MAESTRANO_ROOT . '/app/init/_soa_subscribe.php');

$maestrano = MaestranoService::getInstance();

if ($maestrano->isSoaEnabled() and $maestrano->getSoaUrl()) {
    $log = new MnoSoaBaseLogger();
    $log->debug("Subscribe received a notification");

    $notification = json_decode(file_get_contents('php://input'), false);
    $notification_entity = strtoupper(trim($notification->entity));

    $log->debug("Notification = ". json_encode($notification));

    switch ($notification_entity) {
            case "ORGANIZATIONS":
                $mno_org = new MnoSoaOrganization('', new MnoSoaBaseLogger());		
                $mno_org->receiveNotification($notification);
                break;
            case "PERSONS":
                $mno_person = new MnoSoaPerson('', new MnoSoaBaseLogger());		
                $mno_person->receiveNotification($notification);
                break;
    }
}

?>
