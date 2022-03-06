<?php
/**
 * Plugin Name: Lutheran Herald
 * Description: A plugin made for ELDoNA.
 * Author: merctraider
 * Version: 1.5.0
 * Requires PHP: 5.4
 * Author URI: http://merctraider.me
 */

if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

define('LUTHERALD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LUTHERALD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LUTHERALD_PLUGIN_VERSION', get_file_data(__FILE__, array('Version' => 'Version'), false));

//Updater
require_once LUTHERALD_PLUGIN_DIR . 'inc/class-GithubRepo.Updater.php';
$updater = new \GithubRepo\Updater(__FILE__);
$updater->set_username('merctraider');
$updater->set_repository('Lutheran-Herald');
$updater->initialize();

require_once LUTHERALD_PLUGIN_DIR . 'inc/class-lutherald.Core.php';
add_action( 'after_setup_theme', array('\Lutherald\Core','after_setup_theme') );