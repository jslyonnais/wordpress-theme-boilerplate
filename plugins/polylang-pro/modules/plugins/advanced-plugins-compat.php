<?php

/**
 * Manages compatibility with 3rd party plugins ( and themes )
 * This class is available as soon as the plugin is loaded
 *
 * @since 1.9.1
 */
class PLL_Advanced_Plugins_Compat {
	static protected $instance; // For singleton
	public $acf;

	/**
	 * Constructor
	 *
	 * @since 1.9.1
	 */
	protected function __construct() {
		// Beaver Builder
		add_filter( 'pll_copy_post_metas', array( $this, 'fl_builder_copy_post_metas' ), 10, 2 );

		// Advanced Custom Fields Pro
		add_action( 'init', array( $this->acf = new PLL_ACF(), 'init' ) );
	}

	/**
	 * Access to the single instance of the class
	 *
	 * @since 1.9.1
	 *
	 * @return object
	 */
	static public function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Allow to copy Beaver Builder data when creating a translation
	 *
	 * @since 1.9.1
	 *
	 * @param array $keys list of custom fields names
	 * @param bool  $sync true if it is synchronization, false if it is a copy
	 * @return array
	 */
	function fl_builder_copy_post_metas( $metas, $sync ) {
		$bb_metas = array(
			'_fl_builder_draft',
			'_fl_builder_draft_settings',
			'_fl_builder_data',
			'_fl_builder_data_settings',
			'_fl_builder_enabled'
		);

		return $sync ? $metas : array_merge( $metas, $bb_metas );
	}
}
