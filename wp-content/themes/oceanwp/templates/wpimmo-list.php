<?php
/**
 * The template for displaying properties list page
 *
 * @since WP Immo 1.0
 */
the_content();
get_header(); ?>

    <div id="primary" class="content-area">
        
        <div id="content" class="site-content" role="main">

            <?php
            if( function_exists( 'yoast_breadcrumb' ) ):
                yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
            endif;
            ?>
            
            <header class="archive-header">
                <h1><?php echo WPIMMO::$options['seo']['list_h1']; ?></h1>
            </header><!-- .page-header -->
            
            <?php
            if( WPImmo::$options['front']['search_on_list_template'] == 1 ):
                $widget_args['start_wrapper'] = '<div class="wpimmo_search_wrapper">';
                $widget_args['end_wrapper'] = '</div>';
                $widget_instance['type'] = WPImmo::$options['front']['template_search_type'];
                if( !empty( WPImmo::$search['default_title'] ) ):
                    $widget_instance['title'] = WPImmo::$search['default_title'];
                endif;
                the_widget( 'WPImmo_Widget_Search', $widget_instance, $widget_args );
            endif;
            ?>
                       
            <?php wpimmo_list(); ?>
            
        </div><!-- #content -->
        
    </div><!-- #primary -->
    
<?php get_footer();
