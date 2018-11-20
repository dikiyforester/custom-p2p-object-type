<?php

/**
 * Custom p2p item class.
 */
class P2P_Item_Custom extends P2P_Item {

	public function get_permalink() {
		return '';
	}

	public function get_title() {
		$this->item->title;
	}

}
