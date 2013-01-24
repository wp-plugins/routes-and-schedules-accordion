<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

delete_option('routes_and_schedules_accordion_settings');
?>