<?php

/**
 * Copy the title, content and excerpt from the source when creating a new post translation
 *
 * @since 1.9
 */
class PLL_Duplicate {
	public $options, $model, $filters_media;
	private $active; // Whether duplicate is active or not

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $polylang
	 */
	public function __construct( &$polylang ) {
		$this->options = &$polylang->options;
		$this->model = &$polylang->model;
		$this->filters_media = &$polylang->filters_media;

		// css and js
		add_action( 'admin_print_styles-post-new.php', array( $this, 'print_css' ) );
		add_action( 'admin_print_styles-post.php', array( $this, 'print_css' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'print_js' ) );

		add_action( 'pll_before_post_translations', array( $this, 'add_icon' ) );
		add_action( 'wp_ajax_pll_toggle_duplicate', array( $this, 'toggle' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 5, 2 );
	}

	/**
	 * Print css style
	 *
	 * @since 1.9
	 */
	public function print_css() { ?>
		<style type="text/css">
			.pll-copy {
				float: right;
				margin: 13px 7px;
				padding: 0;
				height: 20px;
				background: none;
				border: none;
				font-size: 20px;
				color: #DDDDDD;
				cursor: pointer;
			}
		</style><?php
	}

	/**
	 * Print js script
	 *
	 * @since 1.9
	 */
	public function print_js() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		$js = "//<![CDATA[
		jQuery( document ).ready(function( $ ) {
			$( '.pll-copy' ).on( 'click', function(){
				var data = {
					action:     'pll_toggle_duplicate',
					post_type:  $( '#post_type' ).val(),
					_pll_nonce: $( '#_pll_nonce' ).val()
				}
				$.post( ajaxurl, data , function( response ){
					if ( response.success ) {
						$( '.pll-copy' ).toggleClass( 'wp-ui-text-highlight' );
						if ( $( '.pll-copy' ).hasClass( 'wp-ui-text-highlight' ) ) {
							title = '" . esc_js( __( 'Deactivate the content duplication', 'polylang-pro' ) ) .  "';
						} else {
							title = '" . esc_js( __( 'Activate the content duplication', 'polylang-pro' ) ) .  "';
						}
						$( '.pll-copy' ).attr( 'title', title );
					}
				});
			});
		});
		//]]>";

		echo '<script type="text/javascript">' . $js . '</script>';
	}

	/**
	 * Add the duplicate content icon
	 *
	 * @since 1.9
	 *
	 * @param string $post_type
	 */
	public function add_icon( $post_type ) {
		if ( 'attachment' !== $post_type ) {
			$text = $this->active ? __( 'Deactivate the content duplication', 'polylang-pro' ) : __( 'Activate the content duplication', 'polylang-pro' );
			printf(
				'<button type="button" class="pll-copy dashicons-before dashicons-admin-page %1$s" title="%2$s"><span class="screen-reader-text">%3$s</span></button>',
				$this->active ? 'wp-ui-text-highlight' : '',
				esc_attr( $text ),
				esc_html( $text )
			);
		}
	}

	/**
	 * Ajax response to a click on the duplicate icon
	 *
	 * @since 1.9
	 */
	function toggle() {
		check_ajax_referer( 'pll_language', '_pll_nonce' );

		if ( post_type_exists( $post_type = $_POST['post_type'] ) ) {
			$duplicate_options = get_user_meta( get_current_user_id(), 'pll_duplicate_content', true );
			$active = ! empty( $duplicate_options ) && ! empty( $duplicate_options[ $post_type ] );
			$duplicate_options[ $post_type ] = ! $active;

			if ( update_user_meta( get_current_user_id(), 'pll_duplicate_content', $duplicate_options ) ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
	}

	/**
	 * Fires the content copy
	 *
	 * @since 1.9
	 *
	 * @param string $post_type
	 * @param object $post current post object
	 */
	public function add_meta_boxes( $post_type, $post ) {
		global $post_type;

		$duplicate_options = get_user_meta( get_current_user_id(), 'pll_duplicate_content', true );
		$this->active = ! empty( $duplicate_options ) && ! empty( $duplicate_options[ $post_type ] );

		if ( $this->active && 'post-new.php' === $GLOBALS['pagenow'] && isset( $_GET['from_post'], $_GET['new_lang'] ) ) {
			// Capability check already done in post-new.php
			$this->copy_content( get_post( (int) $_GET['from_post'] ), $post, $_GET['new_lang'] );
		}
	}

	/**
	 * Copy the content from one post to the other
	 *
	 * @since 1.9
	 *
	 * @param object        $from_post the post to copy from
	 * @param object        $post      the post to copy to
	 * @param object|string $language  the language of the post to copy to
	 */
	public function copy_content( $from_post, $post, $language ) {
		global $shortcode_tags;

		$this->post_id = $post->ID;
		$this->language = $this->model->get_language( $language );

		if ( ! $from_post || ! $this->language ) {
			return;
		}

		// Hack shortcodes
		$backup = $shortcode_tags;
		$shortcode_tags = array();

		// Add our own shorcode actions
		if ( $this->options['media_support'] ) {
			add_shortcode( 'gallery', array( $this, 'ids_list_shortcode' ) );
			add_shortcode( 'playlist', array( $this, 'ids_list_shortcode' ) );
			add_shortcode( 'caption', array( $this, 'caption_shortcode' ) );
			add_shortcode( 'wp_caption', array( $this, 'caption_shortcode' ) );
		}

		if ( empty( $post->post_title ) ) {
			$post->post_title = $from_post->post_title;
		}

		if ( empty( $post->post_excerpt ) ) {
			$post->post_excerpt = $this->translate( $from_post->post_excerpt );
		}

		if ( empty( $post->post_content ) ) {
			$post->post_content = $this->translate( $from_post->post_content );
		}

		// Get the shorcodes back
		$shortcode_tags = $backup;
	}

	/**
	 * Get the media translation id
	 * Create the translation if it does not exist
	 * Attach the media to the parent post
	 *
	 * @since 1.9
	 *
	 * @param int media id
	 * @return int translated media id
	 */
	public function translate_media( $id ) {
		global $wpdb;

		if ( ! $tr_id = $this->model->post->get( $id, $this->language ) ) {
			$tr_id = $this->filters_media->create_media_translation( $id, $this->language );
		}

		// Attach to the translated post
		if ( ! wp_get_post_parent_id( $tr_id ) ) {
			// Query inspired by wp_media_attach_action()
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID = %d", $this->post_id , $tr_id ) );
			clean_attachment_cache( $tr_id );
		}

		return $tr_id;
	}

	/**
	 * Translates the 'gallery' and 'playlist' shortcodes
	 *
	 * @since 1.9
	 *
	 * @param array  $attr shortcode attribute
	 * @param null   $null
	 * @param string $tag  shortcode tag (either 'gallery' or 'playlist')
	 * @return string translated shortcode
	 */
	function ids_list_shortcode( $attr, $null, $tag ) {
		foreach ( $attr as $k => $v ) {
			if ( 'ids' == $k ) {
				$ids = explode( ',', $v );
				$tr_ids = array();
				foreach ( $ids as $id ) {
					$tr_ids[] = $this->translate_media( $id );
				}
				$v = implode( ',', $tr_ids );
			}
			$out[] = $k . '="' . $v .'"';
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']';
	}

	/**
	 * Translates the caption shortcode
	 * Compatible only with the new style introduced in WP 3.4
	 *
	 * @since 1.9
	 *
	 * @param array  $attr    shortcode attrbute
	 * @param string $content shortcode content
	 * @param string $tag     shortcode tag (either 'caption' or 'wp-caption')
	 * @return string translated shortcode
	 */
	function caption_shortcode( $attr, $content, $tag ) {
		// Translate the caption id
		foreach ( $attr as $k => $v ) {
			if ( 'id' == $k ) {
				$idarr = explode( '_', $v );
				$id = $idarr[1]; // Remember this
				$tr_id = $idarr[1] = $this->translate_media( $id );
				$v = implode( '_', $idarr );
			}
			$out[] = $k . '="' . $v .'"';
		}

		// Translate the caption content
		if ( ! empty( $id ) ) {
			$p = get_post( $id );
			$tr_p = get_post( $tr_id );
			$content = str_replace( $p->post_excerpt, $tr_p->post_excerpt, $content );
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']' . $content . '[/' . $tag . ']';
	}

	/**
	 * Translate shortcodes and <img> attributes in a given text
	 *
	 * @since 1.9
	 *
	 * @param string $content text to translate
	 * @return string translated text
	 */
	public function translate( $content ) {
		$content = do_shortcode( $content ); // translate shorcodes

		// FIXME backward compatibility with WP < 4.2.3
		// Bails as we use functions introduced in WP 4.2.3, 4.1.6 ...
		if ( ! function_exists( 'wp_html_split' ) ) {
			return $content;
		}

		$textarr = wp_html_split( $content ); // Since 4.2.3

		// Translate img class and alternative text
		if ( $this->options['media_support'] ) {
			foreach ( $textarr as $i => $text ) {
				if ( 0 === strpos( $text, '<img' ) ) {
					$textarr[ $i ] = $this->translate_img( $text );
				}
			}
		}

		return implode( $textarr );
	}

	/**
	 * Translates <img> 'class' and 'alt' attributes
	 *
	 * @since 1.9
	 *
	 * @param string $text img attributes
	 * @return string translated attributes
	 */
	public function translate_img( $text ) {
		$attributes = wp_kses_attr_parse( $text ); // since WP 4.2.3

		// Replace class
		foreach ( $attributes as $k => $attr ) {
			if ( 0 === strpos( $attr, 'class' ) ) {
				if ( preg_match( '#wp\-image\-([0-9]+)#', $attr, $matches ) ) {
					$id = $matches[1];
					$tr_id = $this->translate_media( $id );
				}
				$attributes[ $k ] = str_replace( 'wp-image-' . $id, 'wp-image-' . $tr_id, $attr );
			}
		}

		// Got a tr_id, attempt to replace the alt text
		foreach ( $attributes as $k => $attr ) {
			if ( 0 === strpos( $attr, 'alt' ) && $alt = get_post_meta( $tr_id, '_wp_attachment_image_alt', true ) ) {
				$attributes[ $k ] = 'alt="' . esc_attr( $alt ) . '" ';
			}
		}

		return implode( $attributes );
	}
}
