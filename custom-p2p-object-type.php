<?php
/*
Plugin Name: Custom p2p object type
Description: Example of how to use wp-lib-posts-to-posts with custom object types, besides post and user types.
Version: 1.0.0
Author: Artem Frolov
*/

// Assumed you already have wp-lib-posts-to-posts installed in your system.

define( 'CUSTOM_P2P_OBJECT_TYPE', 'custom' );
define( 'CUSTOM_P2P_TEXTDOMAIN', 'custom' );

/**
 * Register custom object side for connection type.
 *
 * This function is a workaround.
 * It's better to check existance of P2P_Side_* class inside the
 * P2P_Connection_Type_Factory::create_side() method.
 * In this case we'll no need anything except loaded class P2P_Side_*.
 *
 * This improvement can be suggested to the original lib or added into the fork.
 *
 * @param P2P_Connection_Type $ctype Connection type.
 * @param array               $args  Connection type args.
 */
function custom_p2p_registered_connection_type( $ctype, $args ) {
	foreach ( array( 'from', 'to' ) as $direction ) {
		if ( empty( $args[ $direction ] ) || CUSTOM_P2P_OBJECT_TYPE !== $args[ $direction ] ) {
			continue;
		}

		$class = 'P2P_Side_' . ucfirst( CUSTOM_P2P_OBJECT_TYPE );

		if ( ! class_exists( $class ) ) {
			continue;
		}

		$query_vars = _p2p_pluck( $args, $direction . '_query_vars' );

		// Replace default object with our custom.
		$ctype->side[ $direction ] = new $class( $query_vars );
	}
}
add_action( 'p2p_registered_connection_type', 'custom_p2p_registered_connection_type', 99, 2 );

function init_custom_p2p_type_plugin( ) {
	if ( ! isset( $GLOBALS['wpdb']->{CUSTOM_P2P_OBJECT_TYPE} ) ) {
		scb_register_table( CUSTOM_P2P_OBJECT_TYPE );
	}

	require_once 'class-custom-side.php';
	require_once 'class-custom-item.php';

	// Register connection.
	$connection = p2p_register_connection_type( array(
		'name' => 'my-custom-connection-type',
		'from' => 'post',
		'to'   => CUSTOM_P2P_OBJECT_TYPE,
		'to_query_vars' => array(),
	) );

	// Add a post.
	$post_1_id = wp_insert_post( array(
		'post_title' => 'Some post',
	) );

	// Add new connections between posts and custom type objects.
	$p2p_id = p2p_create_connection( 'my-custom-connection-type', array(
		'from' => $post_1_id,
		'to'   => 1, // we've already created some custom items on activation.
		'meta' => array(
			'foo' => 'bar',
		),
	) );

	// Check connection.
	$connected = p2p_get_connection( $p2p_id );

	// Check meta.
	$p2p_meta = p2p_get_meta( $p2p_id );

	// Find all custom items connected to the post.
	$connected_custom = p2p_get_connections( 'my-custom-connection-type', array(
		'from' => $post_1_id,
		'fields' => 'p2p_to',
	) );

	// Find all posts connected to custom item.
	$connected_posts = p2p_get_connections( 'my-custom-connection-type', array(
		'to' => 1,
		'fields' => 'p2p_from',
	) );

	// And so on.
}
add_action( 'init', 'init_custom_p2p_type_plugin' );

// Plugin activation stuff.

function custom_p2p_type_plugin_activate() {
	add_option( 'custom_p2p_type_plugin_activate', true );
}
register_activation_hook( __FILE__, 'custom_p2p_type_plugin_activate' );

function load_custom_p2p_type_plugin() {
	global $wpdb;

	if ( get_option( 'custom_p2p_type_plugin_activate' ) ) {
		delete_option( 'custom_p2p_type_plugin_activate' );

		// Create table
		scb_install_table( CUSTOM_P2P_OBJECT_TYPE, "
			ID bigint(20) unsigned NOT NULL auto_increment,
			title longtext,
			description longtext,
			PRIMARY KEY  (ID)
		" );

		// Add some dummy data.
		for ( $index = 1; $index < 6; $index++ ) {
			$wpdb->insert( $wpdb->{CUSTOM_P2P_OBJECT_TYPE}, array(
				'title' => 'Title #' . $index,
				'description' => 'Description #' . $index,
			) );
		}
	}
}
add_action( 'init', 'load_custom_p2p_type_plugin', 99 );
