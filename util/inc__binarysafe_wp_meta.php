<?php
/**
 * Binary safe wrapper for wordpress's meta functions
 *
 * Functions that rely on add_metadata(), get_metadata() and update_metadata()
 * are not binary safe and also mess about with slashes... not fun.
 *
 * We provide wrappers to commonly used functions such as add_post_meta() and
 * such like.
 * 
 * @author Peter Maxwell <peter@allicient.co.uk>
 * @copyright Peter Maxwell, 2015
 * @package WordPress-Libs
 * @since 0.1
 * @version 0.1
 *
 * @todo Implement meta storage for non-post stuff
 */
defined('ABSPATH') or die('');








/**
 * Binary safe wrapper for add_post_meta()
 */
function bs_add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {

	// Process the value
	$l_s__value = bs_encode_meta_value( $meta_value );

	// Name space the key to avoid accidental collision with standard WordPress API
	$l_s__key = bs_encode_meta_key( $meta_key );
			
	return add_post_meta( $post_id, $l_s__key, $l_s__value, $unique );

}


function bs_update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {

	// Process the value
	$l_s__value = bs_encode_meta_value( $meta_value );
	
	// Process the "prev value" if not empty
	$l_s__prev = bs_encode_meta_value( $prev_value );

	// Name space the key to avoid accidental collision with standard WordPress API
	$l_s__key = bs_encode_meta_key( $meta_key );
	
	return update_post_meta( $post_id, $l_s__key, $l_s__value, $l_s__prev );

}


function bs_delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {

	// Process the value
	$l_s__value = bs_encode_meta_value( $meta_value );

	// Name space the key to avoid accidental collision with standard WordPress API
	$l_s__key = bs_encode_meta_key( $meta_key );

	return delete_post_meta( $post_id, $l_s__key, $l_s__value );
	
}



function bs_get_post_meta( $post_id, $meta_key = '', $single = false ) {

	// Name space the key to avoid accidental collision with standard WordPress API
	if ( ! empty( $meta_key ) )
			$l_s__key = bs_encode_meta_key( $meta_key );
		else
			$l_s__key = '';

	// $l_s__base64 should either be empty string or base64 encoded
	// and serialized data
	$l_s__base64 = get_post_meta( $post_id, $l_s__key, $single );
	
	return bs_decode_meta_value( $l_s__base64 );
	
}





function bs_encode_meta_value( $in_m__meta_value ) {

	// If we have a zero length string then treat that specially
	if ( '' === $in_m__meta_value )
		return '';

	// To avoid ambiguity between encoded false and function returning false
	// on unserialize, we wrap in an array... more PHP joy.
	$l_a__value = array( 'value' => $in_m__meta_value );

	// Serialize and base64 encode
	$l_s__serialized = serialize( $l_a__value );
	$l_s__base64 = base64_encode( $l_s__serialized );
	if ( false === $l_s__base64 )
		do_action( 'tgv_write_log_entry', __CLASS__, __FUNCTION__, 'ERROR could not base64 encode', 5, true );

	return $l_s__base64;
	
}



function bs_decode_meta_value( $in_m__base64, $in_i__recursion_level = 3 ) {

	// If we have zero length string or otherwise empty var, treat specially
	if ( '' === $in_m__base64 || empty( $in_m__base64 ) )
		return '';
		
	// It's possible to end up with an array here due to non-unique values
	// for a given key, if so recursively process...
	if ( is_array( $in_m__base64 ) ) {

		if ( 0 == $in_i__recursion_level )
			do_action( 'tgv_write_log_entry', __CLASS__, __FUNCTION__, 'ERROR hit maximum recursion level', 5, true );
	
		$l_a__result = array();
		foreach ( $in_m__base64 as $l_m__index => $l_s__base64 ) {
		
			$l_a__result[ $l_m__index ] = bs_decode_meta_value( $l_s__base64, $in_i__recursion_level - 1 );
		
		}
		
		return $l_a__result;
	
	
	} else {

		// Decaode base64
		$l_s__serialized = base64_decode( $in_m__base64 );
		if ( false === $l_s__serialized )
			do_action( 'tgv_write_log_entry', __CLASS__, __FUNCTION__, 'ERROR could not base64 decode', 5, true );

		// Unserialize
		$l_m__meta_value = unserialize( $l_s__serialized );
		if ( false === $l_m__meta_value )
			do_action( 'tgv_write_log_entry', __CLASS__, __FUNCTION__, 'ERROR could not unserialize', 5, true );

		// Sanity check
		if ( ! is_array( $l_m__meta_value ) || ! isset( $l_m__meta_value[ 'value' ] ) )
			do_action( 'tgv_write_log_entry', __CLASS__, __FUNCTION__, 'ERROR could not parse unserialized data', 5, true );
			
		return $l_m__meta_value[ 'value' ];
		
	}
	
}


function bs_encode_meta_key( $in_s__meta_key ) {

	return 'bs_' . $in_s__meta_key;

}


