<?php
/* 
 * WP Immo CRON functions
 */

class WPImmo_Cron extends WPImmo  {
    
    /**
     * 
     * Cron setup
     * 
     * @since 1.0
     */
    public static function setup() {
        if ( ! wp_next_scheduled( 'wpimmo_cron' ) ):
            if ( parent::$options['cron']['active'] == 1 ):
                if ( parent::$options['cron']['hour'] < date( 'G' ) ) :
                    $time = mktime( intval( parent::$options['cron']['hour'] ) - 1, 0, 0, date( 'm' ), date( 'd' ) + 1, date( 'Y' ) );
                else :
                    $time = mktime( intval( parent::$options['cron']['hour'] ) - 1, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
                endif;
                wp_schedule_event( $time, parent::$options['cron']['recurrence'], 'wpimmo_cron' );
            else:
                wp_clear_scheduled_hook( 'wpimmo_cron' );
            endif;
        endif;
    }
    
    /**
     * 
     * Cron process
     * 
     * @since 1.0
     */
    public static function process() {
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $process_tab = WPImmo_Process::prepare_import();
        $tab_ids = array_keys( $process_tab['process'] );
        $remain = count( $tab_ids );
        foreach( $tab_ids as $id ):
            $remain--;
            $report = WPImmo_Process::import( $id, $remain, $process_tab['existing'], $process_tab['delete'], $process_tab['url'], $report['added'], $report['updated'] );
        endforeach;
        //email
        if ( parent::$options['cron']['report'] == 1 and !empty( parent::$options['cron']['email'] ) ):
            if ( empty( $report['added'] ) ) :
                $report['added'] = 0;
            endif;
            if ( empty( $report['updated'] ) ) :
                $report['updated'] = 0;
            endif;
            if ( empty( $report['deleted'] ) ) :
                $report['deleted'] = 0;
            endif;
            $message = 
                __( "Hello !", WPIMMO_PLUGIN_NAME ) . "\r\n\r\n" . 
                __( "Your WP Immo cron has just finished.", WPIMMO_PLUGIN_NAME ) . "\r\n\r\n" . 
                __( "Here's what happened:", WPIMMO_PLUGIN_NAME ) . "\r\n" . 
                sprintf( __( "Added items: %s", WPIMMO_PLUGIN_NAME ), $report['added'] ) . "\r\n" . 
                sprintf( __( "Updated items: %s", WPIMMO_PLUGIN_NAME ), $report['updated'] ) . "\r\n" . 
                sprintf( __( "Deleted items: %s", WPIMMO_PLUGIN_NAME ), $report['deleted'] ) . "\r\n\r\n" . 
                __( 'WP Immo team', WPIMMO_PLUGIN_NAME );
            wp_mail( parent::$options['cron']['email'], __( 'WP Immo cron report', 'wp_immo' ), $message);
        endif;
    }
    
}