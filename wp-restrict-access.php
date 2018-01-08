<?php
/**
 * Restrict access plugins page to a single user.
 *
 * @package wp-restrict-access
 */

/*
-------------------------------------------------------------------------------
	Plugin Name: WP Restrict Access
	Plugin URI: https://santiagogarza.co
	Description: Restrict access to certain WP admin  pages
	Text Domain: wp-restrict-access
	Author: sgarza
	Author URI: https://santiagogarza.co
	License: GPLv3 or later
	License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
	Version: 1.0.0
------------------------------------------------------------------------------
*/

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Include dependencies */
include_once( 'includes.php' );

/** Instantiate the plugin. */
new WPRestrictAccess();

/**
 * WPRestrictAccess class.
 *
 * @package wp-restrict-access
 * @todo Change class name to be unique to your plugin.
 **/
class WPRestrictAccess {

	/**
	 * Plugin Basename.
	 *
	 * IE: wp-restrict-access/wp-restrict-access.php
	 *
	 * @var string
	 */
	public static $plugin_base_name;

	/**
	 * Path to current plugin directory.
	 *
	 * @var string
	 */
	public static $plugin_base_dir;

	/**
	 * Path to plugin base file.
	 *
	 * @var string
	 */
	public static $plugin_file;

	/**
	 * Plugin Constructor.
	 */
	public function __construct() {
		/* Define Constants */
		static::$plugin_base_name = plugin_basename( __FILE__ );
		static::$plugin_base_dir = plugin_dir_path( __FILE__ );
		static::$plugin_file = __FILE__;

		$this->init();
	}

	/**
	 * Initialize Plugin.
	 */
	private function init() {
		/* Language Support */
		load_plugin_textdomain( 'wp-restrict-access', false, dirname( static::$plugin_base_name ) . '/languages' );

		/* Plugin Activation/De-Activation. */
		register_activation_hook( static::$plugin_file, array( $this, 'activate' ) );
		register_deactivation_hook( static::$plugin_file, array( $this, 'deactivate' ) );

		add_action( 'admin_init', array( $this, 'restrict_admin_with_redirect' ) );
    add_action( 'admin_menu', array( $this, 'modify_admin_menu' ) );
	}

	/**
	 * Method that executes on plugin activation.
	 */
	public function activate() {
		add_action( 'plugins_loaded', 'flush_rewrite_rules' );
	}

	/**
	 * Method that executes on plugin de-activation.
	 */
	public function deactivate() {
		add_action( 'plugins_loaded', 'flush_rewrite_rules' );
	}

	/**
	* If user is not a SuperAdmin, when they try to access the below URLs they are redirected back to the dashboard.
	*
	* @access public
	* @return void
	*/
	function restrict_admin_with_redirect() {
		global $current_user;

		$restrictions = array(
			'/wp-admin/plugins.php',
			'/wp-admin/plugin-install.php',
		);

		$restrictions = apply_filters( 'restrict_access_modify_restrictions', $restrictions );
		foreach ( $restrictions as $restriction ) {
			if ( 'imforza-dev' !== $current_user->user_login && $_SERVER['PHP_SELF'] === $restriction ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}

	/**
	* Disable Theme & Plugin Editors.
	*
	* @access public
	* @return void
	*/
	function modify_admin_menu() {
		global $current_user;

		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
		if ( 'imforza-dev' !== $current_user->user_login ) {
			remove_menu_page( 'plugins.php' );
		}
	}
}
