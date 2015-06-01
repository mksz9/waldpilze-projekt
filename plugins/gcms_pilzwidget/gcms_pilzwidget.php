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

	//register the widget class
	add_action('widgets_init', create_function('', 'return register_widget("Pilzwidget_Widget");'));
	
	$pilzWidgetAdmin = new Pilzwidget_Admin();
	
	//only on plugin installation:
	register_activation_hook(__FILE__, array($pilzWidgetAdmin, 'activate'));
	
	//initialize the taxonomies for custom post type
	add_action('init', array($pilzWidgetAdmin, 'initSeasonsTaxonomy'), 10);

	//initiliaze the admin settings page
	add_action('admin_menu', array($pilzWidgetAdmin, 'pilzwidgetRegisterSettingsPage'));
	
	//other initital functions
	$pilzWidgetAdmin->init();

	//if pilzformular is active -> add the season tax for submitting mushrooms
	if (class_exists('gcms_pf_bootstrap')) {
		require_once('formFields/gcms_pf_widgetTaxonomy.php');
		new gcms_pf_widgetTaxonomy();
	}
}