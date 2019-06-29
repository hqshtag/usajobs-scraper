<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://wajihtagourty.ml/
 * @since      1.0.0
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/includes
 * @author     Wajih <Wajih.tagourty@gmail.com>
 */
class Usajobs_Scraper_Loader
{

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;




	public $number_of_jobs;
	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{

		$this->actions = array();
		$this->filters = array();


		/*$this->add_action('admin_init', $this, 'process_handler');
		$this->add_action('load_script_api', $this, 'load_script');*/
		$this->add_action('admin_init', $this, 'usajobs_scraper_core_fn');

		/*$this->add_filter('cron_schedules', $this, 'custom_30hours_job_recurrence');*/
	}

	public function get_job_type_map()
	{
		return
			[
				'1' => get_option('usajobs_scraper_type_full_time'),
				'2' => get_option('usajobs_scraper_type_part_time'),
				'3' => get_option('usajobs_scraper_type_shift_work'),
				'4' => get_option('usajobs_scraper_type_intermittent'),
				'5' => get_option('usajobs_scraper_type_job_share'),
				'6' => get_option('usajobs_scraper_type_multiple_schedules')
			];
	}

	public function usajobs_scraper_core_fn()
	{
		$update = false;
		$init = false;
		$number = 101;
		//var_dump(get_option('usajob_initial_jobs'));
		if (get_option('usajob_initial_jobs') &&  !get_option('usajobs_scraper_once_01')) {
			$init = true;
			$number  = get_option('usajob_initial_jobs');
			update_option('usajobs_scraper_once_01', 'completed');
			update_option('usajobs_scraper_update_timer', time());
		} else if (get_option('usajobs_scraper_update_timer')) {
			$currentTime = time();
			if ($currentTime > 86400 + get_option('usajobs_scraper_update_timer')) { //108000sec = 30hours
				$how_long = $currentTime - get_option('usajobs_scraper_update_timer');
				$number = floor($how_long / 3600 / 24);
				//$number = 1;
				$update = true;
				$init = true;
				update_option('usajobs_scraper_update_timer', time());
			}
		}


		$prefix = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$prefix = 'https';
		}
		$api = $prefix . '://' . $_SERVER['HTTP_HOST'];
		if (preg_match('/localhost/', $api)) {
			$api = "$api/wordpress";
		}

		$email = get_option('usajobs_rest_api_email');
		$authKey = get_option('usajobs_rest_api_auth');
		$api = "$api/wp-json/wp/v2/job-listings";
		$token = wp_create_nonce('wp_rest');
		$map = $this->get_job_type_map();

		wp_enqueue_script('usajobs-scraper-two', plugin_dir_url(__FILE__) . 'api/usajobs-wp-rest-api.js', time(), false);
		wp_localize_script(
			'usajobs-scraper-two',
			'magicalData',
			[
				"init" => $init,
				"localHost" => [
					"api" => $api,
					"nonce" => $token
				],
				"usajobsAuth" => [
					"email" => $email,
					"key" => $authKey,
				],
				"userSettings" => [
					"typeMap" => $map,
					"number" => $number,
					"update" => $update
				]

			]
		);
	}
	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
	{

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{



		foreach ($this->filters as $hook) {
			add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}

		foreach ($this->actions as $hook) {
			add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}
	}
}
