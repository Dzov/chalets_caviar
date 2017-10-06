<?php
/**
 * This file controls the property images meta box.
 */
# Exit if accessed directly
defined( 'ABSPATH' ) or exit;
//token
wp_nonce_field( 'wpimmo','wpimmo_images_nonce' );
$edit_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

$images = json_decode( get_post_meta( get_the_ID(), 'wpimmo_images', true ) );
?>

<ul class="wpimmo-img-container">
<?php
if ( ! empty( $images ) ) : ?>
    <?php foreach ( $images as $img_id ) : ?>
        <li class="wpimmo-img" id="img-<?php echo $img_id; ?>">
            <a class="wpimmo-button-delete ir" href="javascript:void(0);"><?php _e( 'Delete', WPIMMO_PLUGIN_NAME ); ?></a>
            <?php echo wp_get_attachment_image( $img_id, 'thumbnail' ); ?>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
</ul>

<input class="wpimmo-img-ids" name="wpimmo-images-ids" type="hidden" value="<?php echo implode( ',', $images ); ?>" />
<a href="javascript:void(0);" class="alignright button wpimmo-button-add"><?php _e( 'Add images', WPIMMO_PLUGIN_NAME ); ?></a>
