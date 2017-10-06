<?php
/**
 * This file controls the porpertu meta box.
 */
# Exit if accessed directly
defined( 'ABSPATH' ) or exit;
//token
wp_nonce_field( 'wpimmo','wpimmo_informations_nonce' );
?>
<div class="metab-box-item-title">
    <?php
    foreach( self::$fields as $key => $field ):
        if( !in_array( $key, array( self::$platform['title_key'], self::$platform['description_key'] ) ) ):
            ?>
            <div class="meta-box-item-content">
                <table class="form-table post-box">
                    <tbody>
                        <tr>
                            <?php
                            $value = esc_attr( get_post_meta( $object->ID, 'wpimmo_' . $key, true ) );
                            switch( $field['type'] ):
                                case 'text':
                                case 'tel':
                                case 'email':
                                case 'number':
                                case 'date':
                                    if( in_array( $field['type'], array( 'text', 'email' ) ) ):
                                        $class_input = 'regular-text';
                                    else:
                                        $class_input = '';
                                    endif;
                                    ?>
                                    <th><label for="wpimmo_<?php echo $key; ?>"><?php echo $field['name']; ?></label></th>
                                    <td>
                                        <div class="wpimmo-input-wrap">
                                            <input class="<?php echo $class_input; echo ( $field['type'] == 'date' ) ? ' datepicker' : ''; ?>" type="<?php echo $field['type']; ?>" name="wpimmo_<?php echo $key; ?>" value="<?php echo $value; ?>" />
                                        </div>
                                        <?php if( !empty( $field['unit'] ) ): ?>
                                            <div class="wpimmo-input-append"><?php echo $field['unit']; ?></div>
                                        <?php endif; ?>
                                        <?php if( !empty( $field['help'] ) ): ?>
                                            <p class="wpimmo-help description">(<?php echo $field['help']; ?>)</p>
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                break;
                                case 'yesno':
                                    ?>
                                    <th><label for="wpimmo_<?php echo $key; ?>"><?php echo $field['name']; ?></label></th>
                                    <td>
                                        <input type="radio" name="wpimmo_<?php echo $key; ?>" value="<?php _e( 'yes', WPIMMO_PLUGIN_NAME ); ?>"<?php echo ( $value == __( 'yes', WPIMMO_PLUGIN_NAME ) ) ? ' checked="checked"' : ''; ?> /><?php _e( 'yes', WPIMMO_PLUGIN_NAME ); ?>&nbsp;&nbsp;&nbsp;
                                        <input type="radio" name="wpimmo_<?php echo $key; ?>" value="<?php _e( 'no', WPIMMO_PLUGIN_NAME ); ?>"<?php echo ( $value == __( 'no', WPIMMO_PLUGIN_NAME ) or empty( $value ) ) ? ' checked="checked"' : ''; ?> /><?php _e( 'no', WPIMMO_PLUGIN_NAME ); ?>
                                        <?php if( !empty( $field['help'] ) ): ?>
                                            <p class="description">(<?php echo $field['help']; ?>)</p>
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                break;
                                case 'taxonomy':
                                    $terms = get_terms( $field['taxonomy'], array( 'hide_empty'=>false ) );
                                    ?>
                                    <th><label for="wpimmo_<?php echo $key; ?>"><?php echo $field['name']; ?></label></th>
                                    <td>
                                        <select name="wpimmo_<?php echo $key; ?>">
                                            <option value=""></option>
                                            <?php foreach( $terms as $term ): ?>
                                                <option value="<?php echo $term->term_id; ?>" <?php if( has_term( $term->term_id, $field['taxonomy'] ) ): echo ' selected="selected"'; endif; ?>><?php echo $term->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if( !empty( $field['help'] ) ): ?>
                                            <p class="description">(<?php echo $field['help']; ?>)</p>
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                break;
                                case 'images':
                                break;
                                default:
                                break;
                            endswitch;
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        endif;
    endforeach; ?>
</div>