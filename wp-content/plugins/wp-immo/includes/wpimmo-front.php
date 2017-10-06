<?php
/* 
 * WP Immo Frontend functions
 */

class WPImmo_Front extends WPImmo  {
    
    /**
     * Scripts and styles enqueues
     * 
     * @since 1.0
     */
    public static function enqueues() {
        wp_enqueue_style( 'genericons', plugins_url( '../genericons/genericons.css', __FILE__ ), array(), '3.0.2');
        
        if ( parent::$options['front']['lazy_load'] == 1 ) :
            wp_enqueue_script( 'lazy-load', plugins_url( '../js/jquery.lazyload.min.js', __FILE__ ) );
        endif;
        
        if ( get_post_type() === WPIMMO_POST_TYPE and parent::$options['front']['use_wpi_templates'] == 1 ) :
            wp_enqueue_style( 'elastislide', plugins_url('../css/elastislide.css', __FILE__ ), array(), '20140731');
            wp_enqueue_script( 'modernizr', plugins_url('../js/modernizr.custom.17475.js', __FILE__ ), array('jquery'), '20140731');
            wp_enqueue_script( 'elastislide', plugins_url('../js/jquery.elastislide.js', __FILE__ ), array('jquery'), '20140731');
            wp_enqueue_script( 'swipebox', plugins_url('../js/jquery.swipebox.min.js', __FILE__ ), array('jquery'), '20140731');
            wp_enqueue_style( 'swipebox', plugins_url('../css/swipebox.min.css', __FILE__ ), array(), '20140731');
        endif;
        
        wp_enqueue_style( 'wpimmo', plugins_url( '../css/wpimmo.css', __FILE__ ) );
        wp_enqueue_script( 'wpimmo', plugins_url( '../js/wpimmo.js', __FILE__ ) );
    }
    
    /**
     * Frontend templates override
     * 
     * @since 1.0
     */
    public static function set_template(){
        if( parent::$options['front']['use_wpi_templates'] == 1 ):
            
            // List page
            if ( is_page( parent::$options['front']['list_page_id'] ) ):
                $page_template = get_template_directory() . '/templates/wpimmo-list.php';
            
            // Details page
            elseif ( is_single() and get_post_type() === WPIMMO_POST_TYPE ):
                $page_template = get_template_directory() . '/templates/wpimmo-property.php';
            
            // Taxonomy
            elseif ( is_tax() ):
                $page_template = get_template_directory() . '/templates/wpimmo-taxonomy.php';
            endif;
            
           if( is_file( $page_template ) ):
                add_filter( 'body_class', array( 'WPImmo_Tools', 'add_body_classes' ) );
                include( $page_template );
                die();
            endif;
            
        endif;
    }
        
    /**
     * Override Yoast breadcrumbs
     * 
     * @global type $post
     * @param type $links
     * @return type
     * @since 1.0
     */
    public static function override_yoast_breadcrumb_trail( $links ) {
        global $post;

        if ( is_singular( WPIMMO_POST_TYPE ) ):
            foreach( $links as $key => $link ):
                if( key( $link ) === 'ptarchive' and current( $link ) === WPIMMO_POST_TYPE ):
                    unset( $links[$key] );
                endif;
            endforeach;
            $breadcrumb[] = array(
                'url' => get_permalink( parent::$options['front']['list_page_id'] ),
                'text' => get_the_title( parent::$options['front']['list_page_id'] ),
            );
            unset( $links['ptarchive'] );
            array_splice( $links, 1, -2, $breadcrumb );
        endif;
        
        if ( is_tax()) :
            $breadcrumb[] = array(
                'url' => get_permalink( parent::$options['front']['list_page_id'] ),
                'text' => get_the_title( parent::$options['front']['list_page_id'] ),
            );
            array_splice( $links, 1, -2, $breadcrumb );
        endif;
        
        return $links;
    }
    
    /**
     * Add search to the query
     * 
     * @param type $query
     * @return type
     * @since 1.0
     */
    public static function pre_get_posts( $query ) {
        // validate
        if( is_admin() ):
            return;
        endif;

        if( $query->get( 'post_type' ) !== WPIMMO_POST_TYPE or empty( $_GET['wpimmo_search'] ) ):
            return;
        endif;
        
        $tax_query = array();

        // Search type
        $search_type = parent::$options['front']['template_search_type'];

        // get original meta query
        $meta_query = $query->get( 'meta_query' );

        foreach( $_GET as $get_key => $get_value ):
            if( empty( $get_value ) or $get_key === 'wpi_search' )
                continue;
            if( substr( $get_key, 0, 7 ) === 'wpimin_' ):
                $key_max = substr( $get_key, 7 );
                if( empty( $_GET[$key_max] ) ):
                    $meta_query[] = array(
                        'key' => 'wpimmo_' . $key_max,
                        'value' => array( intval( $get_value ), 999999999 ),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    );
                else:
                    continue;
                endif;
            endif;
            switch( parent::$search[$search_type][$get_key]['type'] ):
                case 'text':
                    $meta_query[] = array(
                        'key' => 'wpimmo_' . $get_key,
                        'value' => addslashes( urldecode( $get_value ) ),
                        'compare' => 'LIKE',
                    );
                    break;
                case 'min':
                    $meta_query[] = array(
                        'key' => 'wpimmo_' . $get_key,
                        'value' => addslashes( urldecode( $get_value ) ),
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    );
                    break;
                case 'max':
                    $meta_query[] = array(
                        'key' => 'wpimmo_' . $get_key,
                        'value' => addslashes( urldecode( $get_value ) ),
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    );
                    break;
                case 'interval':
                    if( empty( $_GET['wpimin_' . $get_key] ) ):
                        $get_value_min = 0;
                    else:
                        $get_value_min = intval( $_GET['wpimin_'.$get_key] );
                    endif;
                    $meta_query[] = array(
                        'key' => 'wpimmo_' . $get_key,
                        'value' => array( $get_value_min, intval( $_GET[$get_key] ) ),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    );
                    break;
                case 'select':
                    if( parent::$fields[$get_key]['type'] === 'taxonomy' ):
                        $tax_query[] = array(
                            'taxonomy' => parent::$fields[$get_key]['taxonomy'],
                            'field' => 'name',
                            'terms' => urldecode( $get_value ),
                        );
                    else:
                        $meta_query[] = array(
                            'key' => 'wpimmo_' . $get_key,
                            'value' => html_entity_decode( urldecode( $get_value ) ),
                            'compare' => 'IN',
                        );
                    endif;
                    break;
                default:
                    break;
            endswitch;
        endforeach;
        
        if( !empty( $tax_query ) ):
            $query->set('tax_query', $tax_query );
        endif;
        if( !empty( $meta_query ) ):
            $query->set('meta_query', $meta_query );
        endif;
        
        // always return
        return;
    }
    
    /**
     * Displays energy diagrams
     * 
     * @param type $post_id
     * @return string
     * @since 1.0
     */
    public static function get_energy_diagrams( $post_id ) {
        if ( empty( parent::$platform['dpe_key'] ) and empty( parent::$platform['ges_key'] ) ) :
            return;
        endif;
        
        $output = '';
        $dpe = WPImmo_tools::get_field( intval( $post_id ), parent::$platform['dpe_key'] );
        $ges = WPImmo_tools::get_field( intval( $post_id ), parent::$platform['ges_key'] );
        if( $dpe or $ges ):
            $output .= '<div class="wpimmo-wrapper-diagnostic">
                            <h2>' . __( 'Diagnosis of energy performance', WPIMMO_PLUGIN_NAME ) . '</h2>
                                <div>';
            if( $dpe ):
                $output .= '<ul id="wpimmo-dpe" data-value="' . $dpe . '">
                                <li>' . __( 'Economic residence', WPIMMO_PLUGIN_NAME ) . '</li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-a"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-b"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-c"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-d"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-e"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-f"></span></strong></li>
                                <li><strong class="wpimmo-dpe"><span class="wpimmo-dpe-g"></span></strong></li>
                                <li>' . __( 'Inefficient housing', WPIMMO_PLUGIN_NAME ) . '</li>
                            </ul>';
                endif;
                if( $ges ):
                    $output .= '<ul id="wpimmo-ges" data-value="' . $ges . '">
                                    <li>' . __( 'Low-emission', WPIMMO_PLUGIN_NAME ) . '</li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-a"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-b"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-c"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-d"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-e"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-f"></span></strong></li>
                                    <li><strong class="wpimmo-ges"><span class="wpimmo-ges-g"></span></strong></li>
                                    <li>' . __( 'High gas emission', WPIMMO_PLUGIN_NAME ) . '</li>
                                </ul>';
                endif;
                $output .= '</div>
                        </div>';
        endif;
        return $output;
    }
    
    /**
     * Display AddThis buttons
     * 
     * @return string
     * @since 1.0
     */
    public static function get_addthis_buttons() {
        $output = '';
        if ( parent::$options['addthis']['active'] == 1 and ! empty( parent::$options['addthis']['items'] ) ) :
            $output .= '<div class="alignleft addthis_label">' . __( 'Share:', WPIMMO_PLUGIN_NAME ) . '&nbsp;</div>
                        <div class="alignleft addthis_toolbox addthis_default_style ' . parent::$options['addthis']['size'] . '">';
            foreach ( parent::$options['addthis']['items'] as $key ) :
                $output .= '<a class="' . parent::$addthis[$key]['class'] .'"' . ( ( parent::$addthis[$key]['new_window'] == 1 ) ? ' target="_blank"' : '' ) . '></a>';
            endforeach;
            $output .= '</div>
                        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js' . ( ( ! empty( parent::$options['addthis']['pubid'] ) ) ? '#pubid='.parent::$options['addthis']['pubid'] : '' ) . '"></script>';
        endif;
        return $output;
    }
    
    /**
     * Override meta title
     * 
     * @param type $title
     */
    public static function set_title( $title ) {
        if ( is_single() and get_post_type() == WPIMMO_POST_TYPE and ! empty ( parent::$options['seo']['title'] ) ) :
            global $post;
            $args = array(
                'source' => 'wp',
                'mask' => parent::$options['seo']['title'],
                'post_id' => $post->ID,
            );
            $title = WPImmo_Tools::fields_replaced_string( $args );
        elseif ( ( is_page( parent::$options['front']['list_page_id'] ) or is_post_type_archive( WPIMMO_POST_TYPE ) ) and ! empty ( parent::$options['seo']['list_title'] ) ) :
            $title = parent::$options['seo']['list_title'];
        endif;
        return $title;
    }
    
    /**
     * Query for properties
     * 
     * @return \WP_Query
     * @since 1.0
     */
    public static function get_items( $params ) {
        $args = array(
            'post_type'   => WPIMMO_POST_TYPE,
            'post_status' => 'publish',
        );
        if ( parent::$options['front']['pagination'] == 1 ) :
            $args['posts_per_page'] = parent::$options['front']['items_per_page'];
            $args['paged'] = get_query_var('paged') ? intval( get_query_var('paged') ) : 1;
        else :
            $args['nopaging'] = true;
        endif;
        if ( !empty( $params ) ) :
            $args = array_merge( $args, $params );
        endif;
        $query = new WP_Query( $args );
        return $query;
    }
    
    /**
     * Build a list (<ul>) with properties
     * 
     * @param $r
     * @return html
     * @since 1.0
     */
    public static function get_list( $params ) {
        $r = self::get_items( $params );
        $html = '';
        if ( $r->have_posts() ) : $counter = 0;
            $counter = 0;
            //$html .= '<h2>' . __( 'Properties matching your search', WPIMMO_PLUGIN_NAME ) . '</h2>';
            $html .= '<ul class="wpimmo-list clearfix">';
            while ( $r->have_posts() ) :
                $counter++;
                $r->the_post();
                $images = wpimmo_get_field( get_the_ID(), 'images' );
                if ( ! empty( WPImmo::$options['front']['field_pastille'] ) ) :
                    $pastille = wpimmo_get_field( get_the_ID(), WPImmo::$options['front']['field_pastille'] );
                endif;
                $html .= '<li>';
                if( ! empty( WPImmo::$options['front']['list_pastille'] ) and $pastille === 'oui' ) :
                    $html .= '<div class="wpimmo_pastille">' . WPImmo::$options['front']['list_pastille'] . '</div>';
                endif;
                $html .= '<a href="' . get_the_permalink() . '" class="wpimmo_rollover animated">' . __( 'Discover', WPIMMO_PLUGIN_NAME ) . '</a>';
                $html .= '<a href="' . get_the_permalink() . '">';
                if( !empty( $images ) ):
                    $image = wp_get_attachment_image_src( $images[0], 'wpi_list' );
                    if ( $counter < 7 or WPImmo::$options['front']['lazy_load'] != 1 ) :
                        $html .= '<img src="' . $image[0] .'" width="' . $image[1] . '" height="' . $image[2] . '" />';
                    else :
                        $html .= '<img class="lazy" data-original="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" />';
                    endif;
                else:
                    if ( $counter < 7 or WPImmo::$options['front']['lazy_load'] != 1 ) :
                        $html .= '<img class="wpimmo-no-photo" src="' . plugin_dir_url( __FILE__ ) . '../images/no-photo.png" width="' . WPImmo::$options['images']['wpi_list']['width'] . '" height="' . WPImmo::$options['images']['wpi_list']['height'] . '" />';
                    else :
                        $html .= '<img class="lazy wpimmo-no-photo" data-original="' . plugin_dir_url( __FILE__ ) . '../images/no-photo.png" width="' . WPImmo::$options['images']['wpi_list']['width'] . '" height="' . WPImmo::$options['images']['wpi_list']['height'] . '" />';
                    endif;
                endif;
                $html .= '</a><br />';
                $html .= WPImmo_Tools::fields_replaced_string( array(
                    'source' => 'wp',
                    'post_id' => get_the_ID(),
                    'mask' => WPImmo::$options['front']['list_title'],
                    'linked' => true,
                    'truncate' => 9,
                ) );
                $html .= '</li>';
            endwhile;
            $html .= '</ul>';
            $html .= self::paging_nav( $r );
        else:
            $html .= '<p class="wpimmo-no-results">';
            if ( ! empty( WPImmo::$options['front']['contact_page_id'] ) ):
                $html .= sprintf( __( 'There is no result for your search but we have perhaps the property of your dreams. <a href="%s">Please click here to contact us!</a>', WPIMMO_PLUGIN_NAME ), get_permalink( WPImmo::$options['front']['contact_page_id'] ) );
            else:
            $html .= __( 'There is no result for your search.', WPIMMO_PLUGIN_NAME );
            endif;
            $html .= '<p>';
        endif;
        return $html;
    }
    
    /**
     * Display navigation when applicable.
     *
     * @param type $query
     * @return type
     * @since 1.0
     */
    public static function paging_nav( $query ) {
        // Don't print empty markup if there's only one page.
        if ( $query->max_num_pages < 2 ) :
            return;
        endif;

        $paged        = $query->query['paged'] ? intval( $query->query['paged']) : 1;
        $pagenum_link = html_entity_decode( get_pagenum_link() );
        $query_args   = array();
        $url_parts    = explode( '?', $pagenum_link );

        if ( isset( $url_parts[1] ) ) {
            wp_parse_str( $url_parts[1], $query_args );
        }

        $pagenum_link = esc_url( remove_query_arg( array_keys( $query_args ), $pagenum_link ) );
        $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

        $format = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
        $format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

        // Set up paginated links.
        $links = paginate_links( array(
            'base'      => $pagenum_link,
            'format'    => $format,
            'total'     => $query->max_num_pages,
            'current'   => $paged,
            'mid_size'  => 1,
            'add_args'  => WPImmo_Tools::array_map_recursive( 'urlencode', $query_args ),
            'prev_next' => false,
        ) );

        if ( $links ) :
            return '<nav class="wpimmo-paging-navigation" role="navigation">' .
                    '<div class="wpimmo-pagination">' .
                        $links .
                    '</div>' .
                '</nav>';
        endif;
    }
        
    /**
     * Return group object
     * 
     * @param type $id
     * @return type
     */
    public static function get_group( $id ){
        foreach( parent::$groups as $group ) :
            if ( $group['id'] == $id ) :
                return $group;
            endif;
        endforeach;
    }

    /**
     * Add opengraph to doctype
     * @param type $output
     * @return type
     */
    public static function add_opengraph_doctype( $output ) {
        return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
    }
    
    /**
     * Add open graph meta in head
     * 
     * @global type $post
     * @return type
     */
    function insert_fb_in_head() {
        global $post;
        if ( ! is_singular() ) //if it is not a post or a page
            return;
        echo '<meta property="og:title" content="' . $post->post_title . '"/>';
        echo '<meta property="og:description" content="' . wp_strip_all_tags( $post->post_content ) . '"/>';
        echo '<meta property="og:type" content="article"/>';
        echo '<meta property="og:url" content="' . $post->guid . '"/>';
        echo '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '"/>';
        $images = wpimmo_get_field( get_the_ID(), 'images' );
        if ( ! empty( $images ) ) :
            $image = wp_get_attachment_image_src( $images[0], 'wpi_detail' );
            echo '<meta property="og:image" content="' . esc_attr( $image[0] ) . '"/>';
        else :
            echo '<meta property="og:image" content="' . plugin_dir_url( __FILE__ ) . '../../images/no-photo.png"/>';

        endif;
    }
    
}