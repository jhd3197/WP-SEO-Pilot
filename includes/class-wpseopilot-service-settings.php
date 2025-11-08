<?php
/**
 * Handles plugin options and settings UI.
 *
 * @package WPSEOPilot
 */

namespace WPSEOPilot\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Settings controller.
 */
class Settings {

	/**
	 * Option keys with defaults.
	 *
	 * @var array<string,mixed>
	 */
	private $defaults = [
		'wpseopilot_default_title_template' => '%post_title% | %site_title%',
		'wpseopilot_post_type_title_templates' => [],
		'wpseopilot_post_type_meta_descriptions' => [],
		'wpseopilot_post_type_keywords' => [],
		'wpseopilot_default_meta_description' => '',
		'wpseopilot_default_og_image' => '',
		'wpseopilot_default_social_width' => 1200,
		'wpseopilot_default_social_height' => 630,
		'wpseopilot_default_noindex' => '0',
		'wpseopilot_default_nofollow' => '0',
		'wpseopilot_global_robots' => 'index, follow',
		'wpseopilot_hreflang_map' => '',
		'wpseopilot_robots_txt' => '',
		'wpseopilot_enable_sitemap_enhancer' => '0',
		'wpseopilot_enable_redirect_manager' => '1',
		'wpseopilot_enable_404_logging' => '0',
	];

	/**
	 * Boot hooks.
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
	}

	/**
	 * Fetch option with fallback.
	 *
	 * @param string $key Key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		$default = $this->defaults[ $key ] ?? '';

		return get_option( $key, $default );
	}

	/**
	 * Register settings + fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		foreach ( $this->defaults as $key => $default ) {
			add_option( $key, $default );
		}

		register_setting( 'wpseopilot', 'wpseopilot_default_title_template', [ $this, 'sanitize_template' ] );
		register_setting( 'wpseopilot', 'wpseopilot_post_type_title_templates', [ $this, 'sanitize_post_type_templates' ] );
		register_setting( 'wpseopilot', 'wpseopilot_post_type_meta_descriptions', [ $this, 'sanitize_post_type_descriptions' ] );
		register_setting( 'wpseopilot', 'wpseopilot_post_type_keywords', [ $this, 'sanitize_post_type_keywords' ] );
		register_setting( 'wpseopilot', 'wpseopilot_default_meta_description', 'sanitize_textarea_field' );
		register_setting( 'wpseopilot', 'wpseopilot_default_og_image', 'esc_url_raw' );
		register_setting( 'wpseopilot', 'wpseopilot_default_social_width', 'absint' );
		register_setting( 'wpseopilot', 'wpseopilot_default_social_height', 'absint' );
		register_setting( 'wpseopilot', 'wpseopilot_default_noindex', [ $this, 'sanitize_bool' ] );
		register_setting( 'wpseopilot', 'wpseopilot_default_nofollow', [ $this, 'sanitize_bool' ] );
		register_setting( 'wpseopilot', 'wpseopilot_global_robots', 'sanitize_text_field' );
		register_setting( 'wpseopilot', 'wpseopilot_hreflang_map', [ $this, 'sanitize_json' ] );
		register_setting( 'wpseopilot', 'wpseopilot_robots_txt', 'sanitize_textarea_field' );
		register_setting( 'wpseopilot', 'wpseopilot_enable_sitemap_enhancer', [ $this, 'sanitize_bool' ] );
		register_setting( 'wpseopilot', 'wpseopilot_enable_redirect_manager', [ $this, 'sanitize_bool' ] );
		register_setting( 'wpseopilot', 'wpseopilot_enable_404_logging', [ $this, 'sanitize_bool' ] );
	}

	/**
	 * Add settings menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'WP SEO Pilot', 'wp-seo-pilot' ),
			__( 'WP SEO Pilot', 'wp-seo-pilot' ),
			'manage_options',
			'wpseopilot',
			[ $this, 'render_settings_page' ],
			'dashicons-airplane',
			58
		);

		add_submenu_page(
			'wpseopilot',
			__( 'SEO Defaults', 'wp-seo-pilot' ),
			__( 'SEO Defaults', 'wp-seo-pilot' ),
			'manage_options',
			'wpseopilot',
			[ $this, 'render_settings_page' ]
		);

		add_submenu_page(
			'wpseopilot',
			__( 'Post Type Defaults', 'wp-seo-pilot' ),
			__( 'Post Type Defaults', 'wp-seo-pilot' ),
			'manage_options',
			'wpseopilot-types',
			[ $this, 'render_post_type_defaults_page' ]
		);
	}

	/**
	 * Sanitize bool-ish values.
	 *
	 * @param mixed $value Value.
	 *
	 * @return string
	 */
	public function sanitize_bool( $value ) {
		return ( ! empty( $value ) ) ? '1' : '0';
	}

	/**
	 * Ensure template placeholders stay safe.
	 *
	 * @param string $value Template.
	 *
	 * @return string
	 */
	public function sanitize_template( $value ) {
		$value = sanitize_text_field( $value );

		$allowed = [
			'%post_title%',
			'%site_title%',
			'%tagline%',
			'%post_author%',
		];

		return str_replace( $allowed, $allowed, $value );
	}

	/**
	 * Sanitize per-post-type template map.
	 *
	 * @param array|string $value Templates.
	 *
	 * @return array
	 */
	public function sanitize_post_type_templates( $value ) {
		return $this->sanitize_post_type_text_map( $value, [ $this, 'sanitize_template' ] );
	}

	/**
	 * Sanitize per-post-type description map.
	 *
	 * @param array|string $value Descriptions.
	 *
	 * @return array
	 */
	public function sanitize_post_type_descriptions( $value ) {
		return $this->sanitize_post_type_text_map( $value, 'sanitize_textarea_field' );
	}

	/**
	 * Sanitize per-post-type keywords map.
	 *
	 * @param array|string $value Keywords.
	 *
	 * @return array
	 */
	public function sanitize_post_type_keywords( $value ) {
		return $this->sanitize_post_type_text_map( $value, 'sanitize_text_field' );
	}

	/**
	 * Shared sanitizer for associative post-type arrays.
	 *
	 * @param array|string $value Value.
	 * @param callable     $callback Sanitizer callback.
	 *
	 * @return array
	 */
	private function sanitize_post_type_text_map( $value, $callback ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$allowed = get_post_types(
			[
				'public'  => true,
				'show_ui' => true,
			]
		);

		unset( $allowed['attachment'] );

		$sanitized = [];

		foreach ( $value as $post_type => $text ) {
			if ( ! isset( $allowed[ $post_type ] ) ) {
				continue;
			}

			$text = call_user_func( $callback, $text );
			if ( '' === $text ) {
				continue;
			}

			$sanitized[ $post_type ] = $text;
		}

		return $sanitized;
	}

	/**
	 * Sanitize JSON stored as text.
	 *
	 * @param string $value JSON.
	 *
	 * @return string
	 */
	public function sanitize_json( $value ) {
		$decoded = json_decode( wp_unslash( $value ), true );

		if ( null === $decoded || ! is_array( $decoded ) ) {
			return '';
		}

		return wp_json_encode( array_map( 'esc_url_raw', $decoded ) );
	}

	/**
	 * Render settings markup.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'wpseopilot-admin',
			WPSEOPILOT_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			WPSEOPILOT_VERSION,
			true
		);

		wp_localize_script(
			'wpseopilot-admin',
			'WPSEOPilotAdmin',
			[
				'mediaTitle'  => __( 'Select default image', 'wp-seo-pilot' ),
				'mediaButton' => __( 'Use image', 'wp-seo-pilot' ),
			]
		);

		wp_enqueue_style(
			'wpseopilot-admin',
			WPSEOPILOT_URL . 'assets/css/admin.css',
			[],
			WPSEOPILOT_VERSION
		);

		include WPSEOPILOT_PATH . 'templates/settings-page.php';
	}

	/**
	 * Render post type defaults page.
	 *
	 * @return void
	 */
	public function render_post_type_defaults_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpseopilot-admin',
			WPSEOPILOT_URL . 'assets/css/admin.css',
			[],
			WPSEOPILOT_VERSION
		);

		$post_types = get_post_types(
			[
				'public'  => true,
				'show_ui' => true,
			],
			'objects'
		);

		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$post_type_templates    = get_option( 'wpseopilot_post_type_title_templates', [] );
		$post_type_descriptions = get_option( 'wpseopilot_post_type_meta_descriptions', [] );
		$post_type_keywords     = get_option( 'wpseopilot_post_type_keywords', [] );

		if ( ! is_array( $post_type_templates ) ) {
			$post_type_templates = [];
		}

		if ( ! is_array( $post_type_descriptions ) ) {
			$post_type_descriptions = [];
		}

		if ( ! is_array( $post_type_keywords ) ) {
			$post_type_keywords = [];
		}

		include WPSEOPILOT_PATH . 'templates/post-type-defaults.php';
	}
}
