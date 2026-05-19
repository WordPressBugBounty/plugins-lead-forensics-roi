<?php
/**
 * Plugin Name: Lead Forensics
 * Plugin URI:  https://www.leadforensics.com
 * Description: Adds the Lead Forensics tracking script to your WordPress site. Correctly places the script tag in the head and noscript tag after the body open tag, without async or defer.
 * Version:     3.4.0
 * Author:      Lead Forensics
 * Author URI:  https://www.leadforensics.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lead-forensics
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LFV2_VERSION', '3.4.0' );
define( 'LFV2_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LFV2_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'LFV2_SCRIPT_TAG', 'lfv2_script_tag' );
define( 'LFV2_NOSCRIPT_TAG', 'lfv2_noscript_tag' );

// ── Bootstrap ──────────────────────────────────────────

add_action( 'plugins_loaded', 'lfv2_init' );

function lfv2_init() {
    add_filter( 'plugin_action_links_' . LFV2_PLUGIN_BASENAME, 'lfv2_add_settings_link' );
}

// ── Admin menu ─────────────────────────────────────────

add_action( 'admin_menu', 'lfv2_add_menu' );

function lfv2_add_menu() {
    add_options_page(
        __( 'Lead Forensics', 'lead-forensics' ),
        __( 'Lead Forensics', 'lead-forensics' ),
        'manage_options',
        'lead-forensics-roi',
        'lfv2_settings_page'
    );
}

// ── Settings link in plugin list ───────────────────────

function lfv2_add_settings_link( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=lead-forensics-roi' ) ) . '">' . __( 'Settings', 'lead-forensics' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

// ── Register settings ──────────────────────────────────

add_action( 'admin_init', 'lfv2_register_settings' );

function lfv2_register_settings() {
    register_setting(
        'lfv2_group',
        LFV2_SCRIPT_TAG,
        array(
            'sanitize_callback' => 'lfv2_sanitize_script',
            'default'           => '',
        )
    );
    register_setting(
        'lfv2_group',
        LFV2_NOSCRIPT_TAG,
        array(
            'sanitize_callback' => 'lfv2_sanitize_noscript',
            'default'           => '',
        )
    );
}

// ── Sanitise script tag (strips async/defer) ───────────

function lfv2_sanitize_script( $input ) {
    $input = trim( $input );
    $input = preg_replace( '/\s+async/i', '', $input );
    $input = preg_replace( '/\s+defer/i', '', $input );
    return $input;
}

// ── Sanitise noscript tag ──────────────────────────────

function lfv2_sanitize_noscript( $input ) {
    return trim( $input );
}

// ── Enqueue admin CSS ──────────────────────────────────

add_action( 'admin_enqueue_scripts', 'lfv2_enqueue_styles' );

function lfv2_enqueue_styles( $hook ) {
    if ( 'settings_page_lead-forensics-roi' !== $hook ) {
        return;
    }
    wp_enqueue_style(
        'lfv2-admin',
        LFV2_PLUGIN_URL . 'assets/admin.css',
        array(),
        LFV2_VERSION
    );
}

// ── Settings page HTML ─────────────────────────────────

function lfv2_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $script_tag    = get_option( LFV2_SCRIPT_TAG, '' );
    $noscript_tag  = get_option( LFV2_NOSCRIPT_TAG, '' );
    $saved         = isset( $_GET['settings-updated'] ) && '1' === $_GET['settings-updated'];
    $is_configured = ! empty( $script_tag );
    $status_class  = $is_configured ? 'lf-status--active' : 'lf-status--inactive';
    $status_label  = $is_configured ? __( 'Tracking Active', 'lead-forensics' ) : __( 'Not Configured', 'lead-forensics' );
    ?>
    <div class="lf-wrap">

        <div class="lf-header">
            <div>
                <div class="lf-header__title">Lead Forensics</div>
                <div class="lf-header__sub">Tracking Code Manager</div>
            </div>
            <div class="lf-status <?php echo esc_attr( $status_class ); ?>">
                <span class="lf-status__dot"></span>
                <?php echo esc_html( $status_label ); ?>
            </div>
        </div>

        <div class="lf-body">
            <div class="lf-main">

                <?php if ( $saved ) : ?>
                <div class="lf-notice">
                    <span>&#10003;</span> <?php esc_html_e( 'Settings saved. Your tracking code is live.', 'lead-forensics' ); ?>
                </div>
                <?php endif; ?>

                <form method="post" action="options.php">
                    <?php settings_fields( 'lfv2_group' ); ?>

                    <div class="lf-card">
                        <div class="lf-card__header">
                            <h2><?php esc_html_e( 'Tracking Code Setup', 'lead-forensics' ); ?></h2>
                            <p>
                                <?php esc_html_e( 'Add your Lead Forensics tracking snippets below.', 'lead-forensics' ); ?>
                                <a href="https://portal.leadforensics.com/TrackingCode" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Get your tracking code', 'lead-forensics' ); ?> &rarr;
                                </a>
                            </p>
                        </div>
                        <div class="lf-card__body">

                            <div class="lf-field">
                                <label for="lfv2_script_tag">
                                    <?php esc_html_e( 'Script Tag', 'lead-forensics' ); ?>
                                    <span class="lf-badge lf-badge--head">&lt;head&gt;</span>
                                </label>
                                <p class="lf-hint">
                                    <?php esc_html_e( 'Paste the full script tag from your portal. async and defer attributes are stripped automatically to ensure accurate tracking.', 'lead-forensics' ); ?>
                                </p>
                                <textarea
                                    id="lfv2_script_tag"
                                    name="<?php echo esc_attr( LFV2_SCRIPT_TAG ); ?>"
                                    rows="5"
                                    spellcheck="false"
                                    placeholder="&lt;script&gt;...&lt;/script&gt;"
                                ><?php echo esc_textarea( $script_tag ); ?></textarea>
                                <?php if ( ! empty( $script_tag ) ) : ?>
                                <div class="lf-preview">
                                    <span><?php esc_html_e( 'Will inject as:', 'lead-forensics' ); ?></span>
                                    <code><?php echo esc_html( lfv2_sanitize_script( $script_tag ) ); ?></code>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="lf-field">
                                <label for="lfv2_noscript_tag">
                                    <?php esc_html_e( 'Noscript Tag', 'lead-forensics' ); ?>
                                    <span class="lf-badge lf-badge--body">&lt;body&gt;</span>
                                </label>
                                <p class="lf-hint">
                                    <?php esc_html_e( 'Paste the full noscript tag. This provides a fallback for visitors who have JavaScript disabled.', 'lead-forensics' ); ?>
                                </p>
                                <textarea
                                    id="lfv2_noscript_tag"
                                    name="<?php echo esc_attr( LFV2_NOSCRIPT_TAG ); ?>"
                                    rows="3"
                                    spellcheck="false"
                                    placeholder="&lt;noscript&gt;...&lt;/noscript&gt;"
                                ><?php echo esc_textarea( $noscript_tag ); ?></textarea>
                            </div>

                        </div>
                        <div class="lf-card__footer">
                            <?php submit_button( __( 'Save Changes', 'lead-forensics' ), 'primary', 'submit', false, array( 'class' => 'lf-btn' ) ); ?>
                        </div>
                    </div>

                </form>
            </div>

            <div class="lf-sidebar">

                <div class="lf-promo">
                    <p class="lf-promo__eyebrow"><?php esc_html_e( 'World-leading B2B software', 'lead-forensics' ); ?></p>
                    <h3 class="lf-promo__heading"><?php esc_html_e( 'Identify your anonymous website visitors', 'lead-forensics' ); ?></h3>
                    <p class="lf-promo__body"><?php esc_html_e( 'Lead Forensics reveals the businesses visiting your website so your teams can act on high-intent opportunities in real time.', 'lead-forensics' ); ?></p>
                    <a href="https://www.leadforensics.com/how-it-works/" target="_blank" rel="noopener noreferrer" class="lf-promo__link">
                        <?php esc_html_e( 'See how it works', 'lead-forensics' ); ?> &rarr;
                    </a>
                </div>

                <div class="lf-card">
                    <div class="lf-card__header"><h3><?php esc_html_e( 'What is Lead Forensics?', 'lead-forensics' ); ?></h3></div>
                    <div class="lf-card__body">
                        <div class="lf-video">
                            <iframe
                                src="https://www.youtube.com/embed/Ttcrv9VgLL4"
                                title="<?php esc_attr_e( 'What is Lead Forensics?', 'lead-forensics' ); ?>"
                                frameborder="0"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                </div>

                <div class="lf-card lf-card--links">
                    <div class="lf-card__header"><h3><?php esc_html_e( 'Quick links', 'lead-forensics' ); ?></h3></div>
                    <div class="lf-card__body">
                        <ul>
                            <li><a href="https://portal.leadforensics.com/TrackingCode" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Tracking Code', 'lead-forensics' ); ?> &#8599;</a></li>
                            <li><a href="https://portal.leadforensics.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'LF Portal', 'lead-forensics' ); ?> &#8599;</a></li>
                            <li><a href="https://www.leadforensics.com/how-it-works/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'How it Works', 'lead-forensics' ); ?> &#8599;</a></li>
                            <li><a href="https://www.leadforensics.com/privacy-policy/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'lead-forensics' ); ?> &#8599;</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <?php
}

// ── Frontend: script tag into <head> ───────────────────

add_action( 'wp_head', 'lfv2_inject_script', 1 );

function lfv2_inject_script() {
    $tag = get_option( LFV2_SCRIPT_TAG, '' );
    if ( empty( $tag ) ) {
        return;
    }
    $allowed = array(
        'script' => array(
            'type' => true,
            'src'  => true,
            'id'   => true,
        ),
    );
    echo "\n" . wp_kses( lfv2_sanitize_script( $tag ), $allowed ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// ── Frontend: noscript tag immediately after <body> ────

add_action( 'wp_body_open', 'lfv2_inject_noscript', 1 );

function lfv2_inject_noscript() {
    $tag = get_option( LFV2_NOSCRIPT_TAG, '' );
    if ( empty( $tag ) ) {
        return;
    }
    $allowed = array(
        'noscript' => array(),
        'img'      => array(
            'src'    => true,
            'width'  => true,
            'height' => true,
            'alt'    => true,
            'style'  => true,
        ),
    );
    echo "\n" . wp_kses( $tag, $allowed ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
