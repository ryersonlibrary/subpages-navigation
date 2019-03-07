<?php

/* Borrowed from WP 2.8 */

if(!function_exists('esc_html')) {
    /**
     * Escaping for HTML blocks.
     *
     * @since 2.8.0
     *
     * @param string $text
     * @return string
     */
    function esc_html( $text ) {
    	$safe_text = wp_check_invalid_utf8( $text );
    	$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    	return apply_filters( 'esc_html', $safe_text, $text );
    	return $text;
    }
}

if(!function_exists('esc_attr')) {
    /**
     * Escaping for HTML attributes.
     *
     * @since 2.8.0
     *
     * @param string $text
     * @return string
     */
    function esc_attr( $text ) {
    	$safe_text = wp_check_invalid_utf8( $text );
    	$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    	return apply_filters( 'attribute_escape', $safe_text, $text );
    }
}

if(!function_exists('_wp_specialchars')) {
    function _wp_specialchars($string, $quote_style = ENT_NOQUOTES) {
        return wp_specialchars($string, $quote_style);
    }
}

?>