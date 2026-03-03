<?php
/**
 * Plugin Name:       Simple Notification Bar
 * Plugin URI:        https://example.com/simple-notification-bar
 * Description:       Displays a configurable announcement bar at the top of every frontend page.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-notification-bar
 * Domain Path:       /languages
 *
 * @package SimpleNotificationBar
 */

// ──────────────────────────────────────────────
// Block direct file access — always the first line.
// ──────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ──────────────────────────────────────────────
// CONSTANTS
// Define reusable values once here so they never
// need to be hardcoded anywhere else in the plugin.
// ──────────────────────────────────────────────

/** Absolute filesystem path to the plugin root (trailing slash included). */
define( 'SNB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/** Public URL to the plugin root (trailing slash included). */
define( 'SNB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/** Plugin version — bump on every release to bust browser caches. */
define( 'SNB_VERSION', '1.0.0' );

/**
 * The wp_options key under which ALL plugin settings are stored as a
 * single serialised array. Using one key keeps the options table clean.
 */
define( 'SNB_OPTION_KEY', 'snb_settings' );

// ──────────────────────────────────────────────
// TEXT DOMAIN (i18n)
// Must be loaded on 'plugins_loaded' — the first
// hook that fires after all plugins are included.
// ──────────────────────────────────────────────
add_action( 'plugins_loaded', 'snb_load_textdomain' );

/**
 * Load plugin text domain so strings are translatable.
 *
 * Translation files live at:
 *   /languages/simple-notification-bar-{locale}.mo
 */
function snb_load_textdomain(): void {
	load_plugin_textdomain(
		'simple-notification-bar',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

// ──────────────────────────────────────────────
// ADMIN
// Load the settings page only inside wp-admin.
// is_admin() prevents this file from being parsed
// on every single frontend request.
// ──────────────────────────────────────────────
if ( is_admin() ) {
	require_once SNB_PLUGIN_DIR . 'admin/settings-page.php';
}

// ──────────────────────────────────────────────
// FRONTEND ASSETS
// wp_enqueue_scripts is the correct, dedicated hook
// for loading CSS/JS on the frontend. Never use
// init or wp_head for enqueueing scripts.
// ──────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'snb_enqueue_frontend_assets' );

/**
 * Enqueue frontend stylesheet and script.
 *
 * Assets are only registered when the bar is visible,
 * so no unused files are ever sent to the browser.
 */
function snb_enqueue_frontend_assets(): void {
	$settings = get_option( SNB_OPTION_KEY );

	// Bail early if visibility is off or bar text is empty.
	if ( empty( $settings['visibility'] ) || empty( $settings['bar_text'] ) ) {
		return;
	}

	wp_enqueue_style(
		'snb-frontend-style',            // Unique handle.
		SNB_PLUGIN_URL . 'assets/css/frontend.css',
		array(),                         // No style dependencies.
		SNB_VERSION                      // Version for cache busting.
	);

	wp_enqueue_script(
		'snb-frontend-script',
		SNB_PLUGIN_URL . 'assets/js/frontend.js',
		array(),                         // No JS dependencies — vanilla JS only.
		SNB_VERSION,
		array( 'strategy' => 'defer' )  // Defer = non-blocking, modern approach.
	);
}

// ──────────────────────────────────────────────
// RENDER THE BAR
// wp_body_open fires immediately after <body> opens.
// All themes following WP standards since 5.2 call
// wp_body_open() inside their header template.
// ──────────────────────────────────────────────
add_action( 'wp_body_open', 'snb_render_notification_bar' );

/**
 * Output the notification bar HTML.
 *
 * Output escaping rules applied here:
 *   - esc_attr()  → used inside HTML tag attributes.
 *   - esc_html()  → used for text content between tags.
 *   - esc_attr_e()→ translated string inside an attribute.
 *
 * The inline style sets the background color chosen in settings.
 * All other visual styling is handled by frontend.css.
 */
function snb_render_notification_bar(): void {
	$settings = get_option( SNB_OPTION_KEY );

	// Do not render if visibility is off or there is no text to show.
	if ( empty( $settings['visibility'] ) || empty( $settings['bar_text'] ) ) {
		return;
	}

	// Prepare escaped values for output.
	$bg_color = ! empty( $settings['bg_color'] )
		? esc_attr( $settings['bg_color'] )
		: '#222222';

	$bar_text = esc_html( $settings['bar_text'] );

	// ── HTML output ──────────────────────────────
	// Separating PHP logic from the HTML block below
	// keeps the markup clean and readable.
	// role="alert" + aria-live make the bar accessible
	// to screen readers without any extra JS.
	?>
	<div
		id="snb-notification-bar"
		style="background-color:<?php echo $bg_color; ?>;"
		role="alert"
		aria-live="polite"
	>
		<span class="snb-bar-text"><?php echo $bar_text; ?></span>
		<button
			class="snb-dismiss-btn"
			aria-label="<?php esc_attr_e( 'Dismiss notification', 'simple-notification-bar' ); ?>"
			type="button"
		>&#10005;</button>
	</div>
	<?php
}
