<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2011 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

/**
 * @package Include
 * @subpackage Reporting
 */

/**
 * Include the usual functions
 */
require_once($config["homedir"] . "/include/functions.php");
require_once($config["homedir"] . "/include/functions_db.php");
require_once($config["homedir"] . "/include/functions_agents.php");
include_once($config["homedir"] . "/include/functions_groups.php");
require_once($config["homedir"] . '/include/functions_graph.php');
include_once($config['homedir'] . "/include/functions_modules.php");
include_once($config['homedir'] . "/include/functions_events.php");
include_once($config['homedir'] . "/include/functions_alerts.php");
include_once($config['homedir'] . '/include/functions_users.php');
enterprise_include_once('include/functions_metaconsole.php');
enterprise_include_once('include/functions_inventory.php');
include_once($config['homedir'] . "/include/functions_forecast.php");
include_once($config['homedir'] . "/include/functions_ui.php");
include_once($config['homedir'] . "/include/functions_netflow.php");

function reporting_get_type($content) {
	switch ($content["type"]) {
		case REPORT_OLD_TYPE_SIMPLE_GRAPH:
			$content["type"] = 'simple_graph';
			break;
		case REPORT_OLD_TYPE_CUSTOM_GRAPH:
			$content["type"] = 'custom_graph';
			break;
		case REPORT_OLD_TYPE_MONITOR_REPORT:
			$content["type"] = 'monitor_report';
			break;
		case REPORT_OLD_TYPE_SLA:
			$content["type"] = 'SLA';
			break;
		case REPORT_OLD_TYPE_AVG_VALUE:
			$content["type"] = 'avg_value';
			break;
		case REPORT_OLD_TYPE_MAX_VALUE:
			$content["type"] = 'max_value';
			break;
		case REPORT_OLD_TYPE_MIN_VALUE:
			$content["type"] = 'min_value';
			break;
		case REPORT_OLD_TYPE_SUMATORY:
			$content["type"] = 'sumatory';
			break;
	}
	
	return $content["type"];
}

function reporting_make_reporting_data($id_report, $date, $time, $period, $type = 'dinamic') {
	$return = array();
	
	switch ($config["dbtype"]) {
		case "mysql":
			$contents = db_get_all_rows_field_filter ("treport_content",
				"id_report", $id_report, "`order`");
			break;
		case "postgresql":
			$contents = db_get_all_rows_field_filter ("treport_content",
				"id_report", $id_report, '"order"');
			break;
		case "oracle":
			$contents = db_get_all_rows_field_filter ("treport_content",
				"id_report", $id_report, '"order"');
			break;
	}
	if ($contents === false) {
		return $return;
	}
	
	$report["group_name"] = groups_get_name ($report['id_group']);
	
	$datetime = strtotime($date . ' ' . $time);
	$report["datetime"] = $datetime;
	
	$report['period'] = $period;
	
	$report['contents'] = array();
	
	foreach ($contents as $content) {
		// Calculate new inteval for all reports
		if ($enable_init_date) {
			if ($datetime_init >= $datetime) {
				$datetime_init = $date_init_less;
			}
			$new_interval = $report['datetime'] - $datetime_init;
			$content['period'] = $new_interval;
		}
		
		switch (reporting_get_type($content)) {
			case 'simple_graph':
				$report['contents'][] =
					reporting_simple_graph(
						$report,
						$content,
						$type);
				break;
		}
	}
	
	return reporting_check_structure_report($return);
}

function reporting_simple_graph($report, $content, $type = 'dinamic') {
	global $config;
	
	//sizgraph_w ¿meter el size en la llamada general?
	
	$return = array();
	
	if (empty($content['name'])) {
		$content['name'] = __('Simple graph');
	}
	
	$module_name = io_safe_output(
		modules_get_agentmodule_name($content['id_agent_module']));
	$agent_name = io_safe_output(
		modules_get_agentmodule_agent_name ($content['id_agent_module']));
	
	
	
	
	$return['title'] = $content['name'];
	$return['subtitle'] = $agent_name . " - " . $module_name;
	$return["description"] = $content["description"];
	$return["date"] = reporting_get_date_text(
		$report['id_rc'],
		$content['id']);
	
	$only_avg = true;
	// Due to database compatibility problems, the 'only_avg' value
	// is stored into the json contained into the 'style' column.
	if (isset($content['style'])) {
		$style_json = io_safe_output($content['style']);
		$style = json_decode($style_json, true);
		
		if (isset($style['only_avg'])) {
			$only_avg = (bool) $style['only_avg'];
		}
	}
	
	$moduletype_name = modules_get_moduletype_name(
		modules_get_agentmodule_type(
			$content['id_agent_module']));
	
	
	
	$return['chart'] = '';
	// Get chart
	switch ($type) {
		case 'dinamic':
			$only_image = false;
			break;
		case 'static':
			$only_image = true;
			break;
		case 'data':
			break;
	}
	
	switch ($type) {
		case 'dinamic':
		case 'static':
			if (preg_match ("/string/", $moduletype_name)) {
				
				$urlImage = ui_get_full_url(false, false, false, false);
				
				$return['chart'] = grafico_modulo_string(
					$content['id_agent_module'],
					$content['period'],
					false,
					$sizgraph_w,
					$sizgraph_h,
					'',
					'',
					false,
					$only_avg,
					false,
					$report["datetime"], $only_image, $urlImage);
				
			}
			else {
				
				$data[0] = grafico_modulo_sparse(
					$content['id_agent_module'],
					$content['period'],
					false,
					$sizgraph_w,
					$sizgraph_h,
					'',
					'',
					false,
					$only_avg,
					true,
					$report["datetime"],
					'',
					0,
					0,
					true,
					$only_image,
					ui_get_full_url(false, false, false, false),
					1,
					false,
					'',
					false,
					true);
			}
			break;
		case 'data':
			break;
	}
	
	return reporting_check_structure_content($return);
}

function reporting_get_date_text($id_report, $id_report_item) {
	global $config;
	
	$return = array();
	$return['date'] = "";
	$return['period'] = "";
	$return['from'] = "";
	$return['to'] = "";
	
	$report = db_get_row('treport_content_item', 'id', $id_report_item);
	$content = db_get_row('treport', 'id_report', $id_report);
	
	if ($content['period'] == 0) {
		$es = json_decode($content['external_source'], true);
		if ($es['date'] == 0) {
			$return['date'] = __('Last data');
		}
		else {
			$return['date'] = date($config["date_format"], $es['date']);
		}
	}
	else {
		$return['period'] = human_time_description_raw ($content['period']);
		$return['from'] = date($config["date_format"], $report["datetime"] - $content['period']);
		$return['from'] = date($config["date_format"], $report["datetime"]);
	}
	
	return $return;
}

/**
 * Check the common items exits
 */
function reporting_check_structure_report($return) {
	if (!isset($return['group_name']))
		$return['group_name'] = "";
	if (!isset($return['title']))
		$return['title'] = "";
	if (!isset($return['datetime']))
		$return['datetime'] = "";
	if (!isset($return['period']))
		$return['period'] = "";
	
	return $return;
}

/**
 * Check the common items exits
 */
function reporting_check_structure_content($return) {
	if (!isset($return['title']))
		$return['title'] = "";
	if (!isset($return['subtitle']))
		$return['subtitle'] = "";
	if (!isset($return['description']))
		$return['description'] = "";
	if (!isset($return["date"])) {
		$return["date"]['date'] = "";
		$return["date"]['period'] = "";
		$return["date"]['from'] = "";
		$return["date"]['to'] = "";
	}
	
	return $return;
}

/** 
 * Get general statistical info on a group
 * 
 * @param int Group Id to get info from. 0 = all
 * 
 * @return array Group statistics
 */
function reporting_get_group_stats ($id_group = 0, $access = 'AR') {
	global $config;
	
	$data = array ();
	$data["monitor_checks"] = 0;
	$data["monitor_not_init"] = 0;
	$data["monitor_unknown"] = 0;
	$data["monitor_ok"] = 0;
	$data["monitor_bad"] = 0; // Critical + Unknown + Warning
	$data["monitor_warning"] = 0;
	$data["monitor_critical"] = 0;
	$data["monitor_not_normal"] = 0;
	$data["monitor_alerts"] = 0;
	$data["monitor_alerts_fired"] = 0;
	$data["monitor_alerts_fire_count"] = 0;
	$data["total_agents"] = 0;
	$data["total_alerts"] = 0;
	$data["total_checks"] = 0;
	$data["alerts"] = 0;
	$data["agents_unknown"] = 0;
	$data["monitor_health"] = 100;
	$data["alert_level"] = 100;
	$data["module_sanity"] = 100;
	$data["server_sanity"] = 100;
	$data["total_not_init"] = 0;
	$data["monitor_non_init"] = 0;
	$data["agent_ok"] = 0;
	$data["agent_warning"] = 0;
	$data["agent_critical"] = 0;
	$data["agent_unknown"] = 0;
	$data["agent_not_init"] = 0;
	
	$cur_time = get_system_time ();
	
	//Check for access credentials using check_acl. More overhead, much safer
	if (!check_acl ($config["id_user"], $id_group, $access)) {
		return $data;
	}
	
	if ($id_group == 0) {
		$id_group = array_keys(
			users_get_groups($config['id_user'], $access, false));
	}
	
	// -----------------------------------------------------------------
	// Server processed stats. NOT realtime (taken from tgroup_stat)
	// -----------------------------------------------------------------
	if ($config["realtimestats"] == 0) {
		
		if (!is_array($id_group)){
			$my_group = $id_group;
			$id_group = array();
			$id_group[0] = $my_group;
		}
		
		foreach ($id_group as $group) {
			$group_stat = db_get_all_rows_sql ("SELECT *
				FROM tgroup_stat, tgrupo
				WHERE tgrupo.id_grupo = tgroup_stat.id_group
					AND tgroup_stat.id_group = $group
				ORDER BY nombre");
			
			$data["monitor_checks"] += $group_stat[0]["modules"];
			$data["agent_not_init"] += $group_stat[0]["non-init"];
			$data["agent_unknown"] += $group_stat[0]["unknown"];
			$data["agent_ok"] += $group_stat[0]["normal"];
			$data["agent_warning"] += $group_stat[0]["warning"];
			$data["agent_critical"] += $group_stat[0]["critical"];
			$data["monitor_alerts"] += $group_stat[0]["alerts"];
			$data["monitor_alerts_fired"] += $group_stat[0]["alerts_fired"];
			$data["monitor_alerts_fire_count"] += $group_stat[0]["alerts_fired"];
			$data["total_checks"] += $group_stat[0]["modules"];
			$data["total_alerts"] += $group_stat[0]["alerts"];
			$data["total_agents"] += $group_stat[0]["agents"];
			$data["agents_unknown"] += $group_stat[0]["agents_unknown"];
			$data["utimestamp"] = $group_stat[0]["utimestamp"];
			
			// This fields are not in database
			$data["monitor_ok"] += (int) groups_get_normal_monitors($group);
			$data["monitor_warning"] += (int) groups_get_warning_monitors($group);
			$data["monitor_critical"] += (int) groups_get_critical_monitors($group);
			$data["monitor_unknown"] += (int) groups_get_unknown_monitors($group);
			$data["monitor_not_init"] += (int) groups_get_not_init_monitors($group);
		}
		
	// -------------------------------------------------------------------
	// Realtime stats, done by PHP Console
	// -------------------------------------------------------------------
	}
	else {
		
		if (!is_array($id_group)) {
			$my_group = $id_group;
			$id_group = array();
			$id_group[0] = $my_group;
		}
		
		// Store the groups where we are quering
		$covered_groups = array();
		$group_array = array();
		foreach ($id_group as $group) {
			$children = groups_get_childrens($group);
			
			//Show empty groups only if they have children with agents
			//$group_array = array();
			
			foreach ($children as $sub) {
				// If the group is quering previously, we ingore it
				if (!in_array($sub['id_grupo'],$covered_groups)) {
					array_push($covered_groups, $sub['id_grupo']);
					array_push($group_array, $sub['id_grupo']);
				}
				
			}
			
			// Add id of this group to create the clause
			// If the group is quering previously, we ingore it
			if (!in_array($group,$covered_groups)) {
				array_push($covered_groups, $group);
				array_push($group_array, $group);
			}
			
			// If there are not groups to query, we jump to nextone
			
			if (empty($group_array)) {
				continue;
			}
		}
		
		if (!empty($group_array)) {
			// FOR THE FUTURE: Split the groups into groups with tags restrictions and groups without it
			// To calculate in the light way the non tag restricted and in the heavy way the others
			/*
			$group_restricted_data = tags_get_acl_tags($config['id_user'], $group_array, $access, 'data');
			$tags_restricted_groups = array_keys($group_restricted_data);
			
			$no_tags_restricted_groups = $group_array;
			foreach ($no_tags_restricted_groups as $k => $v) {
				if (in_array($v, $tags_restricted_groups)) {
					unset($no_tags_restricted_groups[$k]);
				}
			}
			*/
			
			if (!empty($group_array)) {
				// Get monitor NOT INIT, except disabled AND async modules
				$data["monitor_not_init"] += (int) groups_get_not_init_monitors ($group_array, array(), array(), false, false, true);
				
				// Get monitor OK, except disabled and non-init
				$data["monitor_ok"] += (int) groups_get_normal_monitors ($group_array, array(), array(), false, false, true);
				
				// Get monitor CRITICAL, except disabled and non-init
				$data["monitor_critical"] += (int) groups_get_critical_monitors ($group_array, array(), array(), false, false, true);
				
				// Get monitor WARNING, except disabled and non-init
				$data["monitor_warning"] += (int) groups_get_warning_monitors ($group_array, array(), array(), false, false, true);
				
				// Get monitor UNKNOWN, except disabled and non-init
				$data["monitor_unknown"] += (int) groups_get_unknown_monitors ($group_array, array(), array(), false, false, true);
				
				// Get alerts configured, except disabled 
				$data["monitor_alerts"] += groups_monitor_alerts ($group_array) ;
				
				// Get alert configured currently FIRED, except disabled 
				$data["monitor_alerts_fired"] += groups_monitor_fired_alerts ($group_array);
				
				// Calculate totals using partial counts from above
				
				// Get TOTAL non-init modules, except disabled ones and async modules
				$data["total_not_init"] += $data["monitor_not_init"];
			
				// Get TOTAL agents in a group
				$data["total_agents"] += (int) groups_get_total_agents ($group_array, array(), array(), false, false, true);
				
				// Get Agents OK
				$data["agent_ok"] += (int) groups_get_normal_agents ($group_array, array(), array(), false, false, true);
				
				// Get Agents Warning 
				$data["agent_warning"] += (int) groups_get_warning_agents ($group_array, array(), array(), false, false, true);
				
				// Get Agents Critical
				$data["agent_critical"] += (int) groups_get_critical_agents ($group_array, array(), array(), false, false, true);
				
				// Get Agents Unknown
				$data["agent_unknown"] += (int) groups_get_unknown_agents ($group_array, array(), array(), false, false, true);
				
				// Get Agents Not init
				$data["agent_not_init"] += (int) groups_get_not_init_agents ($group_array, array(), array(), false, false, true);
			}
			
			// Get total count of monitors for this group, except disabled.
			$data["monitor_checks"] = $data["monitor_not_init"] + $data["monitor_unknown"] + $data["monitor_warning"] + $data["monitor_critical"] + $data["monitor_ok"];
			
			// Calculate not_normal monitors
			$data["monitor_not_normal"] += $data["monitor_checks"] - $data["monitor_ok"];
		}
		
		// Get total count of monitors for this group, except disabled.
		
		$data["monitor_checks"] = $data["monitor_not_init"] + $data["monitor_unknown"] + $data["monitor_warning"] + $data["monitor_critical"] + $data["monitor_ok"];
		
		/*
		 Monitor health (percentage)
		 Data health (percentage)
		 Global health (percentage)
		 Module sanity (percentage)
		 Alert level (percentage)
		 
		 Server Sanity	0% Uninitialized modules
		 
		 */
	}
	
	if ($data["monitor_unknown"] > 0 && $data["monitor_checks"] > 0) {
		$data["monitor_health"] = format_numeric (100 - ($data["monitor_not_normal"] / ($data["monitor_checks"] / 100)), 1);
	}
	else {
		$data["monitor_health"] = 100;
	}
	
	if ($data["monitor_not_init"] > 0 && $data["monitor_checks"] > 0) {
		$data["module_sanity"] = format_numeric (100 - ($data["monitor_not_init"] / ($data["monitor_checks"] / 100)), 1);
	}
	else {
		$data["module_sanity"] = 100;
	}
	
	if (isset($data["alerts"])) {
		if ($data["monitor_alerts_fired"] > 0 && $data["alerts"] > 0) {
			$data["alert_level"] = format_numeric (100 - ($data	["monitor_alerts_fired"] / ($data["alerts"] / 100)), 1);
		}
		else {
			$data["alert_level"] = 100;
		}
	} 
	else {
		$data["alert_level"] = 100;
		$data["alerts"] = 0;
	}
	
	$data["monitor_bad"] = $data["monitor_critical"] + $data["monitor_warning"];
	
	if ($data["monitor_bad"] > 0 && $data["monitor_checks"] > 0) {
		$data["global_health"] = format_numeric (100 - ($data["monitor_bad"] / ($data["monitor_checks"] / 100)), 1);
	}
	else {
		$data["global_health"] = 100;
	}
	
	$data["server_sanity"] = format_numeric (100 - $data["module_sanity"], 1);
	
	
	$data['alert_fired'] = 0;
	if ($data["monitor_alerts_fired"] > 0) {
		$data['alert_fired'] = 1;
	}
	
	
	if ($data["monitor_critical"] > 0) {
		$data['status'] = 'critical';
	}
	elseif ($data["monitor_warning"] > 0) {
		$data['status'] = 'warning';
	}
	elseif (($data["monitor_unknown"] > 0) ||  ($data["agents_unknown"] > 0)) {
		$data['status'] = 'unknown';
	}
	elseif ($data["monitor_ok"] > 0)  {
		$data['status'] = 'ok';
	}
	elseif ($data["agent_not_init"] > 0)  {
		$data['status'] = 'not_init';
	}
	else {
		$data['status'] = 'none';
	}
	
	return ($data);
}

function reporting_get_stats_indicators($data, $width = 280, $height = 20, $html = true) {
	$table_ind = html_get_predefined_table();
	
	$servers = array();
	$servers["all"] = (int) db_get_value ('COUNT(id_server)','tserver');
	$servers["up"] = (int) servers_check_status ();
	$servers["down"] = $servers["all"] - $servers["up"];
	if ($servers["all"] == 0) {
		$servers["health"] = 0;
	}
	else {
		$servers["health"] = $servers["up"] / ($servers["all"] / 100);
	}
	
	if ($html) {
		$tdata[0] = '<fieldset class="databox tactical_set">
						<legend>' . 
							__('Server health') . ui_print_help_tip (sprintf(__('%d Downed servers'), $servers["down"]), true) . 
						'</legend>' . 
						progress_bar($servers["health"], $width, $height, '', 0) . '</fieldset>';
		$table_ind->rowclass[] = '';
		$table_ind->data[] = $tdata;
		
		$tdata[0] = '<fieldset class="databox tactical_set">
						<legend>' . 
							__('Monitor health') . ui_print_help_tip (sprintf(__('%d Not Normal monitors'), $data["monitor_not_normal"]), true) . 
						'</legend>' . 
						progress_bar($data["monitor_health"], $width, $height, $data["monitor_health"].'% '.__('of monitors up'), 0) . '</fieldset>';
		$table_ind->rowclass[] = '';
		$table_ind->data[] = $tdata;
		
		$tdata[0] = '<fieldset class="databox tactical_set">
						<legend>' . 
							__('Module sanity') . ui_print_help_tip (sprintf(__('%d Not inited monitors'), $data["monitor_not_init"]), true) .
						'</legend>' . 
						progress_bar($data["module_sanity"], $width, $height, $data["module_sanity"].'% '.__('of total modules inited'), 0) . '</fieldset>';
		$table_ind->rowclass[] = '';
		$table_ind->data[] = $tdata;
		
		$tdata[0] = '<fieldset class="databox tactical_set">
						<legend>' . 
							__('Alert level') . ui_print_help_tip (sprintf(__('%d Fired alerts'), $data["monitor_alerts_fired"]), true) . 
						'</legend>' . 
						progress_bar($data["alert_level"], $width, $height, $data["alert_level"].'% '.__('of defined alerts not fired'), 0) . '</fieldset>';
		$table_ind->rowclass[] = '';
		$table_ind->data[] = $tdata;
		
		
		return html_print_table($table_ind, true);
	}
	else {
		$return = array();
		
		$return['server_health'] = array(
			'title' => __('Server health'),
			'graph' => progress_bar($servers["health"], $width, $height, '', 0));
		$return['monitor_health'] = array(
			'title' => __('Monitor health'),
			'graph' => progress_bar($data["monitor_health"], $width, $height, $data["monitor_health"].'% '.__('of monitors up'), 0));
		$return['module_sanity'] = array(
			'title' => __('Module sanity'),
			'graph' => progress_bar($data["module_sanity"], $width, $height, $data["module_sanity"].'% '.__('of total modules inited'), 0));
		$return['alert_level'] = array(
			'title' => __('Alert level'),
			'graph' => progress_bar($data["alert_level"], $width, $height, $data["alert_level"].'% '.__('of defined alerts not fired'), 0));
		
		return $return;
	}
}

function reporting_get_stats_alerts($data, $links = false) {
	global $config;
	
	// Link URLS
	$mobile = false;
	if (isset($data['mobile'])) {
		if ($data['mobile']) {
			$mobile = true;
		}
	}
	
	if ($mobile) {
		$urls = array();
		$urls['monitor_alerts'] = "index.php?page=alerts&status=all_enabled";
		$urls['monitor_alerts_fired'] = "index.php?page=alerts&status=fired";
	}
	else {
		$urls = array();
		if ($links) {
			$urls['monitor_alerts'] = "index.php?sec=estado&sec2=operation/agentes/alerts_status&pure=" . $config['pure'];
			$urls['monitor_alerts_fired'] = "index.php?sec=estado&sec2=operation/agentes/alerts_status&filter=fired&pure=" . $config['pure'];
		} else {
			$urls['monitor_alerts'] = "index.php?sec=estado&amp;sec2=operation/agentes/alerts_status&amp;refr=60";
			$urls['monitor_alerts_fired'] = "index.php?sec=estado&amp;sec2=operation/agentes/alerts_status&amp;refr=60&filter=fired";
		}
	}
	
	// Alerts table
	$table_al = html_get_predefined_table();
	
	$tdata = array();
	$tdata[0] = html_print_image('images/bell.png', true, array('title' => __('Defined alerts')));
	$tdata[1] = $data["monitor_alerts"] <= 0 ? '-' : $data["monitor_alerts"];
	$tdata[1] = '<a class="big_data" href="' . $urls["monitor_alerts"] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = html_print_image('images/bell_error.png', true, array('title' => __('Fired alerts')));
	$tdata[3] = $data["monitor_alerts_fired"] <= 0 ? '-' : $data["monitor_alerts_fired"];
	$tdata[3] = '<a style="color: ' . COL_ALERTFIRED . ';" class="big_data" href="' . $urls["monitor_alerts_fired"] . '">' . $tdata[3] . '</a>';
	$table_al->rowclass[] = '';
	$table_al->data[] = $tdata;
	
	if (!defined('METACONSOLE')) {
		$output = '<fieldset class="databox tactical_set">
					<legend>' . 
						__('Defined and fired alerts') . 
					'</legend>' . 
					html_print_table($table_al, true) . '</fieldset>';
	}
	else {
		$table_al->class = "tactical_view";
		$table_al->style = array();
		$output = '<fieldset class="tactical_set">
					<legend>' . 
						__('Defined and fired alerts') . 
					'</legend>' . 
					html_print_table($table_al, true) . '</fieldset>';
	}
	return $output;
}


function reporting_get_stats_modules_status($data, $graph_width = 250, $graph_height = 150, $links = false, $data_agents=false) {
	global $config;
	
	// Link URLS
	if ($links === false) {
		$urls = array();
		$urls['monitor_critical'] = "index.php?" .
			"sec=estado&amp;sec2=operation/agentes/status_monitor&amp;" .
			"refr=60&amp;status=" . AGENT_MODULE_STATUS_CRITICAL_BAD . "&pure=" . $config['pure'];
		$urls['monitor_warning'] = "index.php?" .
			"sec=estado&amp;sec2=operation/agentes/status_monitor&amp;" .
			"refr=60&amp;status=" . AGENT_MODULE_STATUS_WARNING . "&pure=" . $config['pure'];
		$urls['monitor_ok'] = "index.php?" .
			"sec=estado&amp;sec2=operation/agentes/status_monitor&amp;" .
			"refr=60&amp;status=" . AGENT_MODULE_STATUS_NORMAL . "&pure=" . $config['pure'];
		$urls['monitor_unknown'] = "index.php?" .
			"sec=estado&amp;sec2=operation/agentes/status_monitor&amp;" .
			"refr=60&amp;status=" . AGENT_MODULE_STATUS_UNKNOWN . "&pure=" . $config['pure'];
		$urls['monitor_not_init'] = "index.php?" .
			"sec=estado&amp;sec2=operation/agentes/status_monitor&amp;" .
			"refr=60&amp;status=" . AGENT_MODULE_STATUS_NOT_INIT . "&pure=" . $config['pure'];
	}
	else {
		$urls = array();
		$urls['monitor_critical'] = $links['monitor_critical'];
		$urls['monitor_warning'] = $links['monitor_warning'];
		$urls['monitor_ok'] = $links['monitor_ok'];
		$urls['monitor_unknown'] = $links['monitor_unknown'];
		$urls['monitor_not_init'] = $links['monitor_not_init'];
	}
	
	// Modules by status table
	$table_mbs = html_get_predefined_table();
	
	$tdata = array();
	$tdata[0] = html_print_image('images/module_critical.png', true, array('title' => __('Monitor critical')));
	$tdata[1] = $data["monitor_critical"] <= 0 ? '-' : $data["monitor_critical"];
	$tdata[1] = '<a style="color: ' . COL_CRITICAL . ';" class="big_data" href="' . $urls['monitor_critical'] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = html_print_image('images/module_warning.png', true, array('title' => __('Monitor warning')));
	$tdata[3] = $data["monitor_warning"] <= 0 ? '-' : $data["monitor_warning"];
	$tdata[3] = '<a style="color: ' . COL_WARNING_DARK . ';" class="big_data" href="' . $urls['monitor_warning'] . '">' . $tdata[3] . '</a>';
	$table_mbs->rowclass[] = '';
	$table_mbs->data[] = $tdata;
	
	$tdata = array();
	$tdata[0] = html_print_image('images/module_ok.png', true, array('title' => __('Monitor normal')));
	$tdata[1] = $data["monitor_ok"] <= 0 ? '-' : $data["monitor_ok"];
	$tdata[1] = '<a style="color: ' . COL_NORMAL . ';" class="big_data" href="' . $urls["monitor_ok"] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = html_print_image('images/module_unknown.png', true, array('title' => __('Monitor unknown')));
	$tdata[3] = $data["monitor_unknown"] <= 0 ? '-' : $data["monitor_unknown"];
	$tdata[3] = '<a style="color: ' . COL_UNKNOWN . ';" class="big_data" href="' . $urls["monitor_unknown"] . '">' . $tdata[3] . '</a>';
	$table_mbs->rowclass[] = '';
	$table_mbs->data[] = $tdata;
	
	$tdata = array();
	$tdata[0] = html_print_image('images/module_notinit.png', true, array('title' => __('Monitor not init')));
	$tdata[1] = $data["monitor_not_init"] <= 0 ? '-' : $data["monitor_not_init"];
	$tdata[1] = '<a style="color: ' . COL_NOTINIT . ';" class="big_data" href="' . $urls["monitor_not_init"] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = $tdata[3] = '';
	$table_mbs->rowclass[] = '';
	$table_mbs->data[] = $tdata;
	
	if ($data["monitor_checks"] > 0) {
		$tdata = array();
		$table_mbs->colspan[count($table_mbs->data)][0] = 4;
		$table_mbs->cellstyle[count($table_mbs->data)][0] = 'text-align: center;';
		$tdata[0] = '<div id="outter_status_pie" style="height: ' . $graph_height . 'px">' .
			'<div id="status_pie" style="margin: auto; width: ' . $graph_width . 'px;">' .
				graph_agent_status(false, $graph_width, $graph_height, true, true, $data_agents) .
			'</div></div>';
		$table_mbs->rowclass[] = '';
		$table_mbs->data[] = $tdata;
	}
	
	if (!defined("METACONSOLE")) {
		$output = '
			<fieldset class="databox tactical_set">
				<legend>' . 
					__('Monitors by status') . 
				'</legend>' . 
				html_print_table($table_mbs, true) .
			'</fieldset>';
	}
	else {
		$table_mbs->class = "tactical_view";
		$table_mbs->style=array();
		$output = '
			<fieldset class="tactical_set">
				<legend>' . 
					__('Monitors by status') . 
				'</legend>' . 
				html_print_table($table_mbs, true) .
			'</fieldset>';
	}
	
	return $output;
}

function reporting_get_stats_agents_monitors($data) {
	global $config;
	
	// Link URLS
	$mobile = false;
	if (isset($data['mobile'])) {
		if ($data['mobile']) {
			$mobile = true;
		}
	}
	
	if ($mobile) {
		$urls = array();
		$urls['total_agents'] = "index.php?page=agents";
		$urls['monitor_checks'] = "index.php?page=modules";
	}
	else {
		$urls = array();
		$urls['total_agents'] = "index.php?sec=estado&amp;sec2=operation/agentes/estado_agente&amp;refr=60";
		$urls['monitor_checks'] = "index.php?sec=estado&amp;sec2=operation/agentes/status_monitor&amp;refr=60&amp;status=-1";
	}
	
	// Agents and modules table
	$table_am = html_get_predefined_table();
	
	$tdata = array();
	$tdata[0] = html_print_image('images/agent.png', true, array('title' => __('Total agents')));
	$tdata[1] = $data["total_agents"] <= 0 ? '-' : $data["total_agents"];
	$tdata[1] = '<a class="big_data" href="' . $urls['total_agents'] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = html_print_image('images/module.png', true, array('title' => __('Monitor checks')));
	$tdata[3] = $data["monitor_checks"] <= 0 ? '-' : $data["monitor_checks"];
	$tdata[3] = '<a class="big_data" href="' . $urls['monitor_checks'] . '">' . $tdata[3] . '</a>';
	$table_am->rowclass[] = '';
	$table_am->data[] = $tdata;
	
	$output = '<fieldset class="databox tactical_set">
				<legend>' . 
					__('Total agents and monitors') . 
				'</legend>' . 
				html_print_table($table_am, true) . '</fieldset>';
	
	return $output;
}

function reporting_get_stats_users($data) {
	global $config;
	
	// Link URLS
	$urls = array();
	if (check_acl ($config['id_user'], 0, "UM")) {
		$urls['defined_users'] = "index.php?sec=gusuarios&amp;sec2=godmode/users/user_list";
	}
	else {
		$urls['defined_users'] = 'javascript:';
	}
	
	// Users table
	$table_us = html_get_predefined_table();
	
	$tdata = array();
	$tdata[0] = html_print_image('images/user_green.png', true, array('title' => __('Defined users')));
	$tdata[1] = count (get_users ());
	$tdata[1] = '<a class="big_data" href="' . $urls["defined_users"] . '">' . $tdata[1] . '</a>';
	
	$tdata[2] = $tdata[3] = '&nbsp;';
	$table_us->rowclass[] = '';
	$table_us->data[] = $tdata;
	
	$output = '<fieldset class="databox tactical_set">
				<legend>' . 
					__('Users') . 
				'</legend>' . 
				html_print_table($table_us, true) . '</fieldset>';
	
	return $output;
}
?>