<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.tenbucks.io
 * @since      1.0.0
 *
 * @package    Tenbucks
 * @subpackage Tenbucks/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tenbucks
 * @subpackage Tenbucks/admin
 * @author     Your Name <email@example.com>
 */
class Tenbucks_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tenbucks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tenbucks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tenbucks-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tenbucks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tenbucks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tenbucks-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function ajax_handler()
	{
		add_action('wp_ajax_tenbucks_create_key', array($this, 'create_key'));
	}

	/**
	 * Add Tenbucks admin menu
	 *
	 * @since    1.0.0
	 */
	public function admin_menu()
	{
		$page = add_menu_page('tenbucks', 'tenbucks', 'manage_woocommerce', 'tenbucks', array($this, 'get_content'), $this->get_asset_path('logo.png'), 58.42);
		add_action('admin_print_styles-' . $page, array($this, 'enqueue_styles'));
		add_action('admin_print_scripts-' . $page, array($this, 'enqueue_scripts'));
	}

	/**
	 * Get admin menu content
	 *
	 * @since 1.0.0
	 */
	public function get_content()
	{
		// First check if WooCommerce is active...
		if (!is_plugin_active( 'woocommerce/woocommerce.php' )) {
			return print('<h2 class="clear">'.__('Please install WooCommerce before using this plugin.', 'tenbucks').'</h2>');
		}

		$wc_data = get_plugin_data(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php');

		if (version_compare ( $wc_data['Version'] , '2.4.0', '<')) {
			return print('<h2 class="clear">'.__('Please update your WooCommerce plugin before using this plugin.', 'tenbucks').'</h2>');
		}


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wic-server.php';
		$is_ssl = is_ssl();
		$shop_url = get_site_url();
		$display_iframe = (bool)get_option('tenbucks_registration_complete');
		$api_doc_link = sprintf('<a href="%s" target="_blank">%s</a>', 'http://docs.woothemes.com/document/woocommerce-rest-api/', __('See how', 'tenbucks'));
		$is_api_active = get_option('woocommerce_api_enabled') === 'yes';
		$lang_infos = explode('-', get_bloginfo('language'));
		$query = array(
			'url' => $shop_url,
			'timestamp' => (int)microtime(true),
			'platform' => 'WooCommerce',
			'cms_version' => $wc_data['Version'],
			'module_version' => $this->version
		);

		if (!$is_ssl)
		{
			$ssl_message = __('You\'re not using SSL. For safety reasons, our iframe use <strong>https protocol</strong> to secure every transactions', 'tenbucks');
			$pp_url = 'http://store.webincolor.fr/conditions-generales-de-ventes';
			$pp_link = sprintf('<a href="%s" target="_blank">%s</a>', $pp_url, __('More informations about our privacy policy', 'tenbucks'));
			$this->add_notice($ssl_message.'. '.$pp_link.'.', 'info');
		}

		// If API is disabled.
		if (!$is_api_active) {
				$this->add_notice(__('WooCommerce API is not enabled. Please activate it and create an API read/write access before using this plugin.', 'tenbucks').' '.$api_doc_link, 'error');
		} else {
			$api_details = array();
			preg_match('/\/wc-api\/v(\d)\/$/',  get_woocommerce_api_url('/'), $api_details);
			$api_url = $api_details[0];
			$api_version = (int)$api_details[1];

			if ($api_version > 1) {
				$query['api_version'] = $api_version;
				$standalone_url = WIC_Server::getUrl('/', $query, true);
				$iframe_url = WIC_Server::getUrl('/', $query);

			} else {
				$display_iframe = false;
				$this->add_notice(__('Your WooCommerce version is obsolete, please update it before using this plugin.', 'tenbucks'), 'error');
			}
		}

		// Debug Mod prevent JSON responses to be correctly parsed
		if (WP_DEBUG)
		{
			$message = __('WP_DEBUG is active. This can prevent our WooCommerce responses to be parsed correctly and cause malfunctioning.', 'tenbucks');
			$this->add_notice($message, 'error');
		}
		$template_name = $display_iframe ? 'tenbucks-admin-display' : 'tenbucks-registration-form';
		require_once plugin_dir_path( dirname( __FILE__ ) ). 'admin/partials/'.$template_name.'.php';
	}

	/**
	 * Add a notification to admin page
	 *
	 * @param string $message message to show
	 * @param string $type type of notice (alert, success, ...)
	 */
	private function add_notice($message, $type)
	{
		$this->notice[] = array(
			'message' => $message,
			'type' => $type
		);
	}

	/**
	 * Get the path to an asset
	 *
	 * @param string $filename file to add
	 * @return string url of the file
	 * @throws Exception
	 */
	public function get_asset_path($filename)
	{
		$extension = substr($filename, -3);

		switch ($extension)
		{
			case '.js':
				$dirname = 'js';
				break;

			case 'css':
				$dirname = 'css';
				break;

			case 'jpg':
			case 'png':
			case 'gif':
			case 'svg':
				$dirname = 'img';
				break;

			default :
				throw new Exception('Unknow file extension');
		}

		return plugin_dir_url(__FILE__).$dirname.'/'.$filename;
	}

	public function create_key()
	{
		include_once plugin_dir_path( dirname( __FILE__ ) ).'/vendor/tenbucks_registration_client/lib/TenbucksRegistrationClient.php';
		$form_is_valid = true;
		$required_fields = array('email', 'email_confirmation');
		foreach ($required_fields as $key) {
			if (!array_key_exists($key, $_POST) || empty($_POST[$key])) {
				$format = __( 'Field %s is missing.', 'tenbucks' );
				return wp_send_json_error( array(
					'message' => sprintf($format, $key),
					'field' => $key
					) );
			}
		}
		$post_data = array_map('strtolower', $_POST);
		$email = $post_data['email'];
		$email_confirmation = $post_data['email_confirmation'];
		$sponsor = empty($post_data['sponsor']) ? null : $post_data['sponsor'];

		$error_msg = false;

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error_msg = __( 'Invalid email.', 'tenbucks' );
		}

		if ($email !== $email_confirmation) {
			$error_msg = __( 'Email and confirmation are different.', 'tenbucks' );
		}

		if ($error_msg) {
			return wp_send_json_error( array(
				'message' => $error_msg,
				'field' => 'email'
				) );
		}

		try {
			global $wpdb;
			// If API disabled, active it
			if (get_option('woocommerce_api_enabled') !== 'yes') {
				update_option('woocommerce_api_enabled', 'yes');
			}

			$key_id = (int)get_option('tenbucks_ak_id');
			$consumer_key    = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();
			$table = $wpdb->prefix . 'woocommerce_api_keys';
			$data = array(
				'user_id'         => get_current_user_id(),
				'consumer_key'    => wc_api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 )
			);

			if (!$key_id) {
				$data['description'] = 'tenbucks';
				$data['permissions'] = 'read_write';

				$wpdb->insert($table, $data, array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				) );
				update_option('tenbucks_ak_id', $wpdb->insert_id);

			} else {
				$updated_rows = $wpdb->update(
					$table,
					$data,
					array( 'key_id' => $key_id ),
					array(
						'%d',
						'%s',
						'%s',
						'%s'
					),
					array( '%d' )
				);

				if (!$updated_rows) {
					update_option('tenbucks_ak_id', 0);
					return wp_send_json_error(array(
						'message' => __( 'Keys update failed, please try again.', 'tenbucks' )
					));
				}
			}

			unset($data);

			$client = new TenbucksRegistrationClient();
			$url = get_site_url();
			$lang_infos = explode('_',  get_locale());
			$opts = array(
	            'email' => $email,
	            'sponsor' => $sponsor, // optionnal
				'company' => get_bloginfo('name'),
	            'platform' => 'WooCommerce',
				'locale' => $lang_infos[0],
				'country' => $lang_infos[1],
				'url'         => get_site_url(),
				'credentials' => array(
					'api_key'    => $consumer_key, // key
					'api_secret' => $consumer_secret, // secret
				)
			);
			$query = $client->send($opts);
			$success = array_key_exists('success', $query) && (bool)$query['success'];
			if ($success) {
				// success
				update_option('tenbucks_registration_complete', true);
				if ($query['new_account']) {
					$msg = __( 'New account created. Please check your emails to confirm your address and start using tenbucks.', 'tenbucks' );
					$need_reload = false;
				} else {
					$msg = __( 'Shop added to your existing account. Page will reload shortly.', 'tenbucks' );
					$need_reload = true;
				}
				return wp_send_json_success( array(
					'message' => $msg,
					'needReload' => $need_reload
				) );
			} else {
				return wp_send_json_error(array(
					'message' => __( 'Creation failed, please try again.', 'tenbucks' )
				));
			}

		} catch ( Exception $e ) {
			return wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
