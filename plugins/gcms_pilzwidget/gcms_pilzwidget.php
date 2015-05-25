<?php
/**
* Plugin Name: Pilzwidget
* Description: Erweitert Pilzdatenbank um konfigurierbares Widget
* Version: 0.1
* Author: Grundlagen CMS
*/

if (!function_exists('add_filter')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if (class_exists('pilzDb')) {
	require_once('gcms_pilzwidget_admin.php');
	require_once('gcms_pilzwidget_widget.php');
	require_once('gcms_pilzwidget_widget_view.php');
	
	//add filter, based on options, for changing season format
	add_filter('initSeasonsEntities', 'widget_show_4_seasons', 1, 1);
	//add another for output on widget
	add_filter('initQueryArgumentsForPilzWidget', 'widget_query_4_seasons', 1, 1);

	$pilzWidgetAdmin = new Pilzwidget_Admin();
	$pilzWidgetAdmin->init();

	add_action( 'widgets_init', create_function('', 'return register_widget("Pilzwidget_Widget");') );
	add_action('admin_menu', 'pilzwidget_register_settings_page');
}

function pilzwidget_register_settings_page() {
	add_submenu_page('edit.php?post_type=pilze', __('Widget Settings','pilz-widget'), __('Widget Settings','pilz-widget'), 'manage_options', 'widgetsettings', 'pilzwidget_settings_page');
}

function pilzwidget_settings_page() {	
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>Widget Settings</h2>';
	echo '</div>';
}

function widget_show_4_seasons($theTerms) {
	return array(__('Summer'), __('Autumn'), __('Winter'), __('Spring'));
}

function widget_query_4_seasons($args) {
	$season;

	$dayOfYear = date('z');

	if ($dayOfYear >= 79 && $dayOfYear <= 171) {
		$season = __('Spring');
	} else if ($dayOfYear >= 172 && $dayOfYear <= 264) {
		$season = __('Summer');
	} else if ($dayOfYear >= 265 && $dayOfYear <= 355) {
		$season = __('Autumn');
	} else {
		$season = __('Winter');
	}

	$args['tax_query'][0]['terms'] = $season;

	return $args;
}