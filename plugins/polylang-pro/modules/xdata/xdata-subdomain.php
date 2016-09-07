<?php

/**
 * A class to handle cross domain data and single sign on for subdomains
 *
 * @since 2.0
 */
class PLL_Xdata_Subdomain extends PLL_Xdata_Base {
	private $curlang, $ajax_urls;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );

		// Inactive languages will be lost later. Need to save current language and ajax urls now.
		$this->curlang = $this->links_model->get_language_from_url();
		if ( empty( $this->curlang ) ) {
			$this->curlang = $this->options['default_lang'];
		}

		foreach ( $this->model->get_languages_list() as $lang ) {
			$this->ajax_urls[ $lang->slug ] = $this->links_model->switch_language_in_link( admin_url( 'admin-ajax.php' ), $this->model->get_language( $lang ) );
		}

		if ( empty( $_POST['wp_customize'] ) ) {
			add_action( 'wp_head', array( $this, 'maybe_language_switched' ) );
		}

		add_filter( 'site_url', array( $this, 'site_url' ) );
	}

	/**
	 * Builds an ajax request url to the domain defined by a language
	 * Overrides parent method to take care of inactive languages
	 *
	 * @since 2.0
	 *
	 * @param string $lang
	 * @param array  $args
	 * @return string
	 */
	public function ajax_url( $lang, $args ) {
		return add_query_arg( $args, $this->ajax_urls[ $lang ] );
	}

	/**
	 * Outputs the javascript to create the cross domain request when the language just switched
	 *
	 * @since 2.0
	 */
	public function maybe_language_switched() {
		$redirect = urlencode( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		if ( $js = $this->maybe_get_xdomain_js( $redirect, $this->curlang ) ) {
			echo '<script type="text/javascript">' . $js . '</script>';
		}
	}

	/**
	 * Saves info on the current user session and redirects to the main domain
	 *
	 * @since 2.0
	 *
	 * @param string           $redirect_to           The redirect destination URL.
	 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
	 * @return string
	 */
	function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $this->options['hide_default'] && ! is_wp_error( $user ) ) {
			$redirect_to = $this->_login_redirect( $redirect_to, $requested_redirect_to, $user );
		}
		return $redirect_to;
	}

	/**
	 * Returns the correct site url ( mainly to get the correct login form )
	 *
	 * @since 2.0
	 *
	 * @param string $url
	 * @return string
	 */
	public function site_url( $url ) {
		$lang = $this->links_model->get_language_from_url();
		$lang = $this->model->get_language( $lang );
		return $this->links_model->add_language_to_link( $url, $lang );
	}
}
