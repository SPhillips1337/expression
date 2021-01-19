<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['enable_devlog_alerts'] = 'n';
$config['save_tmpl_files'] = 'y';
// ExpressionEngine Config Items
// Find more configs and overrides at
// https://docs.expressionengine.com/latest/general/system_configuration_overrides.html

$config['app_version'] = '6.0.0';
$config['encryption_key'] = 'e40c51025ccc1ee69ac4d7abe1f45865ddfc2f1e';
$config['session_crypt_key'] = '06e6096bce36fabd032123cc36066ed49b4dfd75';
$config['database'] = array(
	'expressionengine' => array(
		'hostname' => 'localhost',
		'database' => 'expressionengine',
		'username' => 'db_user',
		'password' => 'P@s$w0rd123!',
		'dbprefix' => 'exp_',
		'char_set' => 'utf8mb4',
		'dbcollat' => 'utf8mb4_unicode_ci',
		'port'     => ''
	),
);
$config['show_ee_news'] = 'y';
$config['allow_php'] = 'y';
$config['legacy_member_templates'] = 'y';
// EOF