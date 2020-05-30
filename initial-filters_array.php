<?php
return [
	[
		'hook'          => 'template_include',
		'component'     => 'WP_DG\Includes\WPDG_Highlight',
		'callback'      => 'filterTemplateInclude',
		'priority'      => 1000,
		'accepted_args' => 1
	]
];