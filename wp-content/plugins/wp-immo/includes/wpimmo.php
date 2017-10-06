<?php
/* 
 * WP Immo class
 */

if ( ! class_exists( 'WPImmo' ) ) :
    class WPImmo {

        public static $labels = array();
        public static $options = array();
        public static $platform = array();
        public static $fields = array();
        public static $all_fields = array();
        public static $taxonomies = array();
        public static $all_taxonomies = array();
        public static $addthis = array();
        public static $search = array(
            'default_title' => '',
            'basic' => array(),
            'full'  => array(),
        );
        public static $groups = array();

        /**
         * Plugin initialisation
         * 
         * @since 1.0
         */
        public static function init() {
            // Include required classes
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-process.php' );
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-tools.php' );
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-admin.php' );
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-cron.php' );
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-front.php' );

            // Register text domain
            load_plugin_textdomain( WPIMMO_PLUGIN_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );

            // Enqueue the needed Javascript and CSS.
            add_action( 'admin_enqueue_scripts', array( 'WPImmo_Admin', 'enqueues' ) );
            add_action( 'wp_enqueue_scripts', array( 'WPImmo_Front', 'enqueues' ) );  

            // Ajax for import
            add_action( 'wp_ajax_wpimmo_import', array( 'WPImmo_Process', 'import' ) );
            add_action( 'wp_ajax_wpimmo_delete', array( 'WPImmo_Process', 'delete' ) );

            // Set datas
            require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-data.php' );
            WPImmo_Data::set_data();

            // Register custom post type
            register_post_type( WPIMMO_POST_TYPE, array(
                'public'          => true,
                'has_archive'     => true,
                'labels'          => self::$labels['custom_post'],
                'capability_type' => 'post',
                'show_in_menu'    => self::$options['admin']['show_in_menu']==1 ? true : false,
                'menu_icon'       => 'dashicons-admin-home',
                'supports'        => array( 'title', 'editor' ),
            ) );

            // Remove Custom post type slug from permalink
            add_filter( 'post_type_link', array( 'WPImmo_Tools', 'remove_cpt_slug' ), 0, 3 ); 
            add_action( 'pre_get_posts', array( 'WPImmo_Tools', 'parse_request_trick' ) );         

            // Register image sizes
            foreach( self::$options['images'] as $name => $data):
                add_image_size( $name, $data['width'], $data['height'], $data['crop'] );        
            endforeach;

            // Get platform datas
            self::set_platform_data();
            
            // Active fields
            if( empty( self::$options['active_fields'] ) ):
                foreach( self::$fields as $key => $tab ):
                    if( $tab['active'] == 1 ):
                        self::$options['active_fields'][] = $key;
                    endif;
                endforeach;
            endif;
            foreach( self::$fields as $key => $tab ):
                if( !in_array( $key, self::$options['active_fields'] ) ):
                    unset( self::$fields[$key] );
                endif;
            endforeach;

            // Active taxonomies
            if( empty( self::$options['active_taxonomies'] ) ):
                foreach( self::$taxonomies as $key => $tab ):
                    if( $tab['active'] == 1 ):
                        self::$options['active_taxonomies'][] = $key;
                    endif;
                endforeach;
            endif;
            foreach( self::$taxonomies as $key => $tab ):
                if( !in_array( $key, self::$options['active_taxonomies'] ) ):
                    unset( self::$taxonomies[$key] );
                endif;
            endforeach;

            // Register taxonomies
            foreach( self::$taxonomies as $tax_name => $tax_tab ):
                register_taxonomy( $tax_name, WPIMMO_POST_TYPE, array( 'show_in_menu' => self::$options['admin']['show_tax_in_menu']==1 ? true : false, 'labels' => $tax_tab['labels'], 'rewrite' => $tax_tab['rewrite'] ) );
                if( self::$options['admin']['show_in_menu']==1 and !empty( $tax_tab['terms'] ) ):
                    foreach( $tax_tab['terms'] as $term ):
                        wp_insert_term( $term, $tax_name );
                    endforeach;
                endif;
            endforeach;

            // ADD JavaScript data
            add_action( 'admin_head', array( 'WPImmo_Admin', 'admin_head' ) );

            // Custom columns in custom post admin
            if( self::$options['admin']['show_in_menu']==1 ):
                add_filter( 'manage_edit-' . WPIMMO_POST_TYPE . '_columns' , array( 'WPImmo_Admin', 'prepare_columns' ) );
                add_action( 'manage_posts_custom_column', array( 'WPImmo_Admin', 'display_columns' ) );
                add_action( 'restrict_manage_posts', array( 'WPImmo_Admin', 'restrict_by_taxonomy' ) );
                add_filter( 'parse_query', array( 'WPImmo_Tools', 'convert_id_to_term_in_query' ) );
                add_action( 'save_post', array( 'WPImmo_Process', 'save_informations' ), 10, 2 );
                add_action( 'save_post', array( 'WPImmo_Process', 'save_images' ), 20, 2 );
                add_filter( 'posts_join', array( 'WPImmo_Admin', 'search_join' ) );
                add_filter( 'posts_where', array( 'WPImmo_Admin', 'search_where' ) );
            endif;

            // Add settings page in admin settings menu and in extension list
            add_action( 'admin_menu', array( 'WPImmo_Admin', 'menu' ) );
            add_filter( 'plugin_action_links_' . WPIMMO_PLUGIN_NAME . '/' . WPIMMO_PLUGIN_NAME . '.php', array( 'WPImmo_Admin', 'add_action_links' ) );

            // Cron setup
            add_action( 'wp', array( 'WPImmo_Cron', 'setup' ) );
            add_action( 'wpimmo_cron', array( 'WPImmo_Cron', 'process' ) );

            // Frontend template list page
            if ( self::$options['front']['use_wpi_templates'] == 1 ) :
                // Copy template files
                if ( ! is_dir( get_template_directory() . '/templates/' ) ) :
                    mkdir( get_template_directory() . '/templates/' , 0755 );
                endif;
                if ( ! is_file( get_template_directory() . '/templates/wpimmo-list.php' ) ) :
                    copy( dirname( __FILE__ ) . '/../templates/front/list.php', get_template_directory() . '/templates/wpimmo-list.php' );
                endif;
                if ( ! is_file( get_template_directory() . '/templates/wpimmo-property.php' ) ) :
                    copy( dirname( __FILE__ ) . '/../templates/front/property.php', get_template_directory() . '/templates/wpimmo-property.php' );
                endif;
                if ( ! is_file( get_template_directory() . '/templates/wpimmo-taxonomy.php' ) ) :
                    copy( dirname( __FILE__ ) . '/../templates/front/taxonomy.php', get_template_directory() . '/templates/wpimmo-taxonomy.php' );
                endif;
                
                // Create frontend list page
                if( empty( self::$options['front']['list_page_id'] ) ):
                    self::$options['front']['list_page_id'] = wp_insert_post(
                        array(
                            'post_name'   => empty( self::$options['seo']['list_permalink'] ) ? __( 'properties', WPIMMO_PLUGIN_NAME ) : self::$options['seo']['list_permalink'],
                            'post_title'  => empty( self::$options['seo']['list_title'] ) ? __( 'Our properties', WPIMMO_PLUGIN_NAME ) : self::$options['seo']['list_title'],
                            'post_status' => 'publish',
                            'post_type'   => 'page',
                        )
                    );
                    WPImmo_Process::save_options( self::$options );

                // Test if permalink and h1 have to be updated
                else:
                    $list_page = get_post( self::$options['front']['list_page_id'] );
                    $do_update = false;
                    $tab_update = array( 'ID' => self::$options['front']['list_page_id'] );
                    if( $list_page->post_name !== self::$options['seo']['list_permalink'] ):
                        $tab_update['post_name'] = self::$options['seo']['list_permalink'];
                        $do_update = true;
                    endif;
                    if( $list_page->post_title !== self::$options['seo']['list_h1'] ):
                        $tab_update['post_title'] = self::$options['seo']['list_h1'];
                        $do_update = true;
                    endif;
                    if( $do_update ):
                        wp_update_post( $tab_update );
                    endif;
                endif;
            endif;

            // Override meta title
            add_filter( 'wp_title', array( 'WPImmo_Front', 'set_title' ), 100 );

            // Override list page template
            add_action('template_redirect', array( 'WPImmo_Front', 'set_template' ) );

            // Override Yoast breadcrumbs
            add_filter( 'wpseo_breadcrumb_links', array( 'WPImmo_Front', 'override_yoast_breadcrumb_trail' ) );

            // Frontend search process
            add_action('pre_get_posts', array( 'WPImmo_Front', 'pre_get_posts' ) );
            
            // Add opengraph
            add_filter('language_attributes', array( 'WPImmo_Front', 'add_opengraph_doctype' ) );
            add_action( 'wp_head', array( 'WPImmo_Front', 'insert_fb_in_head' ), 5 );
        }
        
        /**
         * 
         * Set platform data
         * 
         * @since 1.0
         */
        public static function set_platform_data() {
            require_once( plugin_dir_path( __FILE__ ) . '../default-data.php' );
            WPImmo_Default::set_data();
            do_action( 'wpimmo_set_platform_data' );
        }

        /**
         * Deactivate plugin
         * 
         * @since 1.0
         */
        public static function deactivation() {
            wp_clear_scheduled_hook( 'wpimmo_cron' );
            flush_rewrite_rules();
        }

        /**
         * 
         * Uninstall plugin
         * 
         * @since 1.0
         */
        public static function uninstall() {
            set_time_limit(0);
            $wpimmo_options = json_decode( get_option('wpimmo'), true );
            if( $wpimmo_options['deactivation_delete'] ):
                global $wpdb;
                require_once( plugin_dir_path( __FILE__ ) . 'wpimmo-process.php' );
                $taxonomies = array();
                foreach( self::$taxonomies as $taxonomy => $tab_taxonomy ):
                    $taxonomies[] = $taxonomy;
                endforeach;

                // Delete custom posts and attachments
                $query = new WP_Query( array( 'post_type' => WPIMMO_POST_TYPE, 'nopaging' => true ) );
                while( $query->have_posts() ):
                    $query->the_post();
                    $post_id = get_the_ID();
                    $attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment' ) );
                    foreach( $attachments as $attachment ):
                        wp_delete_attachment( $attachment->ID );
                    endforeach;
                    wp_delete_object_term_relationships( $post_id, $taxonomies );
                    wp_delete_post( $post_id, true );
                endwhile;

                // Delete taxonomies
                foreach( $taxonomies as $taxonomy ):
                    $wpdb->get_results( $wpdb->prepare( "DELETE t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s')", $taxonomy ) );
                    // Delete Taxonomy
                    $wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
                endforeach; 

                // Delete template page
                WPImmo_Process::delete( $wpimmo_options['front']['list_page_id'] );

                // Delete WP Immo options
                delete_option( 'wpimmo' );
                delete_option( 'wpimmo_custom_data' );
                delete_option( 'wpimmo_groups' );
                delete_option( 'wpimmo_search' );

                // Remove wp-immo dir in uploads dir
                $upload_dir = wp_upload_dir();
                if( is_dir( $upload_dir['basedir'].'/wp-immo/' ) ):
                    rmdir( $upload_dir['basedir'].'/wp-immo/' );
                endif;
                
                // Remove wp-immo tempaltes
                unlink( get_template_directory() . '/templates/wpimmo-list.php' );
                unlink( get_template_directory() . '/templates/wpimmo-property.php' );
                unlink( get_template_directory() . '/templates/wpimmo-taxonomy.php' );

                // Optimize tables
                $wpdb->query( "OPTIMIZE TABLE `{$wpdb->prefix}options`, `{$wpdb->prefix}postmeta`, `{$wpdb->prefix}posts`, `{$wpdb->prefix}terms`, `{$wpdb->prefix}term_taxonomy`, `{$wpdb->prefix}term_relationships`" );
            endif;
        }

    }

    /**
     * Functions declaration to simplify templating
     * 
     */
    function wpimmo_get_items() {
        return WPImmo_Front::get_items();
    }
    function wpimmo_list( $params = null ) {
        echo WPImmo_Front::get_list( $params );
    }
    function wpimmo_get_field( $post_id, $key ) {
        return WPImmo_Tools::get_field( $post_id, $key );
    }
    function wpimmo_get_energy_diagrams( $post_id ) {
        echo WPImmo_Front::get_energy_diagrams( $post_id );
    }
    function wpimmo_get_addthis_buttons() {
        echo WPImmo_Front::get_addthis_buttons();
    }
    function wpimmo_get_custom_data() {
        return WPImmo_Tools::get_custom_data();
    }
    function wpimmo_paging_nav( $query ) {
        echo WPImmo_Front::paging_nav( $query );
    }
    function wpimmo_get_paging_nav( $query ) {
        return WPImmo_Front::paging_nav( $query );
    }
endif;