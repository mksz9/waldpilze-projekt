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

class Pilzwidget_Main {
	public function init() {
		$this->addWidgetBoxToFrontend();
	}

	//render widget box in frontend
	private function addWidgetBoxToFrontend() {

	}
}

if (class_exists('pilzDb')) {

	require_once('gcms_pilzwidget_admin.php');

	$pilzWidgetAdmin = new Pilzwidget_Admin();
	$pilzWidgetAdmin->init();

	$pilzWidget = new Pilzwidget_Main();
	$pilzWidget->init();
}