<?php
/**
 * Uninstall Smart Product Table
 *
 * @package Smart_Product_Table
 * @version 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all smarttable_product posts
$posts = get_posts( array(
	'numberposts' => -1,
	'post_type'   => 'smarttable_product',
	'post_status' => 'any',
) );

foreach ( $posts as $post ) {
	wp_delete_post( $post->ID, true );
}

// Delete all plugin options
delete_option( 'smarttable_version' );
delete_option( 'smarttable_settings' );

// Delete all post meta
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_smarttable_%'" );

// Clear any cached data
wp_cache_flush();