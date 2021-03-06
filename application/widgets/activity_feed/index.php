<?php    
    $options = explode(",",user_config_option("filters_dashboard"));
    
    $activities =  ApplicationLogs::getLastActivities();
    $limit = $options[2];
    $acts = array();
    $acts['data'] = array();
    foreach($activities as $activity){
        $user = Contacts::findById($activity->getCreatedById());
        if ($activity->getLogData() == 'member deleted') {
        	$object = Members::findById($activity->getRelObjectId());
        	$member_deleted = true;
        } else {
        	$object = Objects::findObject($activity->getRelObjectId());
        }
        
        if($object || $member_deleted){
            $key = $activity->getRelObjectId() . "-" . $activity->getCreatedById();

            if(count($acts['data']) < ($limit*2)){
                if(!array_key_exists($key, $acts['data'])){
                    $acts['data'][$key] = $object;
                    $acts['created_by'][$key] = $user;
                    $acts['act_data'][$key] = $activity->getActivityDataView($user,$object);
                    $acts['date'][$key] = $activity->getCreatedOn() instanceof DateTimeValue ? friendly_date($activity->getCreatedOn()) : lang('n/a');
                }else{
                    $acts['data'][$key] = $object;
                    $acts['created_by'][$key] = $user;
                    $acts['act_data'][$key] = $activity->getActivityDataView($user,$object,true);
                    $acts['date'][$key] = $activity->getCreatedOn() instanceof DateTimeValue ? friendly_date($activity->getCreatedOn()) : lang('n/a');
                }            
            }else{
                break;
            }        
        }
    }
    $active_members = array();
    $context = active_context();
    foreach ($context as $selection) {
    	if ($selection instanceof Member) $active_members[] = $selection;
    }
    if (count($active_members) > 0) {
    	$mnames = array();
    	$allowed_contact_ids = array();
    	foreach ($active_members as $member) {
    		$mnames[] = clean($member->getName());
    	}
    	$widget_title = lang('activity'). ' '. lang('in').' '. implode(", ", $mnames);
    }
    
    $total = $limit ;
    $genid = gen_id();
    if (count($acts['data']) > 0) {
            include_once 'template.php';
    }
?>