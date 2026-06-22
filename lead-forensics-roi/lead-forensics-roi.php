<?php
/**
 * Plugin Name: Lead Forensics
 * Plugin URI:  https://wordpress.org/plugins/lead-forensics-roi/
 * Description: Lead Forensics allows you to Turn your anonymous website visitors into sales leads, convert new business opportunities before your competitors and increase your online ROI. This plugin allows you to easily add your tracking code from Lead Forensics to the head of your WordPress site
 * Version:     3.6.1
 * Author:      Lead Forensics
 * Author URI:  https://www.leadforensics.com/
 * Author Email: wordpress-plugin-support@leadforensics.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lead-forensics
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LF360_VERSION', '3.6.1' );
define( 'LF360_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'LF360_OPTION', 'lfr_options' );
define( 'LF360_OPTION_KEY', 'lfr_tracking_code' );

// ── Activation ─────────────────────────────────────────

register_activation_hook( __FILE__, 'lf360_activate' );

function lf360_activate() {
    lf360_migrate();
}

// ── Upgrade migration ──────────────────────────────────
// Handles all upgrade and downgrade paths:
//
// 3.3.11 / 3.4.0 / 3.5.0 / 3.5.1
//   lfr_options intact — nothing to migrate.
//
// 3.5.2 / 3.5.3 / 3.5.4
//   lfr_options was deleted. Code is split across lfv2_script_tag
//   and lfv2_noscript_tag. Recombine and restore lfr_options.

add_action( 'admin_init', 'lf360_migrate' );

function lf360_migrate() {

    // If lfr_options already has content nothing to migrate.
    $existing      = get_option( LF360_OPTION, array() );
    $existing_code = isset( $existing[ LF360_OPTION_KEY ] ) ? $existing[ LF360_OPTION_KEY ] : '';

    if ( ! empty( $existing_code ) ) {
        lf360_cleanup_v2();
        return;
    }

    // Check for code in split fields written by 3.5.2 through 3.5.4.
    $script_tag   = get_option( 'lfv2_script_tag', '' );
    $noscript_tag = get_option( 'lfv2_noscript_tag', '' );

    if ( ! empty( $script_tag ) ) {
        $combined = trim( $script_tag );
        if ( ! empty( $noscript_tag ) ) {
            $combined .= "\n" . trim( $noscript_tag );
        }
        update_option( LF360_OPTION, array( LF360_OPTION_KEY => $combined ) );
        update_option( 'lf360_migrated', '1' );
    }

    lf360_cleanup_v2();
}

function lf360_cleanup_v2() {
    delete_option( 'lfv2_script_tag' );
    delete_option( 'lfv2_noscript_tag' );
    delete_option( 'lfv2_db_version' );
    delete_option( 'lfv2_migrated' );
    delete_option( 'lfv2_perf_exclude' );
}

// ── Very old version migration (pre 3.3.11) ────────────

add_action( 'plugins_loaded', 'lf360_rename_variables' );

function lf360_rename_variables() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $new_values = get_site_option( LF360_OPTION, true );
    $new_script = isset( $new_values[ LF360_OPTION_KEY ] ) ? $new_values[ LF360_OPTION_KEY ] : '';

    if ( $new_script === '' ) {
        $old_values = get_site_option( 'my_option_name', true );
        $old_script = isset( $old_values['script_textarea'] ) ? $old_values['script_textarea'] : '';

        if ( $old_script !== '' ) {
            $new_value = array( LF360_OPTION_KEY => $old_script );
            update_option( LF360_OPTION, $new_value, true );
        }
    }
}

// ── Bootstrap ──────────────────────────────────────────

add_action( 'plugins_loaded', 'lf360_init' );

function lf360_init() {
    add_filter( 'plugin_action_links_' . LF360_PLUGIN_BASENAME, 'lf360_add_settings_link' );
}

// ── Admin menu ─────────────────────────────────────────

add_action( 'admin_menu', 'lf360_add_plugin_page' );

function lf360_add_plugin_page() {
    add_options_page(
        'Settings Admin',
        'Lead Forensics',
        'manage_options',
        'lfr_settings',
        'lf360_create_admin_page'
    );
}

// ── Settings link in plugin list ───────────────────────

function lf360_add_settings_link( $links ) {
    $lfr_admin_links = array(
        '<a href="' . admin_url( 'options-general.php?page=lfr_settings' ) . '">Settings</a>',
    );
    return array_merge( $links, $lfr_admin_links );
}

// ── Register settings ──────────────────────────────────

add_action( 'admin_init', 'lf360_page_init' );

function lf360_page_init() {
    register_setting(
        'lfr_option_group',
        LF360_OPTION,
        'lf360_sanitize'
    );

    add_settings_section(
        'lfr_setting_section',
        'Lead Forensics',
        'lf360_print_section_info',
        'lfr-setting-admin'
    );

    add_settings_field(
        LF360_OPTION_KEY,
        '',
        'lf360_script_textarea',
        'lfr-setting-admin',
        'lfr_setting_section'
    );
}

function lf360_sanitize( $input ) {
    $new_input = array();
    if ( isset( $input[ LF360_OPTION_KEY ] ) ) {
        $new_input[ LF360_OPTION_KEY ] = trim( $input[ LF360_OPTION_KEY ] );
    }
    return $new_input;
}

// ── Settings page HTML ─────────────────────────────────
// Copied from 3.3.11 exactly.

function lf360_create_admin_page() {
    $options = get_option( LF360_OPTION );

    if ( get_option( 'lf360_migrated' ) === '1' ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Your tracking code has been automatically carried over from the previous plugin version and is live.</strong> Please check the field below to confirm everything looks correct.</p></div>';
        delete_option( 'lf360_migrated' );
    }
    ?>
    <div class="wrap">
        <h2>Lead Forensics Tracking</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'lfr_option_group' );
            do_settings_sections( 'lfr-setting-admin' );
            submit_button();
            lf360_print_section_info_video();
            ?>
        </form>
    </div>
    <?php
}

function lf360_print_section_info() {
    print '<a href="http://www.leadforensics.com" target="_blank">Lead Forensics </a> is a B2B tool used to identify the unidentified visitors that visit your website.<br/>
               This Plugin will assist you in placing the <a href="https://portal.leadforensics.com/TrackingCode" target="blank">Tracking Code </a> into your WordPress site or blog.<br/><br/>
               <strong>Enter your Lead Forensics code below</strong><br/>';
}

function lf360_script_textarea() {
    $options           = get_option( LF360_OPTION );
    $lfr_tracking_code = isset( $options[ LF360_OPTION_KEY ] ) ? esc_attr( $options[ LF360_OPTION_KEY ] ) : '';
    $safe_text         = apply_filters( 'esc_textarea', $lfr_tracking_code );
    ?>
    <textarea cols="75" rows="15" name="<?php echo esc_attr( LF360_OPTION ) . '[' . esc_attr( LF360_OPTION_KEY ) . ']'; ?>" type="textarea"><?php echo trim( $safe_text ); ?></textarea>
    <?php
}

function lf360_print_section_info_video() {
    echo '<div class="textare_descrption">
                <h1>About Lead Forensics</h1>
                <div class="video_cover">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/cWOONn32qtM" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>';
}

// ── Check user has permission ──────────────────────────

add_action( 'admin_init', 'lf360_plugin_settings_page_permission' );

function lf360_plugin_settings_page_permission() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
}

// ── Admin JS (fixes textarea layout — copied from 3.3.11) ──

add_action( 'admin_head', 'lf360_admin_js' );

function lf360_admin_js() {
    global $current_screen;
    $settings_page = $current_screen->base;
    if ( $settings_page == 'settings_page_lfr_settings' ) {
        wp_register_script( 'lfr-scripts', plugin_dir_url( __FILE__ ) . 'js/custom.js' );
        wp_enqueue_script( 'lfr-scripts' );
        wp_localize_script( 'lfr-scripts', 'wp_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }
}

// ── Frontend: inject tracking code into <head> ─────────
// Matches 3.3.11 exactly — raw echo at default priority 10.

add_action( 'wp_head', 'lf360_custom_js' );

function lf360_custom_js() {
    $get_all_value_array = get_option( LF360_OPTION, true );
    $lfr_tracking_code   = isset( $get_all_value_array[ LF360_OPTION_KEY ] ) ? $get_all_value_array[ LF360_OPTION_KEY ] : '';

    if ( $lfr_tracking_code !== '' ) {
        $safe_text = apply_filters( 'esc_textarea', $lfr_tracking_code );

        if ( ! empty( $safe_text ) ) {
            echo trim( htmlspecialchars_decode( $safe_text ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}
