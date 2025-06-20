<?php
/*
Plugin Name: Lead Forensics
Plugin URI: https://wordpress.org/plugins/lead-forensics-roi/
Description: Lead Forensics allows you to Turn your anonymous website visitors into sales leads, convert new business opportunities before your competitiors and increase your online ROI. This plugin allows you to easily add your tracking code from Lead Forensics to the head of your WordPress site
Version: 3.3.11
Author: Lead Forensics
Author URI: http://www.leadforensics.com/
Author Email: wordpress-plugin-support@leadforensics.com
Network: false
Copyright 2008-2025 Lead Forensics (wordpress-plugin-support@leadforensics.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class LFRTrackingCode
{
	private $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'lfr_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'lfr_page_init' ) );
		add_action( 'admin_init', array( $this, 'lfr_plugin_settings_page_permission' ) );
		add_action( 'admin_head', array( $this, 'lfr_admin_js' ) );
		add_action( 'wp_head', array ( $this, 'lfr_custom_js') );
		// update our data structure to migrate old data to new
		add_action( 'plugins_loaded', array( $this, 'lfr_rename_variables' ) );
	}

	//check user has permission to access this plugin
	public function lfr_plugin_settings_page_permission() {
		if(!current_user_can('manage_options')) {
			return;
		}

		//Setting links while plugin is active
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'lfr_add_action_links' ) );
	}

	public function lfr_add_action_links($links) {
		$lfr_admin_links = array(
			'<a href="' . admin_url( 'options-general.php?page=lfr_settings' ) . '">Settings</a>',
		);

		return array_merge( $links, $lfr_admin_links );
	}

	public function lfr_add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Lead Forensics',
			'manage_options',
			'lfr_settings',
			array( $this, 'lfr_create_admin_page' )
		);
	}

	public function lfr_create_admin_page() {
		$this->options = get_option( 'lfr_options' );
		?>
        <div class="wrap">
            <h2>Lead Forensics Tracking</h2>
            <form method="post" action="options.php">
				<?php

				settings_fields( 'lfr_option_group' );
				do_settings_sections( 'lfr-setting-admin' );
				submit_button();
				$this->lfr_print_section_info_video();
				?>
            </form>
        </div>
		<?php
	}

	public function lfr_page_init() {
		register_setting(
			'lfr_option_group',
			'lfr_options',
			array( $this, 'lfr_sanitize' )
		);

		add_settings_section(
			'lfr_setting_section',
			'Lead Forensics',
			array( $this, 'lfr_print_section_info' ),
			'lfr-setting-admin'
		);
		add_settings_field(
			'lfr_tracking_code',
			'',
			array( $this, 'lfr_script_textarea' ),
			'lfr-setting-admin',
			'lfr_setting_section'
		);
	}

	public function lfr_sanitize( $input ) {
		$new_input = array();

		if( isset( $input['lfr_tracking_code'] ) ) {
			$new_input['lfr_tracking_code'] =  trim($input['lfr_tracking_code']);
		}

		return $new_input;
	}

	public function lfr_print_section_info() {
		print '<a href="http://www.leadforensics.com" target="_blank">Lead Forensics </a> is a B2B tool used to identify the unidentified visitors that visit your website.<br/>
               This Plugin will assist you in placing the <a href="https://portal.leadforensics.com/TrackingCode" target="blank">Tracking Code </a> into your WordPress site or blog.<br/><br/>
               <strong>Enter your Lead Forensics code below</strong><br/>';
	}

	public function lfr_script_textarea() {
		$lfr_tracking_code = isset( $this->options['lfr_tracking_code'] ) ? esc_attr( $this->options['lfr_tracking_code']) : '';
		$safe_text = apply_filters( 'esc_textarea', $lfr_tracking_code);

		?>
        <textarea cols="75" rows="15" name="<?php echo 'lfr_options[lfr_tracking_code]'; ?>" type="textarea"><?php echo trim($safe_text); ?></textarea>
		<?php
	}

	//hook to display the script on front side
	public function lfr_custom_js() {
		$get_all_value_array = get_option( 'lfr_options', true );
		$lfr_tracking_code = $get_all_value_array['lfr_tracking_code'] ?? '';

		if($lfr_tracking_code !='')
		{
			$safe_text = apply_filters( 'esc_textarea', $lfr_tracking_code );

			if ( !empty( $safe_text ) ) {
				echo trim((htmlspecialchars_decode($safe_text)));
			}
		}
	}

	public function lfr_print_section_info_video() {
		echo '<div class="textare_descrption">
                <h1>About Lead Forensics</h1>
                <div class="video_cover">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/cWOONn32qtM" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>';
	}

	public function lfr_admin_js() {
		global $current_screen;

		$settings_page_lfr_settings=  $current_screen->base;

		if($settings_page_lfr_settings == 'settings_page_lfr_settings') {
			wp_register_script('lfr-scripts', plugins_url('/js/custom.js', __FILE__ ) );
			wp_enqueue_script('lfr-scripts');
			wp_localize_script('lfr-scripts', 'wp_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		}
	}

	public function lfr_rename_variables() {
		// make sure were not firing it for normal users
		if(!current_user_can('manage_options')) {
			return;
		}

		$new_values = get_site_option( 'lfr_options', true );
		$new_script = $new_values['lfr_tracking_code'] ?? '';

		if($new_script == '') {
			$old_values = get_site_option( 'my_option_name', true );
			$old_script = $old_values['script_textarea'] ?? '';

			if($old_script != '') {
				$safe_old_text = apply_filters( 'esc_textarea', $old_script );
				$new_value = array('lfr_tracking_code' => $safe_old_text);

				update_option('lfr_options', $new_value, true);
			}
		}
	}
}

// instantiate our class to get everything running
$lfr_tracking_code = new LFRTrackingCode();