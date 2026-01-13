<?php
/**
 * REST API Controller for IndexNow.
 *
 * @package WPSEOPilot
 */

namespace WPSEOPilot\Api;

use WPSEOPilot\Plugin;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * IndexNow REST API Controller.
 */
class IndexNow_Controller extends REST_Controller {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Settings endpoints.
		register_rest_route(
			$this->namespace,
			'/indexnow/settings',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_settings' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
			]
		);

		// Generate new API key.
		register_rest_route(
			$this->namespace,
			'/indexnow/generate-key',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'generate_key' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
			]
		);

		// Verify key file.
		register_rest_route(
			$this->namespace,
			'/indexnow/verify-key',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'verify_key' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
			]
		);

		// Submit URL(s).
		register_rest_route(
			$this->namespace,
			'/indexnow/submit',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'submit_urls' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args'                => [
						'urls' => [
							'required'          => true,
							'type'              => 'array',
							'sanitize_callback' => function ( $urls ) {
								return array_map( 'esc_url_raw', (array) $urls );
							},
						],
					],
				],
			]
		);

		// Get submission logs.
		register_rest_route(
			$this->namespace,
			'/indexnow/logs',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_logs' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args'                => [
						'page'     => [
							'type'              => 'integer',
							'default'           => 1,
							'sanitize_callback' => 'absint',
						],
						'per_page' => [
							'type'              => 'integer',
							'default'           => 50,
							'sanitize_callback' => 'absint',
						],
						'status'   => [
							'type'              => 'string',
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'search'   => [
							'type'              => 'string',
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'clear_logs' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args'                => [
						'days' => [
							'type'              => 'integer',
							'default'           => 0,
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);

		// Get stats.
		register_rest_route(
			$this->namespace,
			'/indexnow/stats',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_stats' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
			]
		);

		// Get options (search engines, post types).
		register_rest_route(
			$this->namespace,
			'/indexnow/options',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_options' ],
					'permission_callback' => [ $this, 'permission_check' ],
				],
			]
		);
	}

	/**
	 * Get IndexNow service.
	 *
	 * @return \WPSEOPilot\Service\IndexNow|null
	 */
	private function get_service() {
		return Plugin::instance()->get( 'indexnow' );
	}

	/**
	 * Get IndexNow settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_settings( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$settings = $service->get_settings();

		// Mask API key for display (show first 8 and last 4 chars).
		if ( ! empty( $settings['api_key'] ) ) {
			$key                     = $settings['api_key'];
			$settings['api_key_display'] = substr( $key, 0, 8 ) . '...' . substr( $key, -4 );
		}

		return $this->success( $settings );
	}

	/**
	 * Save IndexNow settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function save_settings( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$settings = $request->get_json_params();

		if ( empty( $settings ) ) {
			$settings = $request->get_params();
		}

		$result = $service->save_settings( $settings );

		if ( $result ) {
			return $this->success(
				$service->get_settings(),
				__( 'IndexNow settings saved successfully.', 'wp-seo-pilot' )
			);
		}

		return $this->error( __( 'Failed to save settings.', 'wp-seo-pilot' ) );
	}

	/**
	 * Generate a new API key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function generate_key( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$settings            = $service->get_settings();
		$settings['api_key'] = $service->generate_api_key();

		$service->save_settings( $settings );

		// Flush rewrite rules to ensure new key file route works.
		flush_rewrite_rules();

		return $this->success(
			[
				'api_key'     => $settings['api_key'],
				'key_file_url' => home_url( $settings['api_key'] . '.txt' ),
			],
			__( 'New API key generated successfully.', 'wp-seo-pilot' )
		);
	}

	/**
	 * Verify the API key file is accessible.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function verify_key( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$result = $service->verify_key_file();

		return $this->success( $result );
	}

	/**
	 * Submit URLs to IndexNow.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function submit_urls( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$urls = $request->get_param( 'urls' );

		if ( empty( $urls ) ) {
			return $this->error( __( 'No URLs provided.', 'wp-seo-pilot' ) );
		}

		// Filter out invalid URLs.
		$urls = array_filter( $urls, function ( $url ) {
			return filter_var( $url, FILTER_VALIDATE_URL );
		} );

		if ( empty( $urls ) ) {
			return $this->error( __( 'No valid URLs provided.', 'wp-seo-pilot' ) );
		}

		$settings = $service->get_settings();

		if ( empty( $settings['api_key'] ) ) {
			return $this->error( __( 'No API key configured. Please generate an API key first.', 'wp-seo-pilot' ) );
		}

		$success = $service->submit_urls( $urls );

		if ( $success ) {
			return $this->success(
				[
					'submitted' => count( $urls ),
					'urls'      => $urls,
				],
				sprintf(
					/* translators: %d: number of URLs */
					_n(
						'%d URL submitted successfully.',
						'%d URLs submitted successfully.',
						count( $urls ),
						'wp-seo-pilot'
					),
					count( $urls )
				)
			);
		}

		return $this->error( __( 'Failed to submit URLs. Check the submission logs for details.', 'wp-seo-pilot' ) );
	}

	/**
	 * Get submission logs.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_logs( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$logs = $service->get_logs( [
			'page'     => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
			'status'   => $request->get_param( 'status' ),
			'search'   => $request->get_param( 'search' ),
		] );

		return $this->success( $logs );
	}

	/**
	 * Clear submission logs.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function clear_logs( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		$days    = $request->get_param( 'days' );
		$deleted = $service->clear_logs( $days );

		return $this->success(
			[ 'deleted' => $deleted ],
			sprintf(
				/* translators: %d: number of entries */
				_n(
					'%d log entry cleared.',
					'%d log entries cleared.',
					$deleted,
					'wp-seo-pilot'
				),
				$deleted
			)
		);
	}

	/**
	 * Get submission statistics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_stats( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		return $this->success( $service->get_stats() );
	}

	/**
	 * Get IndexNow options (search engines, post types).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_options( $request ) {
		$service = $this->get_service();

		if ( ! $service ) {
			return $this->error( __( 'IndexNow service not available.', 'wp-seo-pilot' ) );
		}

		// Get public post types.
		$post_types       = [];
		$public_post_types = get_post_types( [ 'public' => true ], 'objects' );
		foreach ( $public_post_types as $pt ) {
			if ( 'attachment' === $pt->name ) {
				continue;
			}
			$post_types[] = [
				'name'  => $pt->name,
				'label' => $pt->labels->name,
			];
		}

		return $this->success( [
			'search_engines' => $service->get_search_engines(),
			'post_types'     => $post_types,
		] );
	}
}
