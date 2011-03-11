<?php

//Pandora FMS- http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2011 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

function load_update_manager_lib () {
	set_time_limit (0);
	require_once ('update_manager/load_updatemanager.php');
}

function update_settings_database_connection () {
	global $config;
	
	um_db_connect ('mysql', $config['dbhost'], $config['dbuser'],
			$config['dbpass'], $config['dbname']);
	um_db_update_setting ('dbname', $config['dbname']);
	um_db_update_setting ('dbuser', $config['dbuser']);
	um_db_update_setting ('dbpass', $config['dbpass']);
	um_db_update_setting ('dbhost', $config['dbhost']);
}

function pandora_update_manager_install () {
	global $config;
	
	if (isset ($config['update_manager_installed']))
		/* Already installed */
		return;
	
	load_update_manager_lib ();
	
	/* SQL installation */
	switch ($config['dbtype']) {
		case 'mysql':
			$sentences = file (EXTENSIONS_DIR.'/update_manager/sql/update_manager.sql');
			break;
		case 'postgresql':
			$sentences = file (EXTENSIONS_DIR.'/update_manager/sql/update_manager.postgreSQL.sql');
			break;
	}
	foreach ($sentences as $sentence) {
		$success = process_sql ($sentence);
		if ($success === false)
			return;
	}
	
	$values = array("token" => "update_manager_installed", "value" => 1);
	process_sql_insert('tconfig', $values);
	
	um_db_connect ('mysql', $config['dbhost'], $config['dbuser'],
			$config['dbpass'], $config['dbname']);
	um_db_update_setting ('updating_code_path',
			dirname ($_SERVER['SCRIPT_FILENAME']));
	update_settings_database_connection ();
}

function pandora_update_manager_uninstall () {
	global $config;

	switch ($config["dbtype"]) {
		case "mysql":
			process_sql ('DELETE FROM `tconfig` WHERE `token` = "update_manager_installed"');
			process_sql ('DROP TABLE `tupdate_settings`');
			process_sql ('DROP TABLE `tupdate_journal`');
			process_sql ('DROP TABLE `tupdate`');
			process_sql ('DROP TABLE `tupdate_package`');
			break;
		case "postgresql":
			process_sql ('DELETE FROM "tconfig" WHERE "token" =  \'update_manager_installed\'');
			process_sql ('DROP TABLE "tupdate_settings"');
			process_sql ('DROP TABLE "tupdate_journal"');
			process_sql ('DROP TABLE "tupdate"');
			process_sql ('DROP TABLE "tupdate_package"');
			break;
	}
}

function pandora_update_manager_main () {
	global $config;
	
	if (! check_acl($config['id_user'], 0, "PM")) {
		require ("general/noaccess.php");
		return;
	}

	load_update_manager_lib ();
	update_settings_database_connection ();
	
	require_once ('update_manager/main.php');
}

function pandora_update_manager_login () {
	global $config;
	
	// If first time, make the first autoupdate and disable it in DB
	if (!isset($config["autoupdate"])){
		$config["autoupdate"] = 1;
		
		process_sql_insert('tconfig', array('token' => 'autoupdate', 'value' => 0));
	}

	if ($config["autoupdate"] == 0)
		return;

	load_update_manager_lib ();
	
	um_db_connect ('mysql', $config['dbhost'], $config['dbuser'],
			$config['dbpass'], $config['dbname']);
	$settings = um_db_load_settings ();
	
	$user_key = get_user_key ($settings);

	$package = um_client_check_latest_update ($settings, $user_key);
	
	if (is_object ($package)) {
		echo '<div class="notify">';
		echo '<img src="images/information.png" alt="info" /> ';
		echo __('There\'s a new update for Pandora');
		echo '. <a href="index.php?sec=extensions&amp;sec2=extensions/update_manager">';
		echo __('More info');
		echo '</a>';
		echo '</div>';
	}
}

function pandora_update_manager_godmode () {
	global $config;
	
	load_update_manager_lib ();
	
	require_once ('update_manager/settings.php');
}

if(isset($config['id_user'])) {
	if (check_acl($config['id_user'], 0, "PM")) {
		add_operation_menu_option (__('Update manager'));
		add_godmode_menu_option (__('Update manager settings'), 'PM','gsetup');
		add_extension_main_function ('pandora_update_manager_main');
		add_extension_godmode_function ('pandora_update_manager_godmode');
		add_extension_login_function ('pandora_update_manager_login');
	}
}

pandora_update_manager_install ();

$db = NULL;
?>
