<?php
/**
 * picasa_album_uploader_options class to manage options
 *
 * @package Picasa Album Uploader
 * @author Kenneth J. Brucker <ken.brucker@action-a-day.com>
 * @copyright 2016 Kenneth J. Brucker (email: ken.brucker@action-a-day.com)
 * 
 * This file is part of Picasa Album Uploader, a plugin for Wordpress.
 *
 * Picasa Album Uploader is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Picasa Album Uploader is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Picasa Album Uploader.  If not, see <http://www.gnu.org/licenses/>.
 **/

// Protect from direct execution
if (!defined('WP_PLUGIN_DIR')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die( 'I don\'t think you should be here.' );
}

class picasa_album_uploader_options
{
	/**
	 * slug used to detect pages requiring plugin action
	 *
	 * @var string slug name
	 * @access public
	 **/
	public $slug;
	
	/**
	 * When errors are detected in the module, this variable will contain a text description
	 *
	 * @var string Error Message
	 * @access public
	 **/
	public $error;
	
	/**
	 * Long variable name used in self_test
	 */
	public $long_var_name =   "this_is_a_long_variable_name_to_mimic_picasa_upload_operation_3456789012345678901234567890123456789";

	/**
	 * Init the class
	 *
	 * Setup option defaults
	 * Hook into WP
	 *
	 * @return void
	 */
	function init()
	{
		// Retrieve Plugin Options
		$options = get_option('pau_plugin_settings');

		// TODO Improve handling of default settings
		// Init value for slug name - supply default if undefined
		$this->slug = isset($options['slug']) ? $options['slug'] : 'picasa_album_uploader';
		
		// Init value for error log
		$this->debug_log_enabled = isset($options['debug_log_enabled']) ? $options['debug_log_enabled'] : 0;
		$this->log_to_errlog = isset($options['log_to_errlog']) ? $options['log_to_errlog'] : 0;
		$this->debug_log = isset($options['debug_log']) ? $options['debug_log'] : array();
		
		// When displaying admin screens ...
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'pau_settings_admin_init' ) );
			
			// Add section for reporting configuration errors
			add_action('admin_footer', array( &$this, 'pau_admin_notice'));			
		}

		// If logging is enabled, setup save in the footers.
		if ($this->debug_log_enabled) {
			add_action('admin_footer', array( &$this, 'save_debug_log'));
			add_action('wp_footer', array( &$this, 'save_debug_log'));				
		}		
	}
	
	/**
	 * Cleanup database if uninstall is requested
	 *
	 * @access public
	 * @return void
	 **/
	function uninstall() {
		delete_option('pau_plugin_settings'); // Remove the plugin settings		
	}
			
	/**
	 * WP action to register the plugin settings options when running admin_screen
	 *
	 * @access public
	 * @return void
	 **/
	function pau_settings_admin_init ()
	{
		// Add settings section to the 'media' Settings page
		add_settings_section( 
			'pau_settings_section', 
			'Picasa Album Uploader Settings', 
			array( $this, 'settings_section_html'), 
			'media' 
		);
		
		// Add slug name field to the plugin admin settings section
		add_settings_field( 
			'pau_plugin_settings[slug]', 
			'Slug', 
			array( $this, 'slug_html' ), 
			'media', 
			'pau_settings_section' 
		);
		
		// Add Plugin Error Logging
		add_settings_field( 
			'pau_plugin_settings[debug_log_enabled]', 
			'Enable Debug', 
			array( $this, 'debug_log_enabled_html'), 
			'media', 
			'pau_settings_section' 
		);

		// Send log messages to errlog or plugin log? 
		add_settings_field( 
			'pau_plugin_settings[log_to_errlog]', 
			'Send log messages to errlog', 
			array( $this, 'log_to_errlog_html'), 
			'media', 
			'pau_settings_section' 
		);

		// Section for displaying debug log messages
		add_settings_field(
			'pau_plugin_settings[debug_log]',
			'System Config',
			array( $this, 'debug_log_html'),
			'media',
			'pau_settings_section' 
		);
		
		// Register the slug name setting;
		register_setting( 'media', 'pau_plugin_settings', array (&$this, 'sanitize_settings') );
	}
	
	/**
	 * WP action to emit Admin notice messages with class "error" for display on WP Admin pages
	 *
	 * @access public
	 * @return void
	 **/
	function pau_admin_notice()
	{
		if ( get_option('permalink_structure') == '' ) {
			echo '<div class="error"><p>';
			printf(__('%1$s requires the use of %2$s', 'picasa-album-uploader'), '<a href="options-media.php">' . PAU_PLUGIN_NAME . '</a>', '<a href="options-permalink.php">Permalinks</a>');
			echo '</p></div>';
		}
		
		if ( $this->debug_log_enabled ) {
			echo '<div class="error"><p>';
			printf(__('%s logging is enabled.  If left enabled, this can affect database performance.', 'picasa-album-uploader'),'<a href="options-media.php">' . PAU_PLUGIN_NAME . '</a>');
			echo '</p></div>';
		}		
	}
	
	/**
	 * WP callback function to sanitize the Plugin Options received from the user
	 *
	 * @access public
	 * @param hash $options Options defined by plugin indexed by option name
	 * @return hash Sanitized hash of plugin options
	 **/
	function sanitize_settings($options)
	{
		// Slug must be alpha-numeric, dash and underscore.
		$slug_pattern[0] = '/\s+/'; 						// Translate white space to a -
		$slug_replacement[0] = '-';
		$slug_pattern[1] = '/[^a-zA-Z0-9-_]/'; 	// Only allow alphanumeric, dash (-) and underscore (_)
		$slug_replacement[1] = '';
		$options['slug'] = preg_replace($slug_pattern, $slug_replacement, $options['slug']);
		
		// Cleanup error log if it's disabled
		if ( ! isset($options['debug_log_enabled']) || ! $options['debug_log_enabled'] ) {
			$options['debug_log'] = array();
		}

		return $options;
	}
	
	/**
	 * WP options screen callback to emit HTML to create a settings section for the plugin in admin screen.
	 *
	 * @access public
	 * @return void
	 **/
	function settings_section_html()
	{	
		// Permalinks must be enabled ...
		if ( get_option('permalink_structure') != '' ) {
			echo '<p>';
			_e('To use Picasa Album Uploader, install the Button in Picasa Desktop using this automated install link:', 'picasa-album-uploader');
			echo '</p>';
			// Display button to download the Picasa Button Plugin
			echo do_shortcode( "[picasa_album_uploader_button]" );
		} else {
			echo '<p>';
			_e('To use Picasa Album Uploader, Permalinks must be enabled due to limitations in the Desktop Picasa application.');
			echo '</p>';
		}
	}
	
	/**
	 * WP options screen callback to emit HTML to create form field for slug name
	 *
	 * @access public
	 * @return void
	 **/
	function slug_html()
	{ 
		echo '<input id="pau_slug" type="text" name="pau_plugin_settings[slug]" value="' . $this->slug . '" />';
		echo '<p>';
		_e('Set the slug used by the plugin.  Only alphanumeric, dash (-) and underscore (_) characters are allowed.  White space will be converted to dash, illegal characters will be removed.', 'picasa-album-uploader');
		echo '<br />';
		_e('When the slug name is changed, a new button must be installed in Picasa to match the new setting.', 'picasa-album-uploader');
		echo '</p>';
	}
	
	/**
	 * Build a URL to pages generated by this plugin based on use of permalinks
	 *
	 * @access public
	 * @param string $page plugin handled page name
	 * @return string URL to a plugin generated page
	 **/
	public function build_url( $page )
	{
		global $wp_rewrite;

		/**
		 * Get the page permastruct to use.
		 * PATHINFO Permalinks are "Almost Pretty" and use index.php as base of the permalink URL.
		 */
		$url = $wp_rewrite->get_page_permastruct();
		if (empty($url)) {
			// Whoa, big problem since we kinda need page permalinks to be available
			$this->pau_options->debug_log("Tried to build URL; page permalinks not available!");
			return '';
		}
		$this->debug_log( "get_page_permastruct = $url" );
		
		$url = home_url(str_replace('%pagename%', $this->slug . '/' . $page, $url));
		return $url;
	}
	
	
	/**
	 * Perform self test operations and emit reporting HTML
	 *   Attempt to load page using long GET request
	 *
	 * @access private
	 * @return string HTML report of self test results
	 **/
	private function selftest()
	{
		$text = '';
		
		// Run long REQUEST Variable name test
		// TODO Turn into a javascript based test so media page doesn't pause
		if ($result = $this->test_long_var()) {
			$text .= '<span class="notice notice-error">Long Request Variable Test Failed: ' . $result . '</span>';
		} else {
			$text .= 'REQUEST long variable OK<br>';
		}
		
		return $text;
	}
	
	/**
	 * Perform HTTP request to self test page including a long request variable name
	 * This test confirms that the WP install is capable of receiving the long argument names that are sent by Picasa.
	 *
	 * @access public
	 * @return false if able to retrieve long variable name, string describing error otherwise
	 **/
	function test_long_var()
	{
		$baseurl = $this->build_url('selftest');
		$url = $baseurl . '?' . $this->long_var_name . '=' . $this->long_var_name;
		$get_opts = array( 'timeout' => 5 );
		
		/**
		 * Ignore SSL verification in DEBUG mode. Allows test servers to use self-signed certificates.
		 */
		if ( WP_DEBUG ) $get_opts['sslverify'] = false;
		
		/**
		 * Try remote access
		 *
		 * Due to differences in how servers compress data, a warning from gzinflate() may be seen as a result
		 * of the wp_remote_get() call.  See https://core.trac.wordpress.org/ticket/22952
		 *     WARNING: wp-includes/class-wp-http-encoding.php:58 - gzinflate(): data error
		 */
		$res = wp_remote_get( $url, $get_opts );
		
		if ( is_wp_error( $res ) ) {
			/**
			 * Got a WP error back
			 */
			
			$result = join( ', ', $res->get_error_messages() );
			
		} elseif (wp_remote_retrieve_response_code($res) == 200) {
			/**
			 *  Retrieved content successfully
			 */
			if (wp_remote_retrieve_body($res) == 'REQUEST long variable OK, received length='.strlen($this->long_var_name)) {
				// Got expected results, all OK
				$result = false;				
			} else {
				// Send back first 120 characters of the retrieved body to help in debug
				$result = 'Unexpected results returned (displaying first 120 characters): ' .
						 	esc_html(substr(wp_remote_retrieve_body($res), 0, 120));
			}
		} else {
			/**
			 * Retrieved content successfully
			 */
			$result = wp_remote_retrieve_response_code($res) . ' ' . wp_remote_retrieve_response_message($res);
		}
		
		return $result;
	}
	
	/**
	 * WP options callback to emit HTML to create form field used to enable/disable Debug Logging
	 *
	 * @access public
	 * @return void
	 **/
	function debug_log_enabled_html()
	{ 
		$checked = $this->debug_log_enabled ? "checked" : "" ;
		echo '<input type="checkbox" name="pau_plugin_settings[debug_log_enabled]" value="1" ' . $checked . '>';
		_e('Enable Plugin Debug Logging.', 'picasa-album-uploader');
	}
	
	/**
	 * WP options callback to emit HTML to create form field used to enable/disable sending debug messages to errlog
	 *
	 * @access public
	 * @return void
	 **/
	function log_to_errlog_html()
	{ 
		$checked = $this->log_to_errlog ? "checked" : "" ;
		echo '<input type="checkbox" name="pau_plugin_settings[log_to_errlog]" value="1" ' . $checked . '>';
		_e('Use PHP error_log() vs. displaying below', 'picasa-album-uploader');
	}
	
	/**
	 * Generate data for debug and bug reporting
	 *
	 * @access public
	 * @return string HTML to display debug messages
	 */
	function debug_log_html()
	{
		global $wpdb;
		global $wp_version;
		global $wp_rewrite;

		$plugin_data = get_plugin_data(PAU_PLUGIN_DIR . '/' . PAU_PLUGIN_NAME . '.php');
		$content = '<dl class=pau-debug-log>';
		$content .= '<dt>Wordpress Version:<dd>' . esc_html($wp_version);
		$content .= '<dt>Plugin Version:<dd>' . esc_html($plugin_data['Version']);
		$content .= '<dt>Active Plugins:<dd><ul>';
		foreach (get_option('active_plugins') as $plugin) {
			$content .= '<li>' . esc_html($plugin) . '</li>';
		};
		$content .= '</ul>';

		// Add some environment data
		$content .= '<dt>PHP Version:<dd>' . phpversion();
		$content .= '<dt>MySQL Server Version:<dd>' . esc_html($wpdb->db_version());
		
		$content .= '<dt>Plugin Slug: <dd>' . $this->slug;
		$content .= '<dt>Page Permalink Structure: <dd>' . esc_html($wp_rewrite->get_page_permastruct());

		// Filter the hostname of running system from debug log
		$content .= '<dt>Sample Plugin URL: <dd>' . esc_url( $this->redact_url( $this->build_url( 'sample' ) ) );

		if ($this->debug_log_enabled) {
			// If debug enabled then include a Self Test
			$content .= '<dt>Self Test: <dd>' . self::selftest();
		
			// Add debug log content if not logging to errlog
			if (! $this->log_to_errlog) {
				$content .= '<dt>Log:';
				foreach ($this->debug_log as $line) {
					$content .= '<dd>' . esc_html($line);
				}			
			}
			
		}
		$content .= '</dl>';
		
		echo $content;
		return;
	}
	
	/**
	 * Log a debug message
	 *
	 * @access public
	 * @return void
	 **/
	function debug_log($msg)
	{
		if ( $this->debug_log_enabled ) {
			if ($this->log_to_errlog) {
				error_log(PAU_PLUGIN_NAME . ": " . $msg);
			} else {
				array_push($this->debug_log, date("Y-m-d H:i:s") . " " . $msg);							
			}
		}
	}
	
	/**
	 * Save the error log if it's enabled.  Must be called before server code exits to preserve
	 * any log messages recorded during session.
	 *
	 * @access public
	 * @return void
	 **/
	function save_debug_log()
	{
		// Only need to save the log if messages are not being sent to errlog
		if ($this->debug_log_enabled && ! $this->log_to_errlog ) {
			$options = get_option('pau_plugin_settings');
			$options['debug_log'] = $this->debug_log;
			update_option('pau_plugin_settings', $options);
		}
	}
	
	/**
	 * Log errors to server log and debug log
	 *
	 * @access public
	 * @return void
	 **/
	function error_log($msg)
	{
		// Avoid double logging
		if (! $this->log_to_errlog)
			error_log(PAU_PLUGIN_NAME . ": " . $msg);
		$this->debug_log($msg);
	}
	
	/**
	 * Log a redacted URL to error log
	 *
	 * @access public
	 * @return void
	 */
	function log_url( $logmsg, $url )
	{
		$this->error_log( $logmsg . $this->redact_url( $url ) );
	}
	
	/**
	 * Redact the host name portion of a URL for logging
	 *
	 * @access private
	 * @param string	$url, URL to redact
	 * @return string	Redacted URL
	 */
	function redact_url( $url )
	{
		return preg_replace('/:\/\/.+?\//','://*masked-host*/', $url );
	}
} // END class 
?>
