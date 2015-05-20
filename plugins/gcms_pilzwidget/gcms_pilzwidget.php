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

	$pilzWidgetAdmin = new Pilzwidget_Admin();
	$pilzWidgetAdmin->init();

	add_action( 'widgets_init', create_function('', 'return register_widget("Pilzwidget_Widget");') );
}