<?php

/**
 * Get WPGM key
 *
 * Retrieve the value of a given $key in WPGM plugin
 * 
 * @param  	boolean 				$post_id
 * @param  	string  				$value   
 * @return 	$meta[$value]          
 */
function get_wpgm_key( $post_id = false, $key ) {
	if( !$key ) {
		return;
	}

	$meta = get_post_meta( $post_id, '_wpgm_details', true );

	if( empty( $meta ) ) {
		return;
	}

	return $meta[$key];
}


/**
 * Get WPGM address 
 * 
 * @param  	boolean 				$post_id
 * @return 	function get_wpgm_key
 */
function get_wpgm_address( $post_id = false ) {

	return get_wpgm_key( $post_id, 'address' );
}


/**
 * The WPGM address
 *
 * @param  boolean 					$post_id
 */
function the_wpgm_address( $post_id = false ) {

	$key = get_wpgm_address( $post_id );
	
	echo $key;	                                      
}