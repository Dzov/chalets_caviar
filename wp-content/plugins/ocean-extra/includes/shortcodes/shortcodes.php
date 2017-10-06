<?php
/**
 * All shortcodes
 */

/**
 * Logo shortcode for the Custom Header style
 *
 * @since 1.1.1
 */
if ( ! function_exists( 'oceanwp_logo_shortcode' ) ) {

	function oceanwp_logo_shortcode( $atts ) {

		// Extract attributes
		extract( shortcode_atts( array(
			'position' 		=> 'left',
		), $atts ) );

		// Add classes
		$classes 		= array( 'custom-header-logo', 'clr' );
		$classes[] 		= $position;
		$classes 		= implode( ' ', $classes ); ?>

		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php get_template_part( 'partials/header/logo' ); ?>
		</div>

	<?php
	}

}
add_shortcode( 'oceanwp_logo', 'oceanwp_logo_shortcode' );

/**
 * Nav menu shortcode for the Custom Header style
 *
 * @since 1.1.1
 */
if ( ! function_exists( 'oceanwp_nav_shortcode' ) ) {

	function oceanwp_nav_shortcode( $atts ) {

		// Extract attributes
		extract( shortcode_atts( array(
			'position' 		=> 'left',
		), $atts ) );

		// Add classes
		$classes 		= array( 'custom-header-nav', 'clr' );
		$classes[] 		= $position;
		$classes 		= implode( ' ', $classes ); ?>

		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php
			// Navigation
			get_template_part( 'partials/header/nav' );

			// Mobile nav
			get_template_part( 'partials/header/mobile-icon' ); ?>
		</div>

	<?php
	}

}
add_shortcode( 'oceanwp_nav', 'oceanwp_nav_shortcode' );

/**
 * Dynamic date shortcode
 *
 * @since 1.1.1
 */
if ( ! function_exists( 'oceanwp_date_shortcode' ) ) {

	function oceanwp_date_shortcode( $atts ) {

		// Extract attributes
		extract( shortcode_atts( array(
			'year' => '',
		), $atts ) );

		// Var
		$date = '';

		if ( '' != $year ) {
			$date .= $year . ' - ';
		}

		$date .= date( 'Y' );

		return esc_attr( $date );
			
	}

}
add_shortcode( 'oceanwp_date', 'oceanwp_date_shortcode' );

/**
 * Search form shortcode
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'oceanwp_search_shortcode' ) ) {

	function oceanwp_search_shortcode( $atts ) {

		// Extract attributes
		extract( shortcode_atts( array(
			'width' 		=> '',
			'height' 		=> '',
			'placeholder' 	=> esc_html__( 'Search', 'ocean-extra' ),
			'btn_icon' 		=> 'icon-magnifier',
		), $atts ) );

		// Styles
		$style = array();
		if ( ! empty( $width ) ) {
			$style[] = 'width: '. intval( $width ) .'px;';
		}
		if ( ! empty( $height ) ) {
			$style[] = 'height: '. intval( $height ) .'px;min-height: '. intval( $height ) .'px;';
		}
		$style = implode( '', $style );

		if ( $style ) {
			$style = wp_kses( $style, array() );
			$style = ' style="' . esc_attr( $style) . '"';
		}

		$html = '<form method="get" class="oceanwp-searchform" id="searchform" action="'. esc_url( home_url( '/' ) ) .'"'. $style .'>';
			$html .= '<input type="text" class="field" name="s" id="s" placeholder="'. strip_tags( $placeholder ) .'">';
			$html .= '<button type="submit" class="search-submit" value=""><i class="'. esc_attr( $btn_icon ) .'"></i></button>';
		$html .= '</form>';

		// Return
		return $html;

	}

}
add_shortcode( 'oceanwp_search', 'oceanwp_search_shortcode' );

/**
 * Site url shortcode
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'oceanwp_site_url_shortcode' ) ) {

	function oceanwp_site_url_shortcode( $atts ) {

		// Extract attributes
		extract( shortcode_atts( array(
			'target' => 'self',
		), $atts ) );

		$html = '<a href="'. esc_url( home_url( '/' ) ) .'" target="_'. esc_attr( $target ) .'">'. esc_html( get_bloginfo( 'name' ) ) .'</a>';

		// Return
		return $html;
			
	}

}
add_shortcode( 'oceanwp_site_url', 'oceanwp_site_url_shortcode' );

/**
 * Login/logout link
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'oceanwp_login_shortcode' ) ) {

	function oceanwp_login_shortcode( $atts ) {

		extract( shortcode_atts( array(
			'custom_url' 		=> '',
			'login_text' 		=> esc_html__( 'Login', 'ocean-extra' ),
			'logout_text' 		=> esc_html__( 'Log Out', 'ocean-extra' ),
			'target' 			=> 'self',
			'logout_redirect' 	=> '',
		), $atts ) );

		// Custom login url
		if ( ! empty( $custom_url ) ) {
			$login_url = $custom_url;
		} else {
			$login_url = wp_login_url();
		}

		// Logout redirect
		if ( ! empty( $logout_redirect ) ) {
			$current = get_permalink();
			if ( 'current' == $logout_redirect
				&& $current ) {
				$logout_redirect = $current;
			} else {
				$logout_redirect = $logout_redirect;
			}
		} else {
			$logout_redirect = home_url( '/' );
		}

		// Logged in link
		if ( is_user_logged_in() ) {
			return '<a href="'. wp_logout_url( $logout_redirect ) .'" title="'. esc_attr( $logout_text ) .'" class="oceanwp-logout">'. strip_tags( $logout_text ) .'</a>';
		}

		// Logged out link
		else {
			return '<a href="'. esc_url( $login_url ) .'" title="'. esc_attr( $login_text ) .'" class="oceanwp-login" target="_'. esc_attr( $target ) .'">'. strip_tags( $login_text ) .'</a>';
		}

	}

}
add_shortcode( 'oceanwp_login', 'oceanwp_login_shortcode' );

/**
 * Login/logout link
 *
 * @since 1.2.1
 */
if ( ! function_exists( 'oceanwp_current_user_shortcode' ) ) {

	function oceanwp_current_user_shortcode( $atts ) {

		extract( shortcode_atts( array(
			'text' 			=> esc_html__( 'Welcome back', 'ocean-extra' ),
			'display' 		=> 'display_name',
		), $atts ) );

		// Get current user
		$current_user = wp_get_current_user();

		// Text
		if ( ! empty( $text ) ) {
			$text = $text . ' ';
		}

	    // If logged in
		if ( is_user_logged_in() ) {
			return $text . $current_user->$display;
		}

		// Return if not logged in
		else {
			return;
		}

	}

}
add_shortcode( 'oceanwp_current_user', 'oceanwp_current_user_shortcode' );

/**
 * WooCommerce fragments
 *
 * @since 1.2.2
 */
if ( ! function_exists( 'oceanwp_woo_fragments' ) ) {

	function oceanwp_woo_fragments( $fragments ) {
		$text = oceanwp_tm_translation( 'owp_popup_bottom_text', get_theme_mod( 'owp_popup_bottom_text', '[oceanwp_woo_free_shipping_left]' ) );
		$fragments['.oceanwp-woo-total'] 			= '<span class="oceanwp-woo-total">' . WC()->cart->get_cart_total() . '</span>';
	    $fragments['.oceanwp-woo-cart-count'] 		= '<span class="oceanwp-woo-cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
	    $fragments['.oceanwp-woo-free-shipping'] 	= '<span class="oceanwp-woo-free-shipping">' . do_shortcode( $text ) . '</span>';
	    return $fragments;
	}

}
add_filter( 'woocommerce_add_to_cart_fragments', 'oceanwp_woo_fragments', 10, 1 );

/**
 * WooCommerce total cart
 *
 * @since 1.2.2
 */
if ( ! function_exists( 'oceanwp_woo_total_cart_shortcode' ) ) {

	function oceanwp_woo_total_cart_shortcode() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$html  = '<span class="oceanwp-woo-total">';
	    $html .= WC()->cart->get_cart_total();
	    $html .= '</span>';
			
		return $html;

	}

}
add_shortcode( 'oceanwp_woo_total_cart', 'oceanwp_woo_total_cart_shortcode' );

/**
 * WooCommerce items cart
 *
 * @since 1.2.2
 */
if ( ! function_exists( 'oceanwp_woo_cart_items_shortcode' ) ) {

	function oceanwp_woo_cart_items_shortcode() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$html  = '<span class="oceanwp-woo-cart-count">';
	    $html .= WC()->cart->get_cart_contents_count();
	    $html .= '</span>';
			
		return $html;

	}

}
add_shortcode( 'oceanwp_woo_cart_items', 'oceanwp_woo_cart_items_shortcode' );

/**
 * WooCommerce free shipping left
 *
 * @since 1.2.2
 */
if ( ! function_exists( 'oceanwp_woo_free_shipping_left' ) ) {

	function oceanwp_woo_free_shipping_left( $content, $content_reached, $multiply_by = 1 ) {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( '' == $content ) {
			$content = esc_html__( 'Buy for %left_to_free% more and get free shipping', 'ocean-extra' );
		}

		if ( '' == $content_reached ) {
			$content_reached = esc_html__( 'You have Free delivery!', 'ocean-extra' );
		}

		$min_free_shipping_amount = 0;

		$legacy_free_shipping = new WC_Shipping_Legacy_Free_Shipping();
		if ( 'yes' === $legacy_free_shipping->enabled ) {
			if ( in_array( $legacy_free_shipping->requires, array( 'min_amount', 'either', 'both' ) ) ) {
				$min_free_shipping_amount = $legacy_free_shipping->min_amount;
			}
		}
		if ( 0 == $min_free_shipping_amount ) {
			if ( function_exists( 'WC' ) && ( $wc_shipping = WC()->shipping ) && ( $wc_cart = WC()->cart ) ) {
				if ( $wc_shipping->enabled ) {
					if ( $packages = $wc_cart->get_shipping_packages() ) {
						$shipping_methods = $wc_shipping->load_shipping_methods( $packages[0] );
						foreach ( $shipping_methods as $shipping_method ) {
							if ( 'yes' === $shipping_method->enabled && 0 != $shipping_method->instance_id ) {
								if ( 'WC_Shipping_Free_Shipping' === get_class( $shipping_method ) ) {
									if ( in_array( $shipping_method->requires, array( 'min_amount', 'either', 'both' ) ) ) {
										$min_free_shipping_amount = $shipping_method->min_amount;
										break;
									}
								}
							}
						}
					}
				}
			}
		}

		if ( 0 != $min_free_shipping_amount ) {
			if ( isset( WC()->cart->cart_contents_total ) ) {
				$total = ( WC()->cart->prices_include_tax ) ? WC()->cart->cart_contents_total + array_sum( WC()->cart->taxes ) : WC()->cart->cart_contents_total;
				if ( $total >= $min_free_shipping_amount ) {
					return do_shortcode( $content_reached );
				} else {
					$content = str_replace( '%left_to_free%',             '<span class="oceanwp-woo-left-to-free">'. wc_price( ( $min_free_shipping_amount - $total ) * $multiply_by ) .'</span>', $content );
					$content = str_replace( '%free_shipping_min_amount%', '<span class="oceanwp-woo-left-to-free">'. wc_price( ( $min_free_shipping_amount )          * $multiply_by ) .'</span>', $content );
					return $content;
				}
			}
		}

	}

}

if ( ! function_exists( 'oceanwp_woo_free_shipping_left_shortcode' ) ) {

	function oceanwp_woo_free_shipping_left_shortcode( $atts, $content ) {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		extract( shortcode_atts( array(
			'content' 			=> esc_html__( 'Buy for %left_to_free% more and get free shipping', 'ocean-extra' ),
			'content_reached' 	=> esc_html__( 'You have Free delivery!', 'ocean-extra' ),
			'multiply_by' 		=> 1,
		), $atts ) );

		return oceanwp_woo_free_shipping_left( '<span class="oceanwp-woo-free-shipping">'. $content .'</span>', '<span class="oceanwp-woo-free-shipping">'. $content_reached .'</span>', $multiply_by );

	}

}
add_shortcode( 'oceanwp_woo_free_shipping_left', 'oceanwp_woo_free_shipping_left_shortcode' );