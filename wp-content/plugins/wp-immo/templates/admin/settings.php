<?php
/**
 * This file controls all of the content from the Settings page.
 */
# Exit if accessed directly
defined( 'ABSPATH' ) or exit;
$message      = '';
$notice_class = 'updated';
$notice_style = '';
$text_goback  = sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', WPIMMO_PLUGIN_NAME ), 'javascript:history.go(-1)' );
$links = array(
    'main' => '?page=' . WPIMMO_PLUGIN_NAME,
    'groups' => '?page=' . WPIMMO_PLUGIN_NAME . '&amp;tab=groups',
    'search' => '?page=' . WPIMMO_PLUGIN_NAME . '&amp;tab=search',
    'tools' => '?page=' . WPIMMO_PLUGIN_NAME . '&amp;tab=tools',
    'help' => '?page=' . WPIMMO_PLUGIN_NAME . '&amp;tab=help',
);

$current_tab = WPImmo_Admin::get_current_tab();

switch ( $_REQUEST['action'] ):
    // Save settings
    case 'save' :
        //test nonce
        if ( !wp_verify_nonce( $_POST['wpimmo_settings_nonce'], 'wpimmo_settings' ) ):
            $message      = __( 'Nonce error.', WPIMMO_PLUGIN_NAME );
            $notice_class = 'error';
        //test droits
        elseif ( !current_user_can( 'manage_options' ) ):
            $message      = '<p><strong>' . __( 'You are not allowed to change the settings.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_class = 'error';
        else:
            WPImmo_Process::save_options();
            $message      = '<p><strong>' . __( 'Your settings have been saved.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_style = 'display:block;';
        endif;
        break;
    // Save search boxes
    case 'save_search' :
        //test nonce
        if ( !wp_verify_nonce( $_POST['wpimmo_search_nonce'], 'wpimmo_settings' ) ):
            $message      = __( 'Nonce error.', WPIMMO_PLUGIN_NAME );
            $notice_class = 'error';
        //test droits
        elseif ( !current_user_can( 'manage_options' ) ):
            $message      = '<p><strong>' . __( 'You are not allowed to change the settings.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_class = 'error';
        else:
            WPImmo_Process::save_search_boxes();
            $message      = '<p><strong>' . __( 'Your search boxes have been saved.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_style = 'display:block;';
        endif;
        break;
    // Save groups
    case 'save_groups' :
        //test nonce
        if ( !wp_verify_nonce( $_POST['wpimmo_groups_nonce'], 'wpimmo_settings' ) ):
            $message      = __( 'Nonce error.', WPIMMO_PLUGIN_NAME );
            $notice_class = 'error';
        //test droits
        elseif ( !current_user_can( 'manage_options' ) ):
            $message      = '<p><strong>' . __( 'You are not allowed to change the settings.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_class = 'error';
        else:
            WPImmo_Process::save_groups();
            $message      = '<p><strong>' . __( 'Your groups have been saved.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
            $notice_style = 'display:block;';
        endif;
        break;
    // Import or Delete
    case 'process' :
        switch ( $current_tab ):
            // Import
            case 'import':
                if ( WPImmo_Tools::is_file( self::$options['feed_url'] ) === false ):
                    $message      = '<p><strong>' . __( 'WP Immo feed not found.', WPIMMO_PLUGIN_NAME ) . '</strong></p>';
                    $notice_class = 'error';
                else:
                    $process_tab = WPImmo_Process::prepare_import();
                endif;
                break;
            // Delete
            case 'delete' :
                $process_tab['process'] = WPImmo_Process::prepare_delete();
                break;
            // Default, nothing to do here
            default :
                break;
        endswitch;
        break;
    // Default, nothing to do here
    default :
        break;
endswitch;
?>
<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', WPIMMO_PLUGIN_NAME ) ?></em></p></noscript>

<div id="message" class="<?php echo $notice_class; ?> fade" style="<?php echo $notice_style; ?>"><?php echo $message; ?></div>

<div class="wrap">
    <h2>WP Immo</h2>
    <h2 class="nav-tab-wrapper">
        <a class="nav-tab<?php echo ( $current_tab === 'main' ? ' nav-tab-active' : '' ); ?>" href="<?php echo $links['main']; ?>"><?php _e( 'Settings', WPIMMO_PLUGIN_NAME ); ?></a>
        <a class="nav-tab<?php echo ( $current_tab === 'groups' ? ' nav-tab-active' : '' ); ?>" href="<?php echo $links['groups']; ?>"><?php _e( 'Field groups', WPIMMO_PLUGIN_NAME ); ?></a>
        <a class="nav-tab<?php echo ( $current_tab === 'search' ? ' nav-tab-active' : '' ); ?>" href="<?php echo $links['search']; ?>"><?php _e( 'Search boxes', WPIMMO_PLUGIN_NAME ); ?></a>
        <?php if ( $_REQUEST['action'] === 'process' ): ?>
            <span id="wpimmo-process-tab" class="nav-tab nav-tab-active"><?php echo sprintf( __( '%s in progress...', WPIMMO_PLUGIN_NAME ), $current_tab == 'delete' ? __( 'Deletion', WPIMMO_PLUGIN_NAME ) : __( 'Import', WPIMMO_PLUGIN_NAME ) ); ?></span>
        <?php endif; ?>
        <a class="nav-tab<?php echo ( $current_tab === 'tools' ? ' nav-tab-active' : ''  ); ?>" href="<?php echo $links['tools']; ?>"><?php _e( 'Tools', WPIMMO_PLUGIN_NAME ); ?></a>
        <a class="nav-tab<?php echo ( $current_tab === 'help' ? ' nav-tab-active' : ''  ); ?>" href="<?php echo $links['help']; ?>"><?php _e( 'Help', WPIMMO_PLUGIN_NAME ); ?></a>
    </h2>
    <div class="wpimmo-credits alignright"><?php _e( 'WP Immo is a tool developed by <a href="http://www.agence-web-cvmh.fr" target="_blank">CVMH solutions</a>.', WPIMMO_PLUGIN_NAME ); ?></div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder" data-tab="<?php echo $current_tab; ?>">
            <?php include( plugin_dir_path( __FILE__ ) . 'tabs/' . $current_tab . '.php' ); ?>
        </div><!-- #post-body -->
    </div><!-- #poststuff -->
</div><!-- .wrap -->