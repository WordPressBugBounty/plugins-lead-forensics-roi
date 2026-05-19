<?php
/**
 * Fired when the plugin is uninstalled.
 * Removes all options stored by Lead Forensics.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'lfv2_script_tag' );
delete_option( 'lfv2_noscript_tag' );
