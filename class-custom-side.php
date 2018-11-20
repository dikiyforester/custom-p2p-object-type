<?php

/**
 * Custom p2p side class.
 */
class P2P_Side_Custom extends P2P_Side {

	protected $item_type = 'P2P_Item_Custom';

	function __construct( $query_vars ) {
		$this->query_vars = (array) $query_vars;
	}

	function get_object_type() {
		return CUSTOM_P2P_OBJECT_TYPE;
	}

	function get_desc() {
		return __( 'Custom items', CUSTOM_P2P_TEXTDOMAIN );
	}

	function get_title() {
		return $this->get_desc();
	}

	function get_labels() {
		return (object) array(
			'singular_name' => __( 'Custom item', CUSTOM_P2P_TEXTDOMAIN ),
			'search_items' => __( 'Search Custom items', CUSTOM_P2P_TEXTDOMAIN ),
			'not_found' => __( 'No custom items found.', CUSTOM_P2P_TEXTDOMAIN ),
		);
	}

	function can_edit_connections() {
		return true;
	}

	function can_create_item() {
		return true;
	}

	function translate_qv( $qv ) {
		return (array) $qv;
	}

	function do_query( $args ) {
		return null;
	}

	function capture_query( $args ) {
		return null;
	}

	function get_list( $query ) {
		return null;
	}

	function is_indeterminate( $side ) {
		return true;
	}

	function get_base_qv( $q ) {
		return array_merge( $this->query_vars, $q );
	}

	protected function recognize( $arg ) {
		global $wpdb;

		if ( is_a( $arg, $this->item_type ) ) {
			return $arg;
		}

		$table = CUSTOM_P2P_OBJECT_TYPE;

		/* @var $wpdb wpdb */
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->$table WHERE ID = %d", $arg ) );
	}

}
