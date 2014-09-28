<?php

/*
|--------------------------------------------------------------------------
| Laravel - Magento SOAP Integration Configuration
|--------------------------------------------------------------------------
|
| Remember to set at least one connection prior to testing. This package
| assumes that an explicit connection name will be given, otherwise the
| default connection will be used.
|
| PLEASE NOTE: There is no need to include the API url, just the url to your
| Magento Website. Use the params to set which SOAP version you'd like to use.
|
*/

return array(

	'connections' => [

		// Default connection
		'default'	=>	[
			'site_url'	=>	'http://magentohost',
			'user'		=>	'',
			'key'		=>	'',
			'version'   =>  'v2'
		],

		// You can add as many connections as you'd like, as long
		// as the name (e.g. secondary) is unique
		'secondary'	=>	[
			'site_url'	=>	'http://magentohost',
			'user'		=>	'',
			'key'		=>	'',
			'version'   =>  'v2'
		],
	],

	// enable to see SOAP stack
	'show_stack'	=>	false,
);