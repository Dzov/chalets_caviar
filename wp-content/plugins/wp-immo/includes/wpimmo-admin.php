<?php
/* 
 * WP Immo admin functions
 */

class WPImmo_Admin extends WPImmo  {
    
    /**
     * Adds data in JavaScript
     * 
     * @since 1.0
     */
    public static function admin_head() {
        // l10n
        $l10n = array(
            'saving'             => __( 'Saving', WPIMMO_PLUGIN_NAME ),
            'deleting'           => __( 'Deleting', WPIMMO_PLUGIN_NAME ),
            'stopping'           => __( "Stopping...", WPIMMO_PLUGIN_NAME ),
            'import_completed'   => __( 'Import completed', WPIMMO_PLUGIN_NAME ),
            'deletion_completed' => __( 'Deletion completed', WPIMMO_PLUGIN_NAME ),
            'import_error'       => __( 'The import process was abnormally terminated. This is likely due to the file exceeding available memory or some other type of fatal error.', WPIMMO_PLUGIN_NAME ),
            'delete_error'       => __( 'The delete process was abnormally terminated. This is likely due to the file exceeding available memory or some other type of fatal error.', WPIMMO_PLUGIN_NAME ),
            'select'             => __( "Select images", WPIMMO_PLUGIN_NAME ),
            'use-selection'      => __( "Use this selection", WPIMMO_PLUGIN_NAME ),
            'delete'             => __( "Delete", WPIMMO_PLUGIN_NAME ),
            'edit'               => __( "Edit", WPIMMO_PLUGIN_NAME ),
            'text'               => __( "text", WPIMMO_PLUGIN_NAME ),
            'select'             => __( "select", WPIMMO_PLUGIN_NAME ),
            'interval'           => __( "interval", WPIMMO_PLUGIN_NAME ),
            'submit'             => __( "submit", WPIMMO_PLUGIN_NAME ),
        );
        ?>
        <script type="text/javascript">
            (function($) {
                wpimmo.ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                wpimmo.l10n = <?php echo json_encode( $l10n ); ?>;
                wpimmo.fields = <?php echo json_encode( parent::$fields ); ?>;
                wpimmo.taxonomies = <?php echo json_encode( parent::$taxonomies ); ?>;
            })(jQuery);	
        </script>
        <?php
    }
    
    /**
     * Scripts and styles enqueues
     * 
     * @since 1.0
     */
    public static function enqueues() {
        if( wp_script_is( 'jquery-ui-widget', 'registered' ) ):
            wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( '../js/jquery-ui/jquery.ui.progressbar.min.js', __FILE__ ), array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
        else:
            wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( '../js/jquery-ui/jquery.ui.progressbar.min.1.7.2.js', __FILE__ ), array( 'jquery-ui-core' ), '1.7.2' );
        endif;
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'wpimmo-admin', plugins_url( '../js/wpimmo-admin.js', __FILE__ ) );
        
        wp_enqueue_style( 'jquery-ui-wpimmo', plugins_url( '../js/jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' );
        wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'genericons', plugins_url( '../genericons/genericons.css', __FILE__ ), array(), '3.0.2');
        wp_enqueue_style( WPIMMO_PLUGIN_NAME, plugins_url( '../css/wpimmo.css', __FILE__ ) );
    }
    
    /**
     * Admin menu
     * 
     * @since 1.0
     */
    public static function menu() {
        add_options_page( 'WP Immo', 'WP Immo', 'manage_options', WPIMMO_PLUGIN_NAME, array( get_called_class(), 'settings_page' ) );
        if( parent::$options['admin']['show_in_menu']==1 ):
            foreach( parent::$taxonomies as $tax_name => $tax_tab ):
                remove_meta_box( 'tagsdiv-'.$tax_name, WPIMMO_POST_TYPE, 'normal' );
            endforeach;
            add_meta_box( 'wpimmo_informations', __( 'Property informations', WPIMMO_PLUGIN_NAME ), array( get_called_class(), 'metabox_informations' ), WPIMMO_POST_TYPE, 'normal', 'high' );
            add_meta_box( 'wpimmo_images', __( 'Images', WPIMMO_PLUGIN_NAME ), array( get_called_class(), 'metabox_images' ), WPIMMO_POST_TYPE, 'normal', 'high' );
        endif;
    }
    
    /**
     * Admin settings page
     * 
     * @since 1.0
     */
    public static function settings_page() {
        require_once( plugin_dir_path( __FILE__ ) . '../templates/admin/settings.php' );
    }
    
    /**
     * Get the current tab for admin settings page
     * 
     * @return string
     * @since 1.0
     */
    public static function get_current_tab() {
        if (isset($_GET['tab'])):
            return esc_html($_GET['tab']);
        else:
            return 'main';
        endif;
    }
    
    /**
     * Prepare custom post table columns
     * 
     * @param type $columns
     * @return type
     */
    public static function prepare_columns( $columns ) {
        $wpimmo_columns = array(
            'thumb' => __( 'Image', WPIMMO_PLUGIN_NAME ),
            'ref' => __( 'Reference', WPIMMO_PLUGIN_NAME ),
        );
        foreach( parent::$options['admin']['table_taxonomies'] as $tax ):
            $wpimmo_columns[$tax] = parent::$taxonomies[$tax]['labels']['singular_name'];
        endforeach;
        $columns = array_slice( $columns, 0, 1 ) + $wpimmo_columns + array_slice( $columns, 1, null );
        return $columns;
    }
        
    /**
     * Display custom columns in admin properties table
     * 
     * @global type $post
     * @param type $name
     */
    public static function display_columns( $name ) {
        global $post;
        switch ( $name ):
            case 'thumb':
                $images = json_decode( get_post_meta( get_the_ID(), 'wpimmo_images', true ) );
                if( !empty( $images ) ): 
                    $image = wp_get_attachment_image( $images[0], 'wpi_thumb' );
                    echo '<a href="post.php?post=' . get_the_ID() . '&amp;action=edit">' . $image . '</a>';
                else:
                    echo '<a href="post.php?post=' . get_the_ID() . '&amp;action=edit"><img src="' . plugin_dir_path( __FILE__ ) . '../images/no-photo.png" width="' . parent::$options['images']['wpi_thumb']['width'] . '" height="' . parent::$options['images']['wpi_thumb']['height'] .'" /></a>';
                endif;
            break;
            case 'ref':
                $reference = get_post_meta( get_the_ID(), 'wpimmo_' . parent::$platform['unicity_key'], true );
                if(!empty($reference)):
                    echo '<a href="post.php?post=' . get_the_ID() . '&amp;action=edit">' . $reference .'</a>';
                else:
                    echo '-';
                endif;
            break;
            default:
                foreach( parent::$options['admin']['table_taxonomies'] as $tax ):
                    if( $name == $tax):
                        $tax_value = wp_get_post_terms($post->ID, $tax);
                        echo $tax_value[0]->name;
                    endif;
                endforeach;
            break;
        endswitch;
    }

    /**
     * Taxonomies filters on custom post table
     * 
     * @global type $typenow
     */
    public static function restrict_by_taxonomy() {
        global $typenow;
        if ( $typenow == WPIMMO_POST_TYPE ) {
            foreach( parent::$options['admin']['table_taxonomies'] as $tax ):
                $selected = isset( $_GET[$tax] ) ? $_GET[$tax] : '';
                wp_dropdown_categories( array(
                    'show_option_all' => parent::$taxonomies[$tax]['labels']['all_items'],
                    'taxonomy' => $tax,
                    'name' => $tax,
                    'orderby' => 'name',
                    'selected' => $selected,
                    'hide_empty' => false,
                ) );
            endforeach;
        }
    }
    
    /**
     * Informations metabox
     * 
     * @param type $object
     */
    public static function metabox_informations( $object ){
        require_once( plugin_dir_path( __FILE__ ) . '../templates/admin/metaboxes/informations.php' );
    }
    
    /**
     * Images metabox
     * 
     * @since 1.0
     */
    public static function metabox_images() {
        require_once( plugin_dir_path( __FILE__ ) . '../templates/admin/metaboxes/images.php' );
    } 
    
    /**
     * Adds join in search query
     * 
     * @global type $pagenow
     * @global type $wpdb
     * @param string $join
     * @return string
     * @since 1.0
     */
    public static function search_join( $join ) {
        global $pagenow, $wpdb;
        if ( is_admin() and $pagenow == 'edit.php' and $_GET['post_type'] == WPIMMO_POST_TYPE and $_GET['s'] != '' and ! empty( parent::$platform['unicity_key'] ) ) :
            $join .='LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        endif;
        return $join;
    }

    //
    /**
     * Add filter to search query
     * 
     * @global type $pagenow
     * @global type $wpdb
     * @param type $where
     * @return type
     * @since 1.0
     */
    public static function search_where( $where ){
        global $pagenow, $wpdb;
        if ( is_admin() and $pagenow == 'edit.php' and $_GET['post_type'] == WPIMMO_POST_TYPE and $_GET['s'] != '' and ! empty( parent::$platform['unicity_key'] ) ) :
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
        endif;
        return $where;
    }
    
    /**
     * Settings link in extension list
     * 
     * @param type $links
     * @return type
     */
    public static function add_action_links( $links ) {
        array_unshift(
            $links,
            '<a href="' . admin_url( 'admin.php?page=' . WPIMMO_PLUGIN_NAME ) . '">' . __( 'Settings', WPIMMO_PLUGIN_NAME ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=' . WPIMMO_PLUGIN_NAME ) . '&tab=tools">' . __( 'Tools', WPIMMO_PLUGIN_NAME ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=' . WPIMMO_PLUGIN_NAME ) . '&tab=help">' . __( 'Help', WPIMMO_PLUGIN_NAME ) . '</a>'
        );
        return $links;
    }
      
}