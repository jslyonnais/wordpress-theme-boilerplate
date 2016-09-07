<?php
/*
Plugin Name: Error Log Monitor
Plugin URI: http://w-shadow.com/blog/2012/07/25/error-log-monitor-plugin/
Description: Adds a Dashboard widget that displays the last X lines from your PHP error log, and can also send you email notifications about newly logged errors.
Version: 1.4.2
Author: Janis Elsts
Author URI: http://w-shadow.com/
Text Domain: error-log-monitor
*/

//Optimization: Run only in the admin and when doing cron jobs.
if ( !is_admin() && !defined('DOING_CRON') ) {
	return;
}

require dirname(__FILE__) . '/scb/load.php';
require dirname(__FILE__) . '/Elm/PhpErrorLog.php';
require dirname(__FILE__) . '/Elm/DashboardWidget.php';
require dirname(__FILE__) . '/Elm/ExclusiveLock.php';
require dirname(__FILE__) . '/Elm/Plugin.php';

scb_init('error_log_monitor_init');

function error_log_monitor_init() {
	new Elm_Plugin(__FILE__);
}
