<?php
/**
 * Admin Settings Page — Simple Notification Bar
 *
 * Registers the settings page, settings section, individual fields,
 * and the sanitization callback using the WordPress Settings API.
 *
 * WordPress Settings API flow:
 *   1. register_setting()    → declares the option key + sanitize callback.
 *   2. add_settings_section()→ creates a named group on the page.
 *   3. add_settings_field()  → adds one field inside a section.
 *   4. settings_fields()     → outputs nonce + action hidden fields in the form.
 *   5. do_settings_sections()→ renders all registered sections and fields.
 *
 * @package SimpleNotificationBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ──────────────────────────────────────────────
// ADMIN MENU
// admin_menu fires after the admin is initialised.
// We add a top-level Settings sub-page here.
// ──────────────────────────────────────────────
add_action( 'admin_menu', 'snb_add_settings_page' );

/**
 * Register the plugin settings page under Settings > Notification Bar.
 */
function snb_add_settings_page(): void {
	add_options_page(
		__( 'Simple Notification Bar Settings', 'simple-notification-bar' ), // <title> tag text.
		__( 'Notification Bar', 'simple-notification-bar' ),                 // Sidebar menu label.
		'manage_options',                                                      // Required capability.
		'simple-notification-bar',                                             // Menu slug (unique).
		'snb_render_settings_page'                                             // Callback to render HTML.
	);
}

// ──────────────────────────────────────────────
// SETTINGS REGISTRATION
// admin_init fires on every admin page load.
// This is the correct hook for register_setting()
// and add_settings_field() calls.
// ──────────────────────────────────────────────
add_action( 'admin_init', 'snb_register_settings' );

/**
 * Register the option, section, and all fields via the Settings API.
 */
function snb_register_settings(): void {

	// ── 1. Register the option ────────────────
	// Tells WordPress:
	//   - which option group this belongs to (used in settings_fields()).
	//   - the actual wp_options key to save to.
	//   - the sanitize callback that runs before saving.
	register_setting(
		'snb_settings_group',    // Option group name — matches settings_fields() below.
		SNB_OPTION_KEY,          // The wp_options key: 'snb_settings'.
		array(
			'sanitize_callback' => 'snb_sanitize_settings',
			'default'           => array(
				'bar_text'   => '',
				'bg_color'   => '#222222',
				'visibility' => 0,
			),
		)
	);

	// ── 2. Add a settings section ─────────────
	// A section is a visual grouping of related fields.
	// We only need one section for this plugin.
	add_settings_section(
		'snb_main_section',                                         // Section ID.
		__( 'Bar Configuration', 'simple-notification-bar' ),      // Section heading.
		'snb_main_section_description',                             // Optional description callback.
		'simple-notification-bar'                                   // Page slug (must match add_options_page).
	);

	// ── 3. Add individual fields ──────────────

	// Field: Announcement Text.
	add_settings_field(
		'snb_field_bar_text',
		__( 'Announcement Text', 'simple-notification-bar' ),
		'snb_field_bar_text_cb',
		'simple-notification-bar',
		'snb_main_section',
		array( 'label_for' => 'snb_bar_text' ) // Associates <label> with the input via id.
	);

	// Field: Background Color.
	add_settings_field(
		'snb_field_bg_color',
		__( 'Background Color', 'simple-notification-bar' ),
		'snb_field_bg_color_cb',
		'simple-notification-bar',
		'snb_main_section',
		array( 'label_for' => 'snb_bg_color' )
	);

	// Field: Visibility Toggle.
	add_settings_field(
		'snb_field_visibility',
		__( 'Show Notification Bar', 'simple-notification-bar' ),
		'snb_field_visibility_cb',
		'simple-notification-bar',
		'snb_main_section'
		// No label_for — the label wraps the checkbox directly in the callback.
	);
}

// ──────────────────────────────────────────────
// SANITIZATION CALLBACK
// This runs automatically before WordPress saves
// the data to wp_options. Never trust user input.
// ──────────────────────────────────────────────

/**
 * Sanitize all settings fields before they are saved to the database.
 *
 * Rules applied:
 *   bar_text   → sanitize_text_field() strips tags + extra whitespace.
 *   bg_color   → validated as a proper 3 or 6-character HEX color.
 *   visibility → cast to integer 0 or 1 (checkbox value).
 *
 * @param  array $input Raw POST data array.
 * @return array        Sanitized settings array ready for the database.
 */
function snb_sanitize_settings( array $input ): array {
	$sanitized = array();

	// Sanitize announcement text — strip HTML tags and extra whitespace.
	$sanitized['bar_text'] = isset( $input['bar_text'] )
		? sanitize_text_field( $input['bar_text'] )
		: '';

	// Validate background color — must be a valid CSS HEX value.
	// sanitize_hex_color() returns null for invalid values; fall back to default.
	if ( isset( $input['bg_color'] ) ) {
		$color = sanitize_hex_color( $input['bg_color'] );
		$sanitized['bg_color'] = $color ? $color : '#222222';
	} else {
		$sanitized['bg_color'] = '#222222';
	}

	// Checkbox: unchecked checkboxes are NOT submitted in POST data,
	// so we check for existence and cast to 1 or 0.
	$sanitized['visibility'] = ! empty( $input['visibility'] ) ? 1 : 0;

	return $sanitized;
}

// ──────────────────────────────────────────────
// FIELD CALLBACKS
// Each callback renders exactly one form field.
// Keeping them in individual functions makes the
// code easy to extend later.
// ──────────────────────────────────────────────

/**
 * Optional description rendered below the section heading.
 */
function snb_main_section_description(): void {
	echo '<p>' . esc_html__( 'Configure the announcement bar that appears at the top of your site.', 'simple-notification-bar' ) . '</p>';
}

/**
 * Render the Announcement Text textarea.
 */
function snb_field_bar_text_cb(): void {
	$settings = get_option( SNB_OPTION_KEY );
	$value    = isset( $settings['bar_text'] ) ? $settings['bar_text'] : '';
	?>
	<input
		type="text"
		id="snb_bar_text"
		name="<?php echo esc_attr( SNB_OPTION_KEY ); ?>[bar_text]"
		value="<?php echo esc_attr( $value ); ?>"
		class="regular-text"
		placeholder="<?php esc_attr_e( 'Enter your announcement…', 'simple-notification-bar' ); ?>"
	/>
	<p class="description">
		<?php esc_html_e( 'The message displayed in the notification bar. Leave empty to hide the bar.', 'simple-notification-bar' ); ?>
	</p>
	<?php
}

/**
 * Render the Background Color picker input.
 *
 * type="color" provides a native browser color picker.
 * The value must be a valid HEX string for it to pre-populate correctly.
 */
function snb_field_bg_color_cb(): void {
	$settings = get_option( SNB_OPTION_KEY );
	$value    = isset( $settings['bg_color'] ) ? $settings['bg_color'] : '#222222';
	?>
	<input
		type="color"
		id="snb_bg_color"
		name="<?php echo esc_attr( SNB_OPTION_KEY ); ?>[bg_color]"
		value="<?php echo esc_attr( $value ); ?>"
	/>
	<p class="description">
		<?php esc_html_e( 'Choose the background color of the notification bar.', 'simple-notification-bar' ); ?>
	</p>
	<?php
}

/**
 * Render the Visibility checkbox.
 *
 * checked() is a WordPress helper that outputs checked="checked"
 * when the first and second arguments match — avoids manual comparison.
 */
function snb_field_visibility_cb(): void {
	$settings = get_option( SNB_OPTION_KEY );
	$enabled  = isset( $settings['visibility'] ) ? (int) $settings['visibility'] : 0;
	?>
	<label for="snb_visibility">
		<input
			type="checkbox"
			id="snb_visibility"
			name="<?php echo esc_attr( SNB_OPTION_KEY ); ?>[visibility]"
			value="1"
			<?php checked( 1, $enabled ); ?>
		/>
		<?php esc_html_e( 'Enable the notification bar on the frontend', 'simple-notification-bar' ); ?>
	</label>
	<?php
}

// ──────────────────────────────────────────────
// PAGE RENDER CALLBACK
// This function outputs the full settings page HTML.
// current_user_can() is a second capability check
// (add_options_page already enforces it, but being
// explicit here is a WordPress best practice).
// ──────────────────────────────────────────────

/**
 * Render the full settings page HTML.
 */
function snb_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="options.php">
			<?php
			/**
			 * settings_fields() outputs three hidden fields required by
			 * the Settings API: option_page nonce, action, and _wp_http_referer.
			 * The argument must match the option group in register_setting().
			 */
			settings_fields( 'snb_settings_group' );

			/**
			 * do_settings_sections() renders every section + field that was
			 * registered against the 'simple-notification-bar' page slug.
			 */
			do_settings_sections( 'simple-notification-bar' );

			// submit_button() outputs a properly styled Save Changes button.
			submit_button( __( 'Save Settings', 'simple-notification-bar' ) );
			?>
		</form>
	</div>
	<?php
}
