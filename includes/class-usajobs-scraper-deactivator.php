<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://wajihtagourty.ml/
 * @since      1.0.0
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/includes
 * @author     Wajih <Wajih.tagourty@gmail.com>
 */
class Usajobs_Scraper_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		delete_option('usajobs_scraper_once_01');
		delete_option('usajob_initial_jobs');
		delete_option('usajobs_scraper_update_timer');
	}
}
