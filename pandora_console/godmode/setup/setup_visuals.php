<?php 

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2010 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars
global $config;

check_login ();

if (! check_acl ($config['id_user'], 0, "PM") && ! is_user_admin ($config['id_user'])) {
	db_pandora_audit("ACL Violation", "Trying to access Visual Setup Management");
	require ("general/noaccess.php");
	return;
}

// FIX: this constant is declared to in godmode/reporting/reporting_builder.phps
//Constant with fonts directory
define ('_MPDF_TTFONTPATH', $config['homedir'] . '/include/fonts/');

require_once("include/functions_post_process.php");

// Load enterprise extensions
enterprise_include ('godmode/setup/setup_visuals.php');

/*
 NOTICE FOR DEVELOPERS:
 
 Update operation is done in config_process.php
 This is done in that way so the user can see the changes inmediatly.
 If you added a new token, please check config_update_config() in functions_config.php
 to add it there.
*/

require_once ('include/functions_themes.php');
require_once ('include/functions_gis.php');

$table->width = '98%';
$table->data = array ();

$row = 0;

$table->data[$row][0] = __('Date format string') . ui_print_help_icon("date_format", true);
$table->data[$row][1] = '<em>'.__('Example').'</em> '.date ($config["date_format"]);
$table->data[$row][1] .= html_print_input_text ('date_format', $config["date_format"], '', 30, 100, true);

$row++;

if($config['prominent_time'] == 'comparation') {
	$timestamp = false;
	$comparation = true;
}
else if ($config['prominent_time'] == 'timestamp') {
	$timestamp = true;
	$comparation = false;
}

$table->data[$row][0] = __('Timestamp or time comparation') . ui_print_help_icon ("time_stamp-comparation", true);
$table->data[$row][1] = __('Comparation in rollover') . ' ';
$table->data[$row][1] .= html_print_radio_button ('prominent_time', "comparation", '', $comparation, true);
$table->data[$row][1] .= '<br />'.__('Timestamp in rollover').' ';
$table->data[$row][1] .= html_print_radio_button ('prominent_time', "timestamp", '', $timestamp, true);

$row++;

$table->data[$row][0] = __('Graph color (min)');
$table->data[$row][1] = html_print_input_text ('graph_color1', $config["graph_color1"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color (avg)');
$table->data[$row][1] = html_print_input_text ('graph_color2', $config["graph_color2"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color (max)');
$table->data[$row][1] = html_print_input_text ('graph_color3', $config["graph_color3"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #4');
$table->data[$row][1] = html_print_input_text ('graph_color4', $config["graph_color4"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #5');
$table->data[$row][1] = html_print_input_text ('graph_color5', $config["graph_color5"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #6');
$table->data[$row][1] = html_print_input_text ('graph_color6', $config["graph_color6"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #7');
$table->data[$row][1] = html_print_input_text ('graph_color7', $config["graph_color7"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #8');
$table->data[$row][1] = html_print_input_text ('graph_color8', $config["graph_color8"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #9');
$table->data[$row][1] = html_print_input_text ('graph_color9', $config["graph_color9"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graph color #10');
$table->data[$row][1] = html_print_input_text ('graph_color10', $config["graph_color10"], '', 8, 8, true);

$row++;

$table->data[$row][0] = __('Graphic resolution (1-low, 5-high)');
$table->data[$row][1] = html_print_input_text ('graph_res', $config["graph_res"], '', 5, 5, true);

//----------------------------------------------------------------------
// CUSTOM VALUES POST PROCESS
//----------------------------------------------------------------------
$row++;
$table->data[$row][0] = __('Custom values post process');
$table->data[$row][1] = "<table>";
$table->data[$row][1] .= __('Value') . ':&nbsp;' .
	html_print_input_text ('custom_value', '', '', 25, 50, true);
$table->data[$row][1] .= '&nbsp;' . __('Text') . ':&nbsp;' .
	html_print_input_text ('custom_text', '', '', 25, 50, true);
$table->data[$row][1] .= "&nbsp;";
$table->data[$row][1] .= html_print_input_hidden(
	'custom_value_add', '', true);
$table->data[$row][1] .= html_print_button (__('Add'),
	'custom_value_add_btn', false, "", 'class="sub next"', true);

$table->data[$row][1] .= '<br /><br />';

$table->data[$row][1] .= __('Delete custom values') . ': ';
$table->data[$row][1] .= html_print_select(
	post_process_get_custom_values(), 'custom_values', "", "", '', '',
	true);
$count_custom_postprocess = post_process_get_custom_values();
$table->data[$row][1] .= html_print_button (__('Delete'),
	'custom_values_del_btn',
	empty($count_custom_postprocess), "",
	'class="sub cancel"', true);
// This hidden field will be filled from jQuery before submit
$table->data[$row][1] .= html_print_input_hidden(
	'custom_value_to_delete', '', true);
$table->data[$row][1] .= "</table>";
//----------------------------------------------------------------------


//----------------------------------------------------------------------
// CUSTOM INTERVAL VALUES
//----------------------------------------------------------------------
$row++;
$table->data[$row][0] = __('Interval values');
$units = array(
	1 => __('seconds'),
	SECONDS_1MINUTE => __('minutes'),
	SECONDS_1HOUR => __('hours'),
	SECONDS_1DAY => __('days'),
	SECONDS_1MONTH => __('months'),
	SECONDS_1YEAR => __('years'));
$table->data[$row][1] = __('Add new custom value to intervals') . ': ';
$table->data[$row][1] .= html_print_input_text ('interval_value', '', '', 5, 5, true);
$table->data[$row][1] .= html_print_select ($units, 'interval_unit', 1, "", '', '', true, false, false);
$table->data[$row][1] .= html_print_button (__('Add'), 'interval_add_btn', false, "", 'class="sub next"', true);
$table->data[$row][1] .= '<br><br>';

$table->data[$row][1] .= __('Delete interval') . ': ';
$table->data[$row][1] .= html_print_select (get_periods(false, false), 'intervals', "", "", '', '', true);
$table->data[$row][1] .= html_print_button (__('Delete'), 'interval_del_btn', empty($config["interval_values"]), "", 'class="sub cancel"', true);

$table->data[$row][1] .= html_print_input_hidden ('interval_values', $config["interval_values"], true);
// This hidden field will be filled from jQuery before submit
$table->data[$row][1] .= html_print_input_hidden ('interval_to_delete', '', true);
//----------------------------------------------------------------------



$row++;
$table->data[$row][0] = __('Style template');
$table->data[$row][1] = html_print_select (themes_get_css (), 'style', $config["style"].'.css', '', '', '', true);

$row++;

$table->data[$row][0] = __('Block size for pagination');
$table->data[$row][1] = html_print_input_text ('block_size', $config["global_block_size"], '', 5, 5, true);

$row++;

$table->data[$row][0] = __('Use round corners');
$table->data[$row][1] = __('Yes').'&nbsp;'.html_print_radio_button ('round_corner', 1, '', $config["round_corner"], true).'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No').'&nbsp;'.html_print_radio_button ('round_corner', 0, '', $config["round_corner"], true);

$row++;

$table->data[$row][0] = __('Status icon set');
$iconsets["default"] = __('Colors');
$iconsets["faces"] = __('Faces');
$iconsets["color_text"] = __('Colors and text');
$table->data[$row][1] = html_print_select ($iconsets, 'status_images_set', $config["status_images_set"], '', '', '', true);
$table->data[$row][1] .= "&nbsp;" . html_print_button(__("View"), 'status_set_preview', false, '', '', true);

$row++;

$table->data[$row][0] = __('Font path');
$fonts = load_fonts();
$table->data[$row][1] = html_print_select($fonts, 'fontpath', $config["fontpath"], '', '', 0, true);

$row++;

$table->data[$row][0] = __('Font size');

$font_size_array = array(
	1 => 1,
	2 => 2,
	3 => 3,
	4 => 4,
	5 => 5,
	6 => 6,
	7 => 7,
	8 => 8,
	9 => 9,
	10 => 10,
	11 => 11,
	12 => 12,
	13 => 13,
	14 => 14,
	15 => 15);

$table->data[$row][1] = html_print_select($font_size_array, 'font_size', $config["font_size"], '', '', 0, true); 

$row++;

$table->data[$row][0] = __('Interactive charts') . ui_print_help_tip(__('Whether to use Javascript or static PNG graphs'), true);
$table->data[$row][1] = __('Yes').'&nbsp;'.html_print_radio_button ('flash_charts', 1, '', $config["global_flash_charts"], true).'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No').'&nbsp;'.html_print_radio_button ('flash_charts', 0, '', $config["global_flash_charts"], true);

$row++;

$table->data[$row][0] = __('Custom logo') . ui_print_help_icon("custom_logo", true);
$table->data[$row][1] = html_print_select(
	list_files('images/custom_logo', "png", 1, 0), 'custom_logo',
	$config["custom_logo"], '', '', '', true);
$table->data[$row][1] .= "&nbsp;" . html_print_button(__("View"), 'custom_logo_preview', false, '', '', true);

$row++;

$table->data[$row][0] = __('Login background') . ui_print_help_tip(__('You can place your custom images into the folder images/backgrounds/'), true);
$backgrounds_list_jpg = list_files("images/backgrounds", "jpg", 1, 0);
$backgrounds_list_gif = list_files("images/backgrounds", "gif", 1, 0);
$backgrounds_list_png = list_files("images/backgrounds", "png", 1, 0);
$backgrounds_list = array_merge($backgrounds_list_jpg, $backgrounds_list_png);
$backgrounds_list = array_merge($backgrounds_list, $backgrounds_list_gif);
asort($backgrounds_list);
$table->data[$row][1] = html_print_select ($backgrounds_list,
	'login_background', $config["login_background"], '', __('Default'),
	'', true);
$table->data[$row][1] .= "&nbsp;" . html_print_button(__("View"), 'login_background_preview', false, '', '', true);

$row++;

$values = array ();
$values[5] = human_time_description_raw (5);
$values[30] = human_time_description_raw (30);
$values[SECONDS_1MINUTE] = human_time_description_raw(SECONDS_1MINUTE);
$values[SECONDS_2MINUTES] = human_time_description_raw(SECONDS_2MINUTES);
$values[SECONDS_5MINUTES] = human_time_description_raw(SECONDS_5MINUTES);
$values[SECONDS_10MINUTES] = human_time_description_raw(SECONDS_10MINUTES);
$values[SECONDS_30MINUTES] = human_time_description_raw(SECONDS_30MINUTES);

$table->data[$row][0] = __('Default interval for refresh on Visual Console') . ui_print_help_tip(__('This interval will affect to Visual Console pages'), true);
$table->data[$row][1] = html_print_select ($values, 'vc_refr', $config["vc_refr"], '', 'N/A', 0, true, false, false);

$row++;

if (empty($config["vc_line_thickness"])) $config["vc_line_thickness"] = 2;
$table->data[$row][0] = __('Default line thickness for the Visual Console') . ui_print_help_tip(__('This interval will affect to the lines between elements on the Visual Console'), true);
$table->data[$row][1] = html_print_input_text ('vc_line_thickness', $config["vc_line_thickness"], '', 5, 5, true);

$row++;

$table->data[$row][0] = __('Agent size text') . ui_print_help_tip(__('When the agent name have a lot of characters, in some places in Pandora Console it is necesary truncate to N characters.'), true);
$table->data[$row][1] = __('Small:') . html_print_input_text ('agent_size_text_small', $config["agent_size_text_small"], '', 3, 3, true);
$table->data[$row][1] .= ' ' . __('Normal:') . html_print_input_text ('agent_size_text_medium', $config["agent_size_text_medium"], '', 3, 3, true);

$row++;

$table->data[$row][0] = __('Module size text') . ui_print_help_tip(__('When the module name have a lot of characters, in some places in Pandora Console it is necesary truncate to N characters.'), true);
$table->data[$row][1] = __('Small:') . html_print_input_text ('module_size_text_small', $config["module_size_text_small"], '', 3, 3, true);
$table->data[$row][1] .= ' ' . __('Normal:') . html_print_input_text ('module_size_text_medium', $config["module_size_text_medium"], '', 3, 3, true);

$row++;

$table->data[$row][0] = __('Description size text') . ui_print_help_tip(__('When the description name have a lot of characters, in some places in Pandora Console it is necesary truncate to N characters.'), true);
$table->data[$row][1] = html_print_input_text ('description_size_text', $config["description_size_text"], '', 3, 3, true);

$row++;

$table->data[$row][0] = __('Item title size text') .
	ui_print_help_tip(__('When the item title name have a lot of characters, in some places in Pandora Console it is necesary truncate to N characters.'), true);
$table->data[$row][1] = html_print_input_text('item_title_size_text',
	$config["item_title_size_text"], '', 3, 3, true);

$row++;

$table->data[$row][0] = __('GIS Labels') .
	ui_print_help_tip(__('This enabling this, you get a label with agent name in GIS maps. If you have lots of agents in the map, will be unreadable. Disabled by default.'), true);
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('gis_label', 1, '', $config["gis_label"], true).'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('gis_label', 0, '', $config["gis_label"], true);

$row++;

$listIcons = gis_get_array_list_icons();

$arraySelectIcon = array();
foreach ($listIcons as $index => $value) $arraySelectIcon[$index] = $index;

$table->data[$row][0] = __('Default icon in GIS') . ui_print_help_tip(__('Agent icon for GIS Maps. If set to "none", group icon will be used'), true);
$table->data[$row][1] = html_print_select($arraySelectIcon,
	"gis_default_icon", $config["gis_default_icon"], "", __('None'),
		'', true);
$table->data[$row][1] .= "&nbsp;" . html_print_button(__("View"), 'gis_icon_preview', false, '', '', true);

$row++;

// For 5.1 Autohidden menu feature

$table->data['autohidden'][0] = __('Autohidden menu');
$table->data['autohidden'][1] = html_print_checkbox('autohidden_menu',
	1, $config['autohidden_menu'], true);

// Juanma (07/05/2014) New feature: Table for custom front page for reports  

$table->data[$row][0] = __('Custom report front page') .
	ui_print_help_tip(__('Custom report front page. It will be applied to all reports and templates by default.'), true);
$table->data[$row][1] = html_print_checkbox('custom_report_front', 1,
	$config['custom_report_front'], true);

$row++;

$dirItems = scandir($config['homedir'] . '/images/custom_logo');

foreach ($dirItems as $entryDir) {
	if (strstr($entryDir, '.jpg') !== false) {
		$customLogos['images/custom_logo/' . $entryDir] = $entryDir;
	}
}

$_fonts = array();
$dirFonts = scandir(_MPDF_TTFONTPATH);
foreach ($dirFonts as $entryDir) {
	if (strstr($entryDir, '.ttf') !== false) {
		$_fonts[$entryDir] = $entryDir;
	}
}

// Font
$table->data['custom_report_front-font'][0] = __('Custom report front') . ' - ' . __('Font family');
$table->data['custom_report_front-font'][1] = html_print_select ($_fonts,
	'custom_report_front_font', $config['custom_report_front_font'],
	false, __('Default'), '', true);

// Logo
$table->data['custom_report_front-logo'][0] =  __('Custom report front') . ' - ' .
	__('Custom logo') .
	ui_print_help_tip(
		__("The dir of custom logos is in your www Pandora Console in \"images/custom_logo\". You can upload more files (ONLY JPEG) in upload tool in console."), true);
$table->data['custom_report_front-logo'][1] = html_print_select(
	$customLogos,
	'custom_report_front_logo',
	$config['custom_report_front_logo'],
	'showPreview()',
	__('Default'),
	'',
	true);

// Preview
$table->data['custom_report_front-preview'][0] =  __('Custom report front') . ' - ' . 'Preview';
if (empty($config['custom_report_front_logo'])) {
	$config['custom_report_front_logo'] = 'images/pandora_logo_white.jpg';
}
$table->data['custom_report_front-preview'][1] = '<span id="preview_image">' .
	html_print_image ($config['custom_report_front_logo'], true) . '</span>';

// Header
$table->data['custom_report_front-header'][0] =  __('Custom report front') . ' - ' . __('Header');
$table->data['custom_report_front-header'][1] = html_print_textarea('custom_report_front_header', 5, 15,
	$config['custom_report_front_header'], 'style="width: 38em;"', true);

// First page
$table->data['custom_report_front-first_page'][0] =  __('Custom report front') . ' - ' . __('First page');
$table->data['custom_report_front-first_page'][1] = html_print_textarea('custom_report_front_firstpage', 15, 15,
	$config['custom_report_front_firstpage'], 'style="width: 38em; height: 20em;"', true);

// Footer
$table->data['custom_report_front-footer'][0] =  __('Custom report front') . ' - ' . __('Footer');
$table->data['custom_report_front-footer'][1] = html_print_textarea('custom_report_front_footer', 5, 15,
	$config['custom_report_front_footer'], 'style="width: 38em;"', true);


$table->data[$row][0] = __('Paginate module view');
$table->data[$row][1] = html_print_checkbox('paginate_module', 1,
	$config['paginate_module'], true);

$row++;

$table->data[$row][0] = __('Show QR Code icon in the header');
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('show_qr_code_header', 1, '',
		$config["show_qr_code_header"], true) .
	'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('show_qr_code_header', 0, '',
		$config["show_qr_code_header"], true);

$row++;

$table->data[$row][0] = __('Custom graphviz directory') .
	ui_print_help_tip (__("Custom directory where the graphviz binaries are stored."), true);
$table->data[$row][1] = html_print_input_text ('graphviz_bin_dir',
	$config["graphviz_bin_dir"], '', 50, 255, true);

$row++;

$table->data[$row][0] = __('Networkmap max width');
$table->data[$row][1] = html_print_input_text ('networkmap_max_width',
	$config["networkmap_max_width"], '', 10, 20, true);

$row++;

$table->data[$row][0] = __('Fixed header');
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('fixed_header', 1, '', $config["fixed_header"], true) .
	'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('fixed_header', 0, '', $config["fixed_header"], true);

$row++;

$table->data[$row][0] = __('Fixed menu');
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('fixed_menu', 1, '', $config["fixed_menu"], true) .
	'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('fixed_menu', 0, '', $config["fixed_menu"], true);

$row++;

if (!isset($config["short_module_graph_data"]))
	$config["short_module_graph_data"] = true;

$table->data[$row][0] = __('Shortened module graph data');
$table->data[$row][0] .= ui_print_help_tip(__('The data number of the module graphs will be rounded and shortened'), true);
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('short_module_graph_data', 1, '',
		$config["short_module_graph_data"], true) .
	'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('short_module_graph_data', 0, '',
		$config["short_module_graph_data"], true);

$row++;


$table->data[$row][0] = __('Show only the group name');
$table->data[$row][0] .= ui_print_help_tip(
	__('Show the group name instead the group icon.'), true);
$table->data[$row][1] = __('Yes') . '&nbsp;' .
	html_print_radio_button ('show_group_name', 1, '',
		$config["show_group_name"], true) .
	'&nbsp;&nbsp;';
$table->data[$row][1] .= __('No') . '&nbsp;' .
	html_print_radio_button ('show_group_name', 0, '',
		$config["show_group_name"], true);
$row++;

$table->data[$row][0] = __('Default line thickness for the Custom Graph.');
$table->data[$row][1] = html_print_input_text ('custom_graph_widht',
	$config["custom_graph_widht"], '', 5, 5, true);

$row++;


echo '<form id="form_setup" method="post">';
html_print_input_hidden ('update_config', 1);
html_print_table ($table);

echo '<div class="action-buttons" style="width: '.$table->width.'">';
html_print_submit_button (__('Update'), 'update_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

ui_require_css_file ("color-picker");
ui_require_jquery_file ("colorpicker");

function load_fonts() {
	global $config;
	
	$dir = scandir($config['homedir'] . '/include/fonts/');
	
	$fonts = array();
	
	foreach ($dir as $file) {
		if (strstr($file, '.ttf') !== false) {
			$fonts[$config['homedir'] . '/include/fonts/' . $file] = $file;
		}
	}
	
	return $fonts;
}

ui_require_javascript_file('tiny_mce', 'include/javascript/tiny_mce/');

?>
<script language="javascript" type="text/javascript">

// Juanma (07/05/2014) New feature: Custom front page for reports  
function display_custom_report_front (show) {
	
	if (show == true) {
		$('tr#table2-custom_report_front-font').show();
		$('tr#table2-custom_report_front-logo').show();
		$('tr#table2-custom_report_front-preview').show();
		$('tr#table2-custom_report_front-header').show();
		$('tr#table2-custom_report_front-first_page').show();
		$('tr#table2-custom_report_front-footer').show();
	}
	else {
		$('tr#table2-custom_report_front-font').hide();
		$('tr#table2-custom_report_front-logo').hide();
		$('tr#table2-custom_report_front-preview').hide();
		$('tr#table2-custom_report_front-header').hide();
		$('tr#table2-custom_report_front-first_page').hide();
		$('tr#table2-custom_report_front-footer').hide();
	}
	
}

function showPreview() {
	var img_value = $('#custom_report_front_logo').val();
	
	jQuery.post (
		<?php
		echo "'" . ui_get_full_url(false, false, false, false) . "'";
		?> + "/ajax.php",
		{
			"page" : "<?php echo ENTERPRISE_DIR ?>/godmode/reporting/reporting_builder.template_advanced",
			"get_image_path": "1",
			"img_src" : img_value
		},
		function (data, status) {
			$("#preview_image").html(data);
		}
	);
	
}

tinyMCE.init({
	mode : "exact",
	elements: "textarea_custom_report_front_header, textarea_custom_report_front_footer",
	theme : "advanced",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_buttons1 : "bold,italic, |, cut, copy, paste, |, undo, redo",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_statusbar_location : "none"
});

tinyMCE.init({
	mode : "exact",
	elements: "textarea_custom_report_front_firstpage",
	theme : "advanced",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_buttons1 : "bold,italic, |, image, |, cut, copy, paste, |, undo, redo, |, forecolor, |, fontsizeselect, |, justifyleft, justifycenter, justifyright",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	convert_urls : false,
	theme_advanced_statusbar_location : "none"
});

$(document).ready (function () {
	$("#form_setup #text-graph_color1").attachColorPicker();
	$("#form_setup #text-graph_color2").attachColorPicker();
	$("#form_setup #text-graph_color3").attachColorPicker();
	$("#form_setup #text-graph_color4").attachColorPicker();
	$("#form_setup #text-graph_color5").attachColorPicker();
	$("#form_setup #text-graph_color6").attachColorPicker();
	$("#form_setup #text-graph_color7").attachColorPicker();
	$("#form_setup #text-graph_color8").attachColorPicker();
	$("#form_setup #text-graph_color9").attachColorPicker();
	$("#form_setup #text-graph_color10").attachColorPicker();
	
	
	//------------------------------------------------------------------
	// CUSTOM VALUES POST PROCESS
	//------------------------------------------------------------------
	$("#button-custom_values_del_btn").click( function()  {
		var interval_selected = $('#custom_values').val();
		$('#hidden-custom_value_to_delete').val(interval_selected);
		
		$("input[name='custom_value']").val("");
		$("input[name='custom_text']").val("");
		
		$('#submit-update_button').trigger('click');
	});
	
	$("#button-custom_value_add_btn").click( function() {
		$('#hidden-custom_value_add').val(1);
		
		$('#submit-update_button').trigger('click');
	});
	//------------------------------------------------------------------
	
	
	//------------------------------------------------------------------
	// CUSTOM INTERVAL VALUES
	//------------------------------------------------------------------
	$("#button-interval_del_btn").click( function()  {
		var interval_selected = $('#intervals option:selected').val();
		$('#hidden-interval_to_delete').val(interval_selected);
		$('#submit-update_button').trigger('click');
	});
	
	$("#button-interval_add_btn").click( function() {
		$('#submit-update_button').trigger('click');
	});
	//------------------------------------------------------------------
	
	
	// Juanma (06/05/2014) New feature: Custom front page for reports  
	var custom_report = $('#checkbox-custom_report_front')
		.prop('checked');
	display_custom_report_front(custom_report);
	
	$("#checkbox-custom_report_front").click( function()  {
		var custom_report = $('#checkbox-custom_report_front')
			.prop('checked');
		display_custom_report_front(custom_report);
	});
});

// Dialog loaders for the images previews

$("#button-custom_logo_preview").click (function (e) {
	var icon_name = $("select#custom_logo option:selected").val();
	var icon_path = "<?php echo $config['homeurl']; ?>/images/custom_logo/" + icon_name;

	if (icon_name == "")
		return;

	$dialog = $("<div></div>");
	$image = $("<img src=\"" + icon_path + "\">");
	$image
		.css('max-width', '500px')
		.css('max-height', '500px');

	try {
		$dialog
			.hide()
			.html($image)
			.dialog({
				title: "<?php echo __('Logo preview'); ?>",
				resizable: true,
				draggable: true,
				modal: true,
				overlay: {
					opacity: 0.5,
					background: "black"
				},
				minHeight: 1,
				width: $image.width,
				close: function () {
					$dialog
						.empty()
						.remove();
				}
			}).show();
	}
	catch (err) {
		// console.log(err);
	}
});

$("#button-login_background_preview").click (function (e) {
	var icon_name = $("select#login_background option:selected").val();
	var icon_path = "<?php echo $config['homeurl']; ?>/images/backgrounds/" + icon_name;

	if (icon_name == "")
		return;

	$dialog = $("<div></div>");
	$image = $("<img src=\"" + icon_path + "\">");
	$image
		.css('max-width', '500px')
		.css('max-height', '500px');

	try {
		$dialog
			.hide()
			.html($image)
			.dialog({
				title: "<?php echo __('Background preview'); ?>",
				resizable: true,
				draggable: true,
				modal: true,
				overlay: {
					opacity: 0.5,
					background: "black"
				},
				minHeight: 1,
				width: $image.width,
				close: function () {
					$dialog
						.empty()
						.remove();
				}
			}).show();
	}
	catch (err) {
		// console.log(err);
	}
});

$("#button-gis_icon_preview").click (function (e) {
	var icon_prefix = $("select#gis_default_icon option:selected").val();
	var icon_path = "<?php echo $config['homeurl']; ?>/images/gis_map/icons/" + icon_prefix;

	if (icon_prefix == "")
		return;

	$dialog = $("<div></div>");
	$icon_default = $("<img src=\"" + icon_path + ".default.png\">");
	$icon_ok = $("<img src=\"" + icon_path + ".ok.png\">");
	$icon_warning = $("<img src=\"" + icon_path + ".warning.png\">");
	$icon_bad = $("<img src=\"" + icon_path + ".bad.png\">");

	try {
		$dialog
			.hide()
			.empty()
			.append($icon_default)
			.append($icon_ok)
			.append($icon_warning)
			.append($icon_bad)
			.dialog({
				title: "<?php echo __('Gis icons preview'); ?>",
				resizable: true,
				draggable: true,
				modal: true,
				overlay: {
					opacity: 0.5,
					background: "black"
				},
				minHeight: 1,
				close: function () {
					$dialog
						.empty()
						.remove();
				}
			}).show();
	}
	catch (err) {
		// console.log(err);
	}
});

$("#button-status_set_preview").click (function (e) {
	var icon_dir = $("select#status_images_set option:selected").val();
	var icon_path = "<?php echo $config['homeurl']; ?>/images/status_sets/" + icon_dir + "/";

	if (icon_dir == "")
		return;

	$dialog = $("<div></div>");
	$icon_unknown_ball = $("<img src=\"" + icon_path + "agent_down_ball.png\">");
	$icon_unknown = $("<img src=\"" + icon_path + "agent_down.png\">");
	$icon_ok_ball = $("<img src=\"" + icon_path + "agent_ok_ball.png\">");
	$icon_ok = $("<img src=\"" + icon_path + "agent_ok.png\">");
	$icon_warning_ball = $("<img src=\"" + icon_path + "agent_warning_ball.png\">");
	$icon_warning = $("<img src=\"" + icon_path + "agent_warning.png\">");
	$icon_bad_ball = $("<img src=\"" + icon_path + "agent_critical_ball.png\">");
	$icon_bad = $("<img src=\"" + icon_path + "agent_critical.png\">");

	try {
		$dialog
			.hide()
			.empty()
			.append($icon_unknown_ball)
			.append($icon_unknown)
			.append('&nbsp;')
			.append($icon_ok_ball)
			.append($icon_ok)
			.append('&nbsp;')
			.append($icon_warning_ball)
			.append($icon_warning)
			.append('&nbsp;')
			.append($icon_bad_ball)
			.append($icon_bad)
			.css('vertical-align', 'middle')
			.dialog({
				title: "<?php echo __('Status set preview'); ?>",
				resizable: true,
				draggable: true,
				modal: true,
				overlay: {
					opacity: 0.5,
					background: "black"
				},
				minHeight: 1,
				close: function () {
					$dialog
						.empty()
						.remove();
				}
			}).show();
	}
	catch (err) {
		// console.log(err);
	}
});

</script>
