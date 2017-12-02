<?php 
/**
 * Provides the markup for metabox
 *
 * @link       http://www.19h47.fr
 * @since      1.0.0
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 */
?>
<h4>
    <?php _e( 'Entrez une adresse postale pour l\'afficher sur la carte.', $this->plugin_name ) ?>        
</h4>

<div style="padding-bottom: 20px;" class="wpgm_admin_map">
    <?php echo WPGM_Helper::map_markup( array( 'height' => '400px' ), true, false ) ?>
</div>

<table style="padding-bottom: 10px">
    <tr>
        <th scope="row" style="text-align: right;">
            <label for="wpgm_address">
                <?php _e( 'Addresse', $this->plugin_name ) ?>        
            </label>
        </th>
        <td>
            <input type="hidden" name="wpgm_nonce" value="<?php echo wp_create_nonce( 'wpgm_details' ) ?>" />
            <input type="text" id="wpgm_address" name="wpgm_address" size="60" value="<?php echo sanitize_text_field( $fields['address'] ); ?>" />

            <a id="wpgm_address_search_submit" class="button" />
                <?php _e( 'Rechercher', $this->plugin_name ) ?>        
            </a>

            <a id="wpgm_address_clear" class="button" />
                <?php _e( 'Effacer', $this->plugin_name ) ?>        
            </a>

            <input type="hidden" id="wpgm_latitude" name="wpgm_latitude" value="<?php echo sanitize_text_field( $fields['latitude'] ); ?>" />

            <input type="hidden" id="wpgm_longitude" name="wpgm_longitude" value="<?php echo sanitize_text_field( $fields['longitude'] ); ?>" />
        </td>
    </tr>
</table>