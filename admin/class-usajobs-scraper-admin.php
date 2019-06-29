<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wajihtagourty.ml/
 * @since      1.0.0
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/admin
 * @author     Wajih <Wajih.tagourty@gmail.com>
 */
class Usajobs_Scraper_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	protected $jobTypes;
	protected $jobtype_settings;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;


		add_action('admin_menu', array($this, 'register_usajobs_scraper_admin_menu'));

		add_action('admin_init', array($this, 'prepare_job_type_mapping_options'));
		add_action('admin_init', array($this, 'setup_sections'));
		add_action('admin_init', array($this, 'setup_api_fields'));
		add_action('admin_init', array($this, 'setup_job_type_mapping'));
	}



	/***
	 * Adding plugin menu
	 */
	public function register_usajobs_scraper_admin_menu()
	{
		add_submenu_page('tools.php', 'Usajobs Scrapper Settings', 'UJS Settings', 'manage_options', 'usajobs-scraper-settings', array($this, 'usajobs_scraper_settings_page_render'));
	}



	/**
	 * 
	 */
	public function usajobs_scraper_settings_page_render()
	{
		ob_start();
		include(USAJOBS_SCRAPER_PATH . 'admin/partials/usajobs-scraper-admin-display-settings.php');
		$content = ob_get_contents();
		ob_get_clean();
		echo $content;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/usajobs-scraper-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		$lastUpdate = get_option('usajobs_scraper_update_timer');
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/usajobs-scraper-admin.js', array('jquery'), $this->version, false);
		wp_localize_script(
			'usajobs-scraper',
			'ujsUpdater',
			[
				'last_update' => $lastUpdate
			]

		);
	}







	//sections
	public function setup_sections()
	{
		add_settings_section(
			'usajobs_scraper_settings', //section slug
			'USAJOBS API Access & Job type mapping', //section title
			false, //section display callback
			'usajobs-scraper-settings' //page slug
		);
		add_settings_section(
			'usajobs_scraper_settings',
			'',
			false,
			'usajobs-scraper-settings'
		);
	}


	//seting up fields
	/**
	 * set up usajobs API authentification
	 * @since 1.0.1
	 */
	public function setup_api_fields()
	{
		add_settings_field(
			'usajobs_rest_api_email', //setting slug
			'Email: ', //setting title
			array($this, 'email_field_callback'), //setting display callback
			'usajobs-scraper-settings', //page slug
			'usajobs_scraper_settings' //section slug
		);
		register_setting('usajobs-scraper-settings', 'usajobs_rest_api_email');

		add_settings_field(
			'usajobs_rest_api_auth',
			'API Key: ',
			array($this, 'auth_field_callback'),
			'usajobs-scraper-settings',
			'usajobs_scraper_settings'
		);
		register_setting('usajobs-scraper-settings', 'usajobs_rest_api_auth');
		if (!get_option('usajob_initial_jobs')) {
			add_settings_field(
				'usajobs_initial_jobs',
				'initial job-ads to load',
				array($this, 'initial_jobs_callback'),
				'usajobs-scraper-settings',
				'usajobs_scraper_settings'
			);
			register_setting('usajobs-scraper-settings', 'usajob_initial_jobs');
		}
	}

	/**
	 * setup mapping usajob's work schedules to wp's job types.
	 */

	public function prepare_job_type_mapping_options()
	{
		$this->jobTypes = $this->get_available_job_types();
		if (is_wp_error($this->jobTypes)) {
			$error = $this->jobTypes->get_error_message();
			echo '<div id="message" class="error"><p>' . $error . '</p></div>';
		} else {
			$this->jobTypes = json_decode($this->jobTypes['body']);
			$this->jobtype_settings = [
				'full_time' =>  get_option('usajobs_scraper_type_full_time'),
				'part_time' =>  get_option('usajobs_scraper_type_part_time'),
				'shift_work' =>  get_option('usajobs_scraper_type_shift_work'),
				'intermittent' =>  get_option('usajobs_scraper_type_intermittent'),
				'job_share' =>  get_option('usajobs_scraper_type_job_share'),
				'multiple_schedules' =>  get_option('usajobs_scraper_multiple_schedules'),
			];
		}
	}

	public function setup_job_type_mapping()
	{
		foreach ($this->jobtype_settings as $job_type => $value) {
			$title = key($this->jobtype_settings);
			$title = str_replace('_', ' ', $title);
			$title = ucwords($title);

			$this->add_and_register_radio_settings(
				"usajobs_scraper_type_$job_type",
				$title,
				array($this, 'radio_type_callback'),
				'usajobs-scraper-settings',
				'usajobs_scraper_settings'
			);
			next($this->jobtype_settings);
		}
	}

	public function add_and_register_radio_settings(string $slug, string $title, array $callback, string $page_slug, string $section_slug)
	{
		add_settings_field(
			$slug,
			$title,
			$callback,
			$page_slug,
			$section_slug,
			array(
				"name" => $slug
			)
		);
		register_setting($page_slug, $slug);
	}





	//callbacks
	public function email_field_callback()
	{
		echo '<input name="usajobs_rest_api_email" id="usajobs_rest_api_email" type="text" value="' . get_option('usajobs_rest_api_email') . '" />';
	}
	public function auth_field_callback()
	{
		echo '<input name="usajobs_rest_api_auth" id="usajobs_rest_api_auth" type="text" value="' . get_option('usajobs_rest_api_auth') . '" />';

		echo '<hr>';
	}
	/**
	 * will be updated to radio buttons; do so if you could:
	 */
	public function radio_type_callback($args)
	{
		$name = $args['name'];
		echo '<select name="' . $name . '">';
		echo '<option value="0" selected>--None--</option>';
		foreach ($this->jobTypes as $type) {
			echo '<option value="' . $type->id . '"' . (get_option($name) == $type->id ? 'selected' : '') . '> ' . $type->name  . '</option>';
		}
		echo '</select>';
	}

	public function initial_jobs_callback()
	{
		$name  = 'usajob_initial_jobs';
		echo '<select name="' . $name . '">';
		echo '<option value="42" selected: >--None--</option>';
		//echo '<option value="10" selected: >10</option>';

		for ($i = 20; $i <= 100; $i += 20) {
			echo '<option value="' . $i . '"' . (get_option($name) == $i ? 'selected' : '') . '>' . $i . '</option>';
		}
		echo "</select>";
		echo "</br>";
		echo "*This is only available the first time you initiate the plugin </br> In case of reactivating, you shouldn't change this to avoid duplicating job posts";
		echo '<hr>';
	}






	/**
	 * gets job types from wp api
	 * @since 1.0.1
	 */
	public function get_available_job_types()
	{
		$prefix = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$prefix = 'https';
		}
		$api = $prefix . '://' . $_SERVER['HTTP_HOST'];
		if (preg_match("/localhost/", $api)) {
			$api = "$api/wordpress";
		}
		$api = "$api/wp-json/wp/v2/job-types";
		return wp_remote_get($api);
	}
}
