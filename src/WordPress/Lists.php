<?php

namespace Samwilson\Bibliodata\WordPress;

class Lists {
	/** @var \wpdb */
	protected $db;
	public function __construct( \wpdb $wpdb ) {
		$this->db = $wpdb;
	}

	/**
	 * @return List
	 */
	public function get_all() {
		$current_user = wp_get_current_user();
		$sql = "SELECT * FROM bibliodata_lists WHERE owner=?";
		$lists = $this->db->get_results( $this->db->prepare( $sql, [ $current_user->ID ] ) );
		foreach ( $lists as $list ) {
		}
		return $lists;
	}
}
