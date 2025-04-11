<?php
/**
 * Plugin Name:      Yusocial Event Managment
 * Plugin URI:       https://github.com/sadekur/yusocial-event-managment
 * Description:       Yusocial Event Managment is a plugin that allows users to log in using just their email without a password.
 * Version:          0.0.9
 * Requires at least: 5.9
 * Requires PHP:     7.4
 * Author:           Sadekur Rahman
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      yusocial-em
 * Domain Path:      /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
// require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

/**
 * The main plugin class
 */
final class Yusocial_Event_Managment {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '0.0.9';

	/**
	 * Class construcotr
	 */
	private function __construct() {
		$this->define_constants();

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'YUSOCIAL_EM_VERSION', self::version );
		define( 'YUSOCIAL_EM_FILE', __FILE__ );
		define( 'YUSOCIAL_EM_PATH', __DIR__ );
		define( 'YUSOCIAL_EM_URL', plugins_url( '', YUSOCIAL_EM_FILE ) );
		define( 'YUSOCIAL_EM_ASSETS', YUSOCIAL_EM_URL . '/assets' );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {

		new Yusocia\YusocialEventManagment\Assets();
		// new Yusocia\YusocialEventManagment\Email();
		// // new Yusocia\YusocialEventManagment\RestAPI();

		// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 	new Yusocia\YusocialEventManagment\Ajax();
		// }

		if ( is_admin() ) {
			new Yusocia\YusocialEventManagment\Admin();
		} else {
			new Yusocia\YusocialEventManagment\Frontend();
			new Yusocia\YusocialEventManagment\Common();
		}

	}
}


function yusocial_event_managment() {
	return Yusocial_Event_Managment::init();
}

yusocial_event_managment();
