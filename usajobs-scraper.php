<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wajihtagourty.ml/
 * @since             1.0.0
 * @package           Usajobs_Scraper
 *
 * @wordpress-plugin
 * Plugin Name:       USAjobs Scraper
 * Plugin URI:        https://github.com/kikinass/usajobs-scraper
 * Description:       scraper: fetch and parse job-ads from usajobs-dot-gov and add them to job listings; Requires WP Job Manager.
 * Version:           1.0.0
 * Author:            Wajih
 * Author URI:        https://wajihtagourty.ml/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       usajobs-scraper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('USAJOBS_SCRAPER_VERSION', '1.0.0');

if (!defined('USAJOBS_SCRAPER_PATH')) {
	define('USAJOBS_SCRAPER_PATH', plugin_dir_path(__FILE__));
}


require USAJOBS_SCRAPER_PATH . '/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/kikinass/usajobs-scraper/',
	__FILE__,
	'usajobs-scraper'
);
$myUpdateChecker->setBranch('master');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-usajobs-scraper-activator.php
 */
function activate_usajobs_scraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-usajobs-scraper-activator.php';
	Usajobs_Scraper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-usajobs-scraper-deactivator.php
 */
function deactivate_usajobs_scraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-usajobs-scraper-deactivator.php';
	Usajobs_Scraper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_usajobs_scraper');
register_deactivation_hook(__FILE__, 'deactivate_usajobs_scraper');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-usajobs-scraper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_usajobs_scraper()
{
	setlocale(LC_MONETARY, 'en_US.UTF-8');
	$plugin = new Usajobs_Scraper();
	$plugin->run();
}

run_usajobs_scraper();
