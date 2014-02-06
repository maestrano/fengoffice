-- <?php echo $table_prefix ?> <?php echo $table_prefix ?>
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
	('task panel','tasksDateStart','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'date from to filter out task list'),
    ('task panel','tasksDateEnd','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'the date up to filter the list of tasks'),
	('task panel', 'reminders_tasks', 'reminder_email,1,1440', 'StringConfigHandler', '0', '23', NULL),
 	('task panel', 'add_task_autoreminder', '0', 'BoolConfigHandler', '0', '21', NULL),
 	('task panel', 'add_self_task_autoreminder', '1', 'BoolConfigHandler', '0', '22', NULL), 	
 	('task panel', 'add_task_default_reminder', '1', 'BoolConfigHandler', '0', '20', NULL),
	('calendar panel', 'add_event_autoreminder', '1', 'BoolConfigHandler', '0', '0', NULL),
	('calendar panel', 'autoassign_events', '0', 'BoolConfigHandler', '0', '0', NULL),
	('calendar panel', 'event_send_invitations', '1', 'BoolConfigHandler', '0', '0', NULL),
	('calendar panel', 'event_subscribe_invited', '1', 'BoolConfigHandler', '0', '0', NULL),
	('mails panel', 'mails_per_page', '50', 'IntegerConfigHandler', '0', '0', NULL),
	('general', 'access_member_after_add', '1', 'BoolConfigHandler', '0', '1300', NULL),
	('general', 'access_member_after_add_remember', '0', 'BoolConfigHandler', '0', '1301', NULL),
	('general', 'sendEmailNotification', '1', 'BoolConfigHandler', '1', '0', 'Send email notification to new user'),
 	('general', 'viewContactsChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view contacts checked'),
 	('general', 'viewUsersChecked', '0', 'BoolConfigHandler', '1', '0', 'in people panel is view users checked'),
 	('general', 'viewCompaniesChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view companies checked'),
 	('dashboard', 'overviewAsList', '0', 'BoolConfigHandler', '1', '0', 'View Overview as list'),
	('general', 'contacts_per_page', '50', 'IntegerConfigHandler', '0', '1200', NULL)
ON DUPLICATE KEY UPDATE name=name;

DELETE FROM `<?php echo $table_prefix ?>contact_config_option_values` WHERE `option_id` = ( SELECT `id` FROM `<?php echo $table_prefix ?>contact_config_options` WHERE `name` = 'updateOnLinkedObjects');
DELETE FROM `<?php echo $table_prefix ?>contact_config_options` WHERE `name` = 'updateOnLinkedObjects';

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
	('general', 'can_assign_tasks_to_companies', '1', 'BoolConfigHandler', '0'),
	('general', 'updateOnLinkedObjects', '0', 'BoolConfigHandler', '0'),
	('general', 'use_object_properties', '0', 'BoolConfigHandler', '0')
ON DUPLICATE KEY UPDATE name=name;
UPDATE `<?php echo $table_prefix ?>config_options` SET `value` = if ((SELECT count(*) FROM <?php echo $table_prefix ?>object_properties)>0, 1, 0) WHERE `name`='use_object_properties';
UPDATE `<?php echo $table_prefix ?>config_options` SET `value` = '1' WHERE `name`='can_assign_tasks_to_companies';

INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
	SELECT contact_id, CONCAT('email_adress', id), email_address , '0' FROM <?php echo $table_prefix ?>contact_emails
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
	SELECT contact_id, CONCAT('phone_number', id), number, '0' FROM <?php echo $table_prefix ?>contact_telephones
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
	SELECT contact_id, CONCAT('web_url', id), url , '0' FROM <?php echo $table_prefix ?>contact_web_pages
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
	SELECT contact_id, CONCAT('im_value', id), value , '0' FROM <?php echo $table_prefix ?>contact_im_values
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
	SELECT contact_id, CONCAT('address', id), CONCAT(street,' ',city,' ',state,' ',country,' ',zip_code) , '0' FROM <?php echo $table_prefix ?>contact_addresses
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;

ALTER TABLE  <?php echo $table_prefix ?>event_invitations ADD synced int(1) DEFAULT '0';
ALTER TABLE  <?php echo $table_prefix ?>event_invitations ADD special_id text <?php echo $default_collation ?> NOT NULL DEFAULT '';

update <?php echo $table_prefix ?>contacts set company_id=0 where company_id is null;
update <?php echo $table_prefix ?>contacts set display_name='' where display_name is null;
update <?php echo $table_prefix ?>contacts set avatar_file='' where avatar_file is null;
update <?php echo $table_prefix ?>contacts set last_login='0000-00-00 00:00:00' where last_login is null;
update <?php echo $table_prefix ?>contacts set last_visit='0000-00-00 00:00:00' where last_visit is null;
update <?php echo $table_prefix ?>contacts set personal_member_id=0 where personal_member_id is null;

update <?php echo $table_prefix ?>config_options set is_system=1 where name='exchange_compatible';