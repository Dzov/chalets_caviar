<?php
/**
 * Theme Panel
 *
 * @package Ocean_Extra
 * @category Core
 * @author OceanWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Start Class
class Ocean_Extra_Theme_Panel {

	/**
	 * Start things up
	 */
	public function __construct() {

		// Add panel menu
		add_action( 'admin_menu', 				array( 'Ocean_Extra_Theme_Panel', 'add_page' ), 0 );

		// Add panel submenu
		add_action( 'admin_menu', 				array( 'Ocean_Extra_Theme_Panel', 'add_menu_subpage' ) );

		// Add custom CSS for the theme panel
		add_action( 'admin_enqueue_scripts', 	array( 'Ocean_Extra_Theme_Panel', 'css' ) );

		// Register panel settings
		add_action( 'admin_init', 				array( 'Ocean_Extra_Theme_Panel','register_settings' ) );

		// Load addon files
		self::load_addons();

	}

	/**
	 * Return customizer panels
	 *
	 * @since 1.0.8
	 */
	private static function get_panels() {

		$panels = array(
			'oe_general_panel' => array(
				'label'     => esc_html__( 'General Panel', 'ocean-extra' ),
			),
			'oe_typography_panel' => array(
				'label'     => esc_html__( 'Typography Panel', 'ocean-extra' ),
			),
			'oe_topbar_panel' => array(
				'label'     => esc_html__( 'Top Bar Panel', 'ocean-extra' ),
			),
			'oe_header_panel' => array(
				'label'     => esc_html__( 'Header Panel', 'ocean-extra' ),
			),
			'oe_blog_panel' => array(
				'label'     => esc_html__( 'Blog Panel', 'ocean-extra' ),
			),
			'oe_sidebar_panel' => array(
				'label'     => esc_html__( 'Sidebar Panel', 'ocean-extra' ),
			),
			'oe_footer_widgets_panel' => array(
				'label'     => esc_html__( 'Footer Widgets Panel', 'ocean-extra' ),
			),
			'oe_footer_bottom_panel' => array(
				'label'     => esc_html__( 'Footer Bottom Panel', 'ocean-extra' ),
			),
			'oe_custom_code_panel' => array(
				'label'     => esc_html__( 'Custom CSS/JS Panel', 'ocean-extra' ),
			),
		);

		// Apply filters and return
		return apply_filters( 'oe_theme_panels', $panels );

	}

	/**
	 * Return customizer options
	 *
	 * @since 1.0.8
	 */
	private static function get_options() {

		$options = array(
			'custom_logo' => array(
				'label'    	=> esc_html__( 'Upload your logo', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Add your own logo and retina logo used for retina screens.', 'ocean-extra' ),
			),
			'site_icon' => array(
				'label'    	=> esc_html__( 'Add your favicon', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'The favicon is used as a browser and app icon for your website.', 'ocean-extra' ),
			),
			'ocean_primary_color' => array(
				'label'    	=> esc_html__( 'Choose your primary color', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Replace the default primary and hover color by your own colors.', 'ocean-extra' ),
			),
			'ocean_typography_panel' => array(
				'label'    	=> esc_html__( 'Choose your typography', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Choose your own typography for any parts of your website.', 'ocean-extra' ),
				'panel' 	=> true,
			),
			'ocean_top_bar' => array(
				'label'    	=> esc_html__( 'Top bar options', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Enable/Disable the top bar, add your own paddings and colors.', 'ocean-extra' ),
			),
			'ocean_header_style' => array(
				'label'    	=> esc_html__( 'Header options', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Choose the style, the height and the colors for your site header.', 'ocean-extra' ),
			),
			'ocean_footer_widgets' => array(
				'label'    	=> esc_html__( 'Footer widgets options', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Choose the columns number, paddings and colors for the footer widgets.', 'ocean-extra' ),
			),
			'ocean_footer_bottom' => array(
				'label'    	=> esc_html__( 'Footer bottom options', 'ocean-extra' ),
				'desc'     	=> esc_html__( 'Add your copyright, paddings and colors for the footer bottom.', 'ocean-extra' ),
			),
		);

		// Apply filters and return
		return apply_filters( 'oe_customizer_options', $options );

	}

	/**
	 * Registers a new menu page
	 *
	 * @since 1.0.0
	 */
	public static function add_page() {
	  	add_menu_page(
			esc_html__( 'Theme Panel', 'ocean-extra' ),
			'Theme Panel',
			'manage_options',
			'oceanwp-panel',
			'',
			'dashicons-admin-generic',
			null
		);
	}

	/**
	 * Registers a new submenu page
	 *
	 * @since 1.0.0
	 */
	public static function add_menu_subpage(){
		add_submenu_page(
			'oceanwp-general',
			esc_html__( 'General', 'ocean-extra' ),
			esc_html__( 'General', 'ocean-extra' ),
			'manage_options',
			'oceanwp-panel',
			array( 'Ocean_Extra_Theme_Panel', 'create_admin_page' )
		);
	}

	/**
	 * Register a setting and its sanitization callback.
	 *
	 * @since 1.0.0
	 */
	public static function register_settings() {
		register_setting( 'oe_panels_settings', 'oe_panels_settings', array( 'Ocean_Extra_Theme_Panel', 'validate_panels' ) );
		register_setting( 'oceanwp_options', 'oceanwp_options', array( 'Ocean_Extra_Theme_Panel', 'admin_sanitize_license_options' ) ); 
	}

	/**
	 * Validate Settings Options
	 * 
	 * @since 1.0.0
	 */
	public static function admin_sanitize_license_options( $input ) {

		// filter to save all settings to database
		return $input;
	}

	/**
	 * Main Sanitization callback
	 *
	 * @since 1.2.2
	 */
	public static function validate_panels( $settings ) {

		// Get panels array
		$panels = self::get_panels();

		foreach ( $panels as $key => $val ) {

			$settings[$key] = ! empty( $settings[$key] ) ? true : false;

		}

		// Return the validated/sanitized settings
		return $settings;

	}

	/**
	 * Get settings.
	 *
	 * @since 1.2.2
	 */
	public static function get_setting( $option = '' ) {

		$defaults = self::get_default_settings();

		$settings = wp_parse_args( get_option( 'oe_panels_settings', $defaults ), $defaults );

		return isset( $settings[ $option ] ) ? $settings[ $option ] : false;

	}

	/**
	 * Get default settings value.
	 *
	 * @since 1.2.2
	 */
	public static function get_default_settings() {

		// Get panels array
		$panels = self::get_panels();

		// Add array
		$default = array();

		foreach ( $panels as $key => $val ) {
			$default[$key] = 1;
		}

		// Return
		return apply_filters( 'oe_default_panels', $default );

	}

	/**
	 * Settings page output
	 *
	 * @since 1.0.0
	 */
	public static function create_admin_page() {

		// Get panels array
		$theme_panels = self::get_panels();

		// Get options array
		$options = self::get_options();

		// YouTube img url
		$youtube = OE_URL . '/includes/panel/assets/img/youtube.png'; ?>

		<div class="wrap oceanwp-theme-panel clr">

			<h1><?php esc_attr_e( 'Theme Panel', 'ocean-extra' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php
				//Get current tab
				$curr_tab	= !empty( $_GET['tab'] ) ? $_GET['tab'] : 'features';

				// Feature url
				$feature_url = add_query_arg(
					array(
						'page' 	=> 'oceanwp-panel',
						'tab' 	=> 'features',
					),
					'admin.php'
				);

				// License url
				$license_url = add_query_arg(
					array(
						'page' 	=> 'oceanwp-panel',
						'tab' 	=> 'license',
					),
					'admin.php'
				);

				// Customizer url
				$customize_url = add_query_arg(
					array(
						'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
					),
					'customize.php'
				); ?>

				<a href="<?php echo esc_url( $feature_url ); ?>" class="nav-tab <?php echo $curr_tab == 'features' ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Features', 'ocean-extra' ); ?></a>

				<a href="<?php echo esc_url( $customize_url ); ?>" class="nav-tab"><?php esc_attr_e( 'Customize', 'ocean-extra' ); ?></a>

				<?php if ( apply_filters( 'oceanwp_licence_tab_enable', false ) ) { ?>
					<a href="<?php echo esc_url( $license_url ); ?>" class="nav-tab <?php echo $curr_tab == 'license' ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Licenses', 'ocean-extra' ); ?></a>
				<?php } ?>
			</h2>

			<div class="oceanwp-settings clr" <?php echo $curr_tab == 'features' ? '' : 'style="display:none;"'; ?>>

				<div class="left clr">

					<form id="oceanwp-theme-panel-form" method="post" action="options.php">

						<?php settings_fields( 'oe_panels_settings' ); ?>

						<div class="oceanwp-panels clr">

							<h2 class="oceanwp-title"><?php esc_html_e( 'Customizer Sections', 'ocean-extra' ); ?></h2>

							<p class="oceanwp-desc"><?php esc_html_e( 'Disable the Customizer panels that you do not have or need anymore to load it quickly. Your settings are saved, so do not worry.', 'ocean-extra' ); ?></p>

							<?php
							// Loop through theme pars and add checkboxes
							foreach ( $theme_panels as $key => $val ) :

								// Var
								$label  = isset ( $val['label'] ) ? $val['label'] : '';
								$desc  	= isset ( $val['desc'] ) ? $val['desc'] : '';

								// Get settings
								$settings = self::get_setting( $key ); ?>

								<div id="<?php echo esc_attr( $key ); ?>" class="column-wrap clr">

									<label for="oceanwp-switch-[<?php echo esc_attr( $key ); ?>]" class="column-name clr">
										<h3 class="title"><?php echo esc_attr( $label ); ?></h3>
									    <input type="checkbox" name="oe_panels_settings[<?php echo esc_attr( $key ); ?>]" value="true" id="oceanwp-switch-[<?php echo esc_attr( $key ); ?>]" <?php checked( $settings ); ?>>
										<?php if ( $desc ) { ?>
											<div class="desc"><?php echo esc_attr( $desc ); ?></div>
										<?php } ?>
									</label>

								</div>

							<?php endforeach; ?>

							<?php submit_button(); ?>

						</div>

					</form>

					<div class="divider clr"></div>

					<div class="oceanwp-options clr">

						<h2 class="oceanwp-title"><?php esc_html_e( 'Getting started', 'ocean-extra' ); ?></h2>

						<p class="oceanwp-desc"><?php esc_html_e( 'Take a look in the options of the Customizer and see yourself how easy and quick to customize your website as you wish.', 'ocean-extra' ); ?></p>

						<div class="options-inner clr">

							<?php
							// Loop through options
							foreach ( $options as $key => $val ) :

								// Var
								$label  = isset ( $val['label'] ) ? $val['label'] : '';
								$desc  	= isset ( $val['desc'] ) ? $val['desc'] : '';
								$panel  = isset ( $val['panel'] ) ? $val['panel'] : false;
								$id   	= $key;

								if ( true == $panel ) {
									$focus = 'panel';
								} else {
									$focus = 'control';
								} ?>

								<div class="column-wrap">

									<div class="column-inner clr">

										<h3 class="title"><?php echo esc_attr( $label ); ?></h3>
										<?php if ( $desc ) { ?>
											<p class="desc"><?php echo esc_attr( $desc ); ?></p>
										<?php } ?>

										<div class="bottom-column">
											<a class="option-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus['. $focus .']='. $id .'' ) ); ?>" target="_blank"><?php esc_html_e( 'Go to the option', 'ocean-extra' ); ?></a>
										</div>

									</div>

								</div>

							<?php endforeach; ?>

						</div><!-- .options-inner -->

					</div>

				</div>

				<div class="oceanwp-sidebar right clr">

					<div class="oceanwp-bloc oceanwp-review">
						<h3><?php esc_html_e( 'Are you a helpful person?', 'ocean-extra' ); ?></h3>
						<div class="content-wrap">
							<p class="content"><?php esc_html_e( 'Could you please do me a BIG favor and give to OceanWP and his associated plugins that you use, a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'ocean-extra' ); ?></p>
							<a href="https://wordpress.org/support/theme/oceanwp/reviews/#new-post" class="button owp-button" target="_blank"><?php esc_html_e( 'Leave my review', 'ocean-extra' ); ?></a>
							<p class="bottom-text"><?php esc_html_e( 'Thank you very much!', 'ocean-extra' ); ?></p>
						</div>
						<i class="dashicons dashicons-wordpress"></i>
					</div>

					<div class="oceanwp-bloc oceanwp-youtube">
						<p class="yt-img">
							<a href="https://www.youtube.com/c/OceanWP" target="_blank">
								<img src="<?php echo esc_url( $youtube ); ?>" alt="OceanWP YouTube Channel" />
							</a>
						</p>
						<div class="content-wrap">
							<p class="content"><?php esc_html_e( 'Video tutorials have been created on our YouTube channel to help you master OceanWP and its many features.', 'ocean-extra' ); ?></p>
							<a href="https://www.youtube.com/c/OceanWP" class="button owp-button" target="_blank"><?php esc_html_e( 'Check & Subscribe', 'ocean-extra' ); ?></a>
						</div>
						<i class="dashicons dashicons-video-alt3"></i>
					</div>

					<div class="metabox-holder postbox oceanwp-doc popular-articles clr">
						<h3 class="hndle"><?php esc_html_e( 'Documentation', 'ocean-extra' ); ?><a href="http://docs.oceanwp.org/" target="_blank"><?php esc_html_e( 'View all', 'ocean-extra' ); ?></a></h3>
						<div class="inside">
							<ul>
								<li><a href="http://docs.oceanwp.org/article/52-importing-the-sample-data" target="_blank"><?php esc_html_e( 'Importing The Sample Data', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/354-add-custom-css-and-js-to-your-website" target="_blank"><?php esc_html_e( 'Add Custom CSS and JS to Your Website', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/355-how-to-create-a-custom-header" target="_blank"><?php esc_html_e( 'How To Create a Custom Header', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/120-customize-your-layout-widths" target="_blank"><?php esc_html_e( 'Customize Your Layout Widths', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/56-increasing-memory-limit-to-php" target="_blank"><?php esc_html_e( 'Increasing Memory Limit To PHP', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/149-how-to-create-mega-menus" target="_blank"><?php esc_html_e( 'How To Create Mega Menus', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/150-how-to-add-widgets-to-a-mega-menu" target="_blank"><?php esc_html_e( 'How To Add Widgets To A Mega Menu', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/438-how-turning-oceanwp-multilingual-with-wpml" target="_blank"><?php esc_html_e( 'How Turning OceanWP multilingual', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/440-how-to-add-an-icon-to-a-menu-item" target="_blank"><?php esc_html_e( 'How to Add an Icon To a Menu Item', 'ocean-extra' ); ?></a></li>
								<li><a href="http://docs.oceanwp.org/article/128-how-to-add-custom-fonts" target="_blank"><?php esc_html_e( 'How To Add Custom Fonts', 'ocean-extra' ); ?></a></li>
							</ul>
						</div>
					</div>

					<div class="oceanwp-support clr">
						<p><?php esc_html_e( 'Need help? If you have checked the documentation and still having an issue, open a support ticket by clicking the button below.', 'ocean-extra' ); ?></p>
						<a href="https://oceanwp.org/support/" class="button owp-button" target="_blank"><?php esc_html_e( 'Submit Support Request', 'ocean-extra' ); ?></a>
					</div>

				</div>

			</div><!-- .oceanwp-settings -->

			<form id="oceanwp-license-form" method="post" action="options.php" <?php echo $curr_tab == 'license' ? '' : 'style="display:none;"'; ?>>
				<?php settings_fields( 'oceanwp_options' ); ?>

				<?php do_action( 'oceanwp_licenses_tab_top' ); ?>

				<table id="oceanwp-licenses" class="form-table">
					<tbody>
						<?php do_action( 'oceanwp_licenses_tab_fields' ); ?>
					</tbody>
				</table>

				<p class="submit"><input type="submit" name="oceanwp_licensekey_activateall" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'ocean-extra' ); ?>"></p>
			</form>

		</div>

	<?php
	}

	/**
	 * Include addons
	 *
	 * @since 1.0.0
	 */
	private static function load_addons() {

		// Addons directory location
		$dir = OE_PATH .'/includes/panel/';

		if ( is_admin() ) {

			// Import/Export
			require_once( $dir .'import-export.php' );

			// Recommended Plugins
			require_once( $dir .'rec-plugins.php' );

			// Extensions
			require_once( $dir .'extensions.php' );

		}

		// Scripts panel
		require_once( $dir .'scripts.php' );

	}

	/**
	 * Theme panel CSS
	 *
	 * @since 1.0.0
	 */
	public static function css( $hook ) {

		// Only load scripts when needed
		if ( 'toplevel_page_oceanwp-panel' != $hook ) {
			return;
		}

		// CSS
		wp_enqueue_style( 'oceanwp-theme-panel', plugins_url( '/assets/css/panel.min.css', __FILE__ ) );

	}

}
new Ocean_Extra_Theme_Panel();