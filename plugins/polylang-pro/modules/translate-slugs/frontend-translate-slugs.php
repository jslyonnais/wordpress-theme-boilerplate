<?php

/**
 * Modifies links on frontend
 *
 * @since 1.9
 */
class PLL_Frontend_Translate_Slugs extends PLL_Translate_Slugs {
	public $curlang;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $slugs_model
	 */
	public function __construct( &$slugs_model, &$curlang ) {
		parent::__construct( $slugs_model );

		$this->model = &$slugs_model->model;
		$this->links_model = &$slugs_model->links_model;
		$this->curlang = &$curlang;

		// Translates slugs in archive link
		if ( $this->links_model->using_permalinks ) {
			foreach ( array( 'author_link', 'post_type_archive_link', 'search_link', 'get_pagenum_link', 'attachment_link' ) as $filter ) {
				add_filter( $filter, array( $this, 'translate_slug' ), 20, 'post_type_archive_link' == $filter ? 2 : 1 );
			}
		}

		add_filter( 'pll_get_archive_url', array( $this, 'pll_get_archive_url' ), 10, 2 );
		add_filter( 'pll_check_canonical_url', array( $this, 'pll_check_canonical_url' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 1 );
	}

	/**
	 * Translate the slugs
	 *
	 * @since 1.9
	 *
	 * @param string $link
	 * @param string $post_type optional
	 * @return string modified link
	 */
	public function translate_slug( $link, $post_type = '' ) {
		if ( empty( $this->curlang ) ) {
			return $link;
		}

		$types = array(
			'post_type_archive_link' => 'archive_' . $post_type,
			'get_pagenum_link'       => 'paged',
			'author_link'            => 'author',
			'attachment_link'        => 'attachment',
			'search_link'            => 'search',
		);
		$link = $this->slugs_model->translate_slug( $link, $this->curlang, $types[ current_filter() ] );

		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$link = $this->slugs_model->translate_slug( $link, $this->curlang, 'front' );
		}
		return $link;
	}

	/**
	 * Translate the slugs in archive urls
	 *
	 * @since 1.9
	 *
	 * @param string $url
	 * @param object $language
	 * @return string modified url
	 */
	public function pll_get_archive_url( $url, $language ) {
		if ( is_post_type_archive() && ( $post_type = get_query_var( 'post_type' ) ) ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'archive_' . $post_type );
		}

		if ( is_tax( 'post_format' ) ) {
			$term = get_queried_object();
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'post_format' );
			$url = $this->slugs_model->switch_translated_slug( $url, $language, $term->slug );
		}

		if ( is_author() ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'author' );
		}

		if ( is_search() ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'search' );
		}

		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'front' );
		}

		// If the paged slug is translated, PLL_Links_Model::remove_paged_from_link does not work
		$url = $this->slugs_model->remove_paged_from_link( $url );

		return $url;
	}

	/**
	 * Modifies the canonical url with the translated slugs
	 *
	 * @since 1.9
	 *
	 * @param string $redirect_url
	 * @param object $language
	 * @return string modified canonical url
	 */
	public function pll_check_canonical_url( $redirect_url, $language ) {
		global $wp_query, $post;

		$slugs = array();

		if ( is_single() || is_page() ) {
			if ( isset( $post->ID ) && $this->model->is_translated_post_type( $post->post_type ) ) {
				$slugs[] = $post->post_type;
			}
		}

		elseif ( is_category() || is_tag() || is_tax() ) {
			$obj = $wp_query->get_queried_object();
			if ( $this->model->is_translated_taxonomy( $obj->taxonomy ) ) {
				$slugs[] = $obj->taxonomy;
			} elseif ( 'post_format' == $obj->taxonomy ) {
				$slugs[] = 'post_format';
				$slugs[] = $obj->slug;
			}
		}

		elseif ( is_post_type_archive() ) {
			$obj = $wp_query->get_queried_object();
			$slug = true == $obj->has_archive ? $obj->rewrite['slug'] : $obj->has_archive;
			$slugs[] = 'archive_' . $slug;
		}

		elseif ( is_author() ) {
			$slugs[] = 'author';
		}

		elseif ( is_search() ) {
			$slugs[] = 'search';
		}

		if ( is_paged() ) {
			$slugs[] = 'paged';
		}

		if ( is_attachment() ) {
			$slugs[] = 'attachment';
		}

		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$slugs[] = 'front';
		}

		foreach ( $slugs as $slug ) {
			$redirect_url = $this->slugs_model->switch_translated_slug( $redirect_url, $language, $slug );
		}

		return $redirect_url;
	}

	/**
	 * Hack to avoid WP canonical url breaking
	 *
	 * @since 1.9
	 */
	public function template_redirect() {
		if ( isset( $this->slugs_model->translated_slugs['paged'] ) ) {
			$GLOBALS['wp_rewrite']->pagination_base = $this->slugs_model->translated_slugs['paged']['translations'][ $this->curlang->slug ];
		}
	}
}
