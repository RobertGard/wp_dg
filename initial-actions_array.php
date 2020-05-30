<?php
return [
	[
		'hook'          => 'admin_bar_menu',
		'component'     => 'WP_DG\Includes\WPDG',
		'callback'      => 'toolsAdminBar',
		'priority'      => 80,
		'accepted_args' => 1
	],
	[
		'hook'          => 'wp_ajax_save_regions_list',
		'component'     => 'WP_DG\Includes\WPDG_Saver',
		'callback'      => 'saveRegions',
		'priority'      => 10,
		'accepted_args' => 1
	],
	[
		'hook'          => 'admin_init',
		'component'     => 'WP_DG\Includes\WPDG_Saver',
		'callback'      => 'acfAutoSync',
		'priority'      => 10,
		'accepted_args' => 1
	],
	[
		'hook'          => 'admin_menu',
		'component'     => 'WP_DG\Admin\WPDG_Admin',
		'callback'      => 'addSettingsPage',
		'priority'      => 10,
		'accepted_args' => 1
	],
	[
		'hook'          => 'admin_init',
		'component'     => 'WP_DG\Admin\WPDG_Admin',
		'callback'      => 'settingsPageContent',
		'priority'      => 10,
		'accepted_args' => 1
	]
];