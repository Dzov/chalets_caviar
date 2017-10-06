<?php
/**
 * Recommended Plugins
 *
 * @package 	Ocean_Extra
 * @category 	Core
 * @author 		OceanWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Start Class
class Ocean_Extra_Recommended_Plugins {

	/**
	 * Start things up
	 */
	public function __construct() {
		include_once( OE_PATH .'/includes/panel/assets/class-plugin-installer.php' );
		add_action( 'admin_menu', array( $this, 'add_page' ), 999 );
	}

	/**
	 * Add sub menu page
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		add_submenu_page(
			'oceanwp-panel',
			esc_html__( 'Rec. Plugins', 'ocean-extra' ),
			esc_html__( 'Rec. Plugins', 'ocean-extra' ),
			'manage_options',
			'oceanwp-panel-rec-plugins',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Settings page output
	 *
	 * @since 1.0.0
	 */
	public function create_admin_page() {

		// Free extensions
		$free_plugins = array(   			
			array(
				'slug' => 'elementor',
			),
			array(
				'slug' => 'easy-digital-downloads',
			),
			array(
				'slug' => 'woocommerce',
			),
			array(
				'slug' => 'contact-form-7',
			),
			array(
				'slug' => 'weglot',
			),
			array(
				'slug' => 'wordpress-seo',
			),
			array(
				'slug' => 'ultimate-member',
			),
		);

		// Premium extensions
		$premium_plugins = array(
			array(
				'slug' 			=> 'wp-rocket',
				'full_url' 		=> 'https://wp-rocket.me/',
				'name' 			=> esc_html__( 'WP Rocket', 'ocean-extra' ),
				'description' 	=> esc_html__( 'Caching creates an ultra-fast load time, essential for improving Search Engine Optimization. When you turn on WP Rocket, page caching is immediately activated.', 'ocean-extra' ),
				'icons' 		=> plugins_url( '/assets/img/rec-plugins/wp-rocket-icon.png', __FILE__ ),
				'author' 		=> esc_html__( 'WP Rocket', 'ocean-extra' ),
				'author_url' 	=> 'https://wp-rocket.me/',
			),
			array(
				'slug' 			=> 'affiliatewp',
				'full_url' 		=> 'https://affiliatewp.com/',
				'name' 			=> esc_html__( 'AffiliateWP', 'ocean-extra' ),
				'description' 	=> esc_html__( 'This plugin provides a complete affiliate management system for your WordPress website that seamlessly integrates with all major WordPress e-commerce and membership platforms.', 'ocean-extra' ),
				'icons' 		=> plugins_url( '/assets/img/rec-plugins/affiliatewp-icon.png', __FILE__ ),
				'author' 		=> esc_html__( 'AffiliateWP, LLC', 'ocean-extra' ),
				'author_url' 	=> 'https://affiliatewp.com/',
			),
			array(
				'slug' 			=> 'gravity-forms',
				'full_url' 		=> 'http://www.gravityforms.com/',
				'name' 			=> esc_html__( 'Gravity Forms', 'ocean-extra' ),
				'description' 	=> esc_html__( 'Build and publish your forms in just minutes. Select your fields, configure your options and easily embed forms on your WordPress powered site using the built-in tools.', 'ocean-extra' ),
				'icons' 		=> plugins_url( '/assets/img/rec-plugins/gravity-forms-icon.png', __FILE__ ),
				'author' 		=> esc_html__( 'rocketgenius', 'ocean-extra' ),
				'author_url' 	=> 'http://www.rocketgenius.com/',
			),
			array(
				'slug' 			=> 'slider-revolution',
				'full_url' 		=> 'https://codecanyon.net/item/slider-revolution-responsive-wordpress-plugin/2751380?ref=OceanWP',
				'name' 			=> esc_html__( 'Slider Revolution', 'ocean-extra' ),
				'description' 	=> esc_html__( 'This plugin is an innovative, responsive WordPress Slider Plugin that displays your content the beautiful way. Even beginners will manage to create beautiful presentations.', 'ocean-extra' ),
				'icons' 		=> plugins_url( '/assets/img/rec-plugins/slider-revolution-icon.png', __FILE__ ),
				'author' 		=> esc_html__( 'ThemePunch', 'ocean-extra' ),
				'author_url' 	=> 'https://www.themepunch.com/',
			),
			array(
				'slug' 			=> 'wpbakery-visual-composer',
				'full_url' 		=> 'https://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=OceanWP',
				'name' 			=> esc_html__( 'Visual Composer', 'ocean-extra' ),
				'description' 	=> esc_html__( 'This plugin is an easy to use drag and drop page builder that will help you to create any layout you can imagine fast and easy – no programming knowledge required!', 'ocean-extra' ),
				'icons' 		=> plugins_url( '/assets/img/rec-plugins/visual-composer-icon.png', __FILE__ ),
				'author' 		=> esc_html__( 'Michael M', 'ocean-extra' ),
				'author_url' 	=> 'http://wpbakery.com/',
			),
		); ?>

		<div id="oceanwp-rec-plugins-wrap" class="wrap">
				
			<h2>OceanWP - <?php esc_attr_e( 'Recommended Plugins', 'ocean-extra' ); ?></h2>

			<div class="oceanwp-desc notice notice-warning">
				<p><?php esc_attr_e( 'Below you will find plugins I personally like and recommend. None of these plugins are required for the theme to work, they simply add additional functionality.', 'ocean-extra' ); ?></p>
			</div>
			
			<div class="oe-plugin-installer">
				<?php
				if ( class_exists( 'Ocean_Extra_Plugin_Installer' ) ) {
					Ocean_Extra_Plugin_Installer::init( $free_plugins );
					Ocean_Extra_Plugin_Installer::init_premium( $premium_plugins );
				} ?>
			</div>

			<div class="oceanwp-callout">
				<p><strong><?php esc_attr_e( 'Disclosure:', 'ocean-extra' ); ?></strong> <?php esc_attr_e( 'This page contains external affiliate links that may result in I receive a commission if you choose to purchase said product. I receive no payment for speaking well of plugins or list them on this page.', 'ocean-extra' ); ?></p>
			</div>

		</div>
			
	<?php
	}
}
new Ocean_Extra_Recommended_Plugins();