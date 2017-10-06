<?php
/**
 * WP Immo Last Properties Widget
 * 
 * @since 1.0
 */
class WPImmo_Widget_Last_Properties extends WP_Widget {
    
    public static function register() {
        register_widget( __CLASS__ );
    }

    public function __construct() {
        $widget_ops = array('classname' => 'widget_wpimmo_last_properties', 'description' => __( 'The last WP Immo properties.', WPIMMO_PLUGIN_NAME ) );
        parent::__construct( 'widget_wpimmo_last_properties', __( 'WP Immo - Last properties', WPIMMO_PLUGIN_NAME ), $widget_ops );
    }
    
    public function flush_widget_cache() {
            wp_cache_delete('widget_wpimmo_last_properties', 'widget');
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['number'] = absint( $new_instance['number'] );
        $instance['condition'] = strip_tags( $new_instance['condition'] );
        $this->flush_widget_cache();

        return $instance;
    }

    public function form( $instance ) {
        $title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', WPIMMO_PLUGIN_NAME ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of properties to show:', WPIMMO_PLUGIN_NAME ); ?></label> 
            <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'condition' ); ?>"><?php _e( 'Condition:', WPIMMO_PLUGIN_NAME ); ?></label>
            <select id="<?php echo $this->get_field_id( 'condition' ); ?>" name="<?php echo $this->get_field_name( 'condition' ); ?>">
                <option value=""><?php _e('None', WPIMMO_PLUGIN_NAME ); ?></option>
                <?php foreach ( WPImmo::$fields as $key => $field ) : ?>
                    <?php if ( $field['type'] === 'yesno' ) : ?>
                    <option value="<?php echo $key; ?>"<?php echo $instance['condition'] === $key ? ' selected="selected"' : ''; ?>><?php echo $field['name']; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </p>
        <?php 
    }
    
    public function widget( $args, $instance ) {
        $output = '';
        $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 3;
        $args_query = array(
            'post_type' => WPIMMO_POST_TYPE,
            'posts_per_page'      => $number,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true
        );
        if ( ! empty( $instance['condition'] ) ) :
            $args_query['meta_key'] = 'wpimmo_' . $instance['condition'];
            $args_query['meta_value'] = 'oui';
            $args_query['meta_compare'] = '=';
        endif;
        $r = new WP_Query( apply_filters( 'widget_posts_args', $args_query ) );

        if ($r->have_posts()) :
            
            $output .= $args['before_widget'];
            if ( ! empty( $instance['title'] ) ):
                $output .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            endif;
            $output .= '<ul class="wpimmo-list">' ;
            while ( $r->have_posts() ) :
                $r->the_post();
                $output .= '<li>';
                $output .= '<a href="' . get_the_permalink() . '" class="wpimmo_rollover animated">' . __( 'Discover', WPIMMO_PLUGIN_NAME ) . '</a>';
                if ( ! empty( WPImmo::$options['front']['field_pastille'] ) ) :
                    $pastille = wpimmo_get_field( get_the_ID(), WPImmo::$options['front']['field_pastille'] );
                endif;
                $output .= ( ! empty( WPImmo::$options['front']['list_pastille'] ) and $pastille === 'oui' ) ? '<div class="wpimmo_pastille">' . WPImmo::$options['front']['list_pastille'] . '</div>' : '';
                $images = wpimmo_get_field( get_the_ID(), 'images' );
                if( !empty( $images ) ): 
                    $image = wp_get_attachment_image( $images[0], 'wpi_list' );
                    $output .= '<a href="' . get_the_permalink() .'">' . $image . '</a><br />';
                else:
                
                    $output .= '<a href="' . get_the_permalink() .'"><img class="wpimmo-no-photo" src="' . plugin_dir_url( __FILE__ ) . '../images/no-photo.png" width="' . WPImmo::$options['images']['wpi_list']['width'] . '" height="' . WPImmo::$options['images']['wpi_list']['height'] .'" /></a><br />';
                endif;
                $output .= WPImmo_Tools::fields_replaced_string( array(
                    'source' => 'wp',
                    'post_id' => get_the_ID(),
                    'mask' => WPImmo::$options['front']['list_title'],
                    'linked' => true,
                    'truncate' => 9,
                ) );
                $output .= '</li>';
            endwhile;
            $output .= '</ul>';
            $output .= $args['after_widget'];
            wp_reset_postdata();
        endif;
        echo $output;
    }
}

/**
 * WP Immo Search Widget
 * 
 * @since 1.0
 */
class WPImmo_Widget_Search extends WP_Widget {
    
    public static function register() {
        register_widget( __CLASS__ );
    }

    public function __construct() {
        $widget_ops = array(
            'classname' => 'widget_wpimmo_search', 
            'description' => __( 'Search box for WP Immo properties.', WPIMMO_PLUGIN_NAME ),
        );
        parent::__construct( 'widget_wpimmo_search', __( 'WP Immo - Search', WPIMMO_PLUGIN_NAME ), $widget_ops );
    }
    
    public function flush_widget_cache() {
            wp_cache_delete('widget_wpimmo_search', 'widget');
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['type'] = $new_instance['type'];
        $this->flush_widget_cache();

        return $instance;
    }

    public function form( $instance ) {
        $title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $type  = $instance['type'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp_immo' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Type:', WPIMMO_PLUGIN_NAME ); ?></label>
            <select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
                <option value="full"<?php echo $instance['type'] === 'full' ? ' selected="selected"' : ''; ?>><?php _e('Full', WPIMMO_PLUGIN_NAME ); ?></option>
                <option value="basic"<?php echo $instance['type'] === 'basic' ? ' selected="selected"' : ''; ?>><?php _e('Basic', WPIMMO_PLUGIN_NAME ); ?></option>
            </select>
        </p>
        <?php 
    }
    
    public function widget( $args, $instance ) {
        global $wpdb;
        $type = esc_attr( $instance['type'] );
        $output = '';
        if ( ! empty ( WPImmo::$search[$type] ) ) :
            $output .= $args['start_wrapper'];
            $output .= $args['before_widget'];
            if ( ! empty( $instance['title'] ) ):
                $output .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            endif;
            $output .= '<form id="' . $this->id . '-form" class="wpimmo-searchform" action="' . get_permalink( WPImmo::$options['front']['list_page_id'] ) . '" method="get" role="search">';
            foreach ( WPImmo::$search[$type] as $key => $tab ) :
                $output .= '<div class="wpimmo-field-wrapper">';
                $output .= ! empty( $tab['label'] ) ? '<label>' . $tab['label'] . '</label>' : '';
                $field_name = ! empty( $tab['name'] ) ? $tab['default'] : $key;
                $get_value = urldecode( $_GET[$field_name] ) ;
                $output .= '<div class="wpimmo-field wpimmo-' . $tab['type'] .'">';
                switch( $tab['type'] ):
                    case 'text':
                    case 'min':
                    case 'max':
                        $output .= '<input id="wpimmo-field-' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" name="' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" type="text" value="' . $get_value . '"' . ( ! empty( $tab['placeholder'] ) ? ' placeholder="' . $tab['placeholder'] . '"' : '' ) .' />';
                        break;
                    case 'select':
                        $output .= '<select id="wpimmo-field-' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" name="' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '">';
                        $output .= ! empty( $tab['default'] ) ? '<option value="">' . $tab['default'] . '</option>' : '';
                        if( ! empty( $tab['taxonomy'] ) ):
                            $options = array();
                            $tmp = get_terms( $tab['taxonomy'], '&hide_empty=' . $tab['hide_empty'] );
                            foreach( $tmp as $tax ):
                                $options[] = $tax->name;
                            endforeach;
                        elseif( ! empty( $tab['values'] ) ):
                            $options = $tab['values'];
                        else:
                            $options = $wpdb->get_col("SELECT DISTINCT `meta_value` FROM `$wpdb->postmeta` WHERE `meta_key` = 'wpimmo_{$key}' ORDER BY `meta_value` " );
                        endif;
                        if( ! empty( $options ) ):
                            foreach( $options as $counter => $option ):
                                $option_txt = $tab['is_numeric'] == 1 ? number_format_i18n( $option ) : $option;
                                $output.= '<option value="' . urlencode( $option ) . '"' . ( $option == $get_value ? ' selected="selected"' : '' ) . '>' . $option_txt . ( ! empty( $tab['unit'] ) ? $tab['unit'] : '' ) . '</option>';
                            endforeach;
                        endif;
                        $output .= ! empty( $tab['last_value'] ) ? '<option value="' . $tab['last_value'] . '">' . $tab['last_option'] . '</option>' : '';
                        $output .= '</select>';
                        break;
                    case 'interval':
                        $output .= '<input id="wpimmo-field-wpimin_' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" name="wpimin_' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" type="text" value="' . ( ! empty( $tab['name'] ) ? sanitize_key( $_GET['wpimin_' . $tab['name']] ) : sanitize_key( $_GET['wpimin_' . $key] ) ) . '"' . ( ! empty( $tab['first_placeholder'] ) ? ' placeholder="' . $tab['first_placeholder'] . '"' : '' ) .' />';
                        $output .= '<input id="wpimmo-field-' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" name="' . ( ! empty( $tab['name'] ) ? $tab['name'] : $key ) . '" type="text" value="' . sanitize_key( $_GET[$field_name] ) . '"' . ( ! empty( $tab['second_placeholder'] ) ? ' placeholder="' . $tab['second_placeholder'] . '"' : '' ) .' />';
                        break;
                    case 'submit':
                        $output .= '<input type="submit" value="' . ( ! empty( $tab['value'] ) ? $tab['value'] : __( 'Send', WPIMMO_PLUGIN_NAME ) ) . '" />';
                        break;
                    default:
                        // nothing to do, but do !
                        break;
                endswitch;
                $output .= '</div>';
                $output .= '</div>';
            endforeach;
            $output .= '<input type="hidden" name="wpimmo_search" value="1" />';
            $output .= '</form>';
            $output .= $args['after_widget'];
            $output .= $args['end_wrapper'];
        endif;
        echo $output;
    }
}

/**
 * WP Immo Fields Group Widget
 * 
 * @since 1.0
 */
class WPImmo_Widget_Group extends WP_Widget {
    
    public static function register() {
        register_widget( __CLASS__ );
    }

    public function __construct() {
        $widget_ops = array(
            'classname' => 'widget_wpimmo_group', 
            'description' => __( 'Fields group for WP Immo properties.', WPIMMO_PLUGIN_NAME ),
        );
        parent::__construct( 'widget_wpimmo_group', __( 'WP Immo - Group', WPIMMO_PLUGIN_NAME ), $widget_ops );
    }
    
    public function flush_widget_cache() {
            wp_cache_delete('widget_wpimmo_group', 'widget');
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['id'] = $new_instance['id'];
        $instance['show_title'] = $new_instance['show_title'];
        $this->flush_widget_cache();

        return $instance;
    }

    public function form( $instance ) {
        $id  = $instance['id'];
        $show_title  = $instance['show_title'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'Group:', WPIMMO_PLUGIN_NAME ); ?></label>
            <select id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>">
                <?php foreach ( WPImmo::$groups as $group ) : ?>
                    <option value="<?php echo $group['id']; ?>"<?php echo $id == $group['id'] ? ' selected="selected"' : ''; ?>><?php echo $group['title']; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show title:', 'wp_immo' ); ?></label> 
            <input name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="radio" value="1" <?php echo $show_title == 1 ? ' selected="selected"' : ''; ?> /><?php _e( 'yes', WPIMMO_PLUGIN_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="radio" value="0" <?php echo empty ( $show_title ) ? ' selected="selected"' : ''; ?> /><?php _e( 'no', WPIMMO_PLUGIN_NAME ); ?>
        </p>
        <?php 
    }
    
    public function widget( $args, $instance ) {
        global $post;
        $id = esc_attr( $instance['id'] );
        $show_title = esc_attr( $instance['show_title'] );
        $group = WPImmo_Front::get_group( $id );
        $output = '';
        if ( ! empty ( $group ) ) :
            $output .= $args['before_widget'];
            if ( ! empty( $group['title'] ) and $show_title == 1 ):
                $output .= $args['before_title'] . apply_filters( 'widget_title', $group['title'] ). $args['after_title'];
            endif;
            $output .= '<div class="wpimmo-group-' . $group['id'] . '">';
            foreach ( $group['fields'] as $tab ) :
                $value = trim( WPImmo_Tools::get_field( $post->ID, $tab['key'], true, true ) );
                if ( ! empty( $value ) and $value != '0 ' . WPImmo::$fields[$tab['key']]['unit'] ) :
                    $output .= '<p>' . $tab['label'] . ' <span>' . $value;
                    if ( ! empty( $tab['combined'] ) ) :
                        $output .= ' ' . WPImmo_Tools::get_field( $post->ID, $tab['combined'], true, true );
                    endif;
                    $output .= '</span></p>';
                endif;
            endforeach;
            $output .= '</div>';
            $output .= $args['after_widget'];
        endif;
        echo $output;
    }
}

add_action( 'widgets_init', array( 'WPImmo_Widget_Group', 'register' ) );
add_action( 'widgets_init', array( 'WPImmo_Widget_Search', 'register' ) );
add_action( 'widgets_init', array( 'WPImmo_Widget_Last_Properties', 'register' ) );