<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly
};

/**
 * A class to load specific Pro modules
 *
 * @since 1.9
 */
class Polylang_Pro {
	/**
	 * Initialization
	 *
	 * @since 1.9
	 */
	public function init( &$polylang ) {
		if ( ! $polylang instanceof PLL_Frontend ) {
			load_plugin_textdomain( 'polylang-pro', false, basename( POLYLANG_DIR ) . '/languages' );
			new PLL_License( POLYLANG_FILE, 'Polylang Pro', POLYLANG_VERSION, 'Frédéric Demarle' );

			add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );
		}

		$this->load_modules( $polylang );
	}

	/**
	 * Hack to download Polylang languages packs
	 *
	 * @since 1.9
	 *
	 * @param array  $args http request args
	 * @param string $url  url of the request
	 * @return array
	 */
	public function http_request_args( $args, $url ) {
		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/' ) ) {
			$plugins = (array) json_decode( $args['body']['plugins'], true );
			$plugins['plugins']['polylang/polylang.php'] = array( 'Version' => POLYLANG_VERSION );
			$args['body']['plugins'] = wp_json_encode( $plugins );
		}
		return $args;
	}

	/**
	 * Load modules
	 *
	 * @since 1.9
	 *
	 * @param object $polylang
	 */
	public function load_modules( &$polylang ) {
		$options = &$polylang->options;

		if ( get_option( 'permalink_structure' ) ) {
			// Translate slugs, only for pretty permalinks
			$slugs_model = new PLL_Translate_Slugs_Model( $polylang );
			$polylang->translate_slugs = $polylang instanceof PLL_Frontend ?
				new PLL_Frontend_Translate_Slugs( $slugs_model, $polylang->curlang ) :
				new PLL_Translate_Slugs( $slugs_model );

			// Share slugs only for pretty permalinks and language information in url
			if ( $options['force_lang'] ) {
				// Share post slugs
				$polylang->share_post_slug = $polylang instanceof PLL_Frontend ?
					new PLL_Frontend_Share_Post_Slug( $polylang ) :
					new PLL_Share_Post_Slug( $polylang );

				// Share term slugs
				// Backward compatibility with WP < 4.1
				// The unique key for term slug has been removed in WP 4.1
				if ( version_compare( $GLOBALS['wp_version'], '4.1', '>=' ) ) {
					$polylang->share_term_slug = $polylang instanceof PLL_Frontend ?
						new PLL_Frontend_Share_Term_Slug( $polylang ) :
						new PLL_Admin_Share_Term_Slug( $polylang );
				}
			}
		}

		// Active languages
		$polylang->active_languages = new PLL_Active_Languages( $polylang );

		// Advanced media
		if ( ! $polylang instanceof PLL_Frontend && $polylang->options['media_support'] ) {
			$polylang->advanced_media = new PLL_Admin_Advanced_Media( $polylang );
		}

		// Duplicate content
		if ( ! $polylang instanceof PLL_Frontend ) {
			$polylang->duplicate = new PLL_Duplicate( $polylang );
		}

		// Cross domain
		if ( PLL_COOKIE ) {
			switch ( $polylang->options['force_lang'] ) {
				case 2:
					$polylang->xdata = new PLL_Xdata_Subdomain( $polylang );
				break;
				case 3:
					$polylang->xdata = new PLL_Xdata_Domain( $polylang );
				break;
			}
		}
	}
}

add_action( 'pll_pre_init', array( new Polylang_Pro(), 'init' ) );
PLL_Advanced_Plugins_Compat::instance();
