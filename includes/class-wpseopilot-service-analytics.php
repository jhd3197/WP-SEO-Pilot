<?php

namespace WPSEOPilot\Service;

defined( 'ABSPATH' ) || exit;

class Analytics {

	private $matomo_url = 'https://matomo.builditdesign.com/matomo.php';
	private $site_id = 1;
	private $enabled = true;

	public function boot() {
		add_action( 'admin_init', [ $this, 'track_admin_page_view' ] );
	}

	public function is_enabled() {
		return apply_filters( 'wpseopilot_analytics_enabled', $this->enabled );
	}

	public static function track_activation() {
		$analytics = new self();
		$analytics->send_event( 'plugin_activate' );
	}

	public function track_admin_page_view() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		$page = sanitize_text_field( $_GET['page'] );

		if ( strpos( $page, 'wpseopilot' ) === false && $page !== 'wp-seo-pilot' ) {
			return;
		}

		$transient_key = 'wpseopilot_analytics_page_' . md5( $page . get_current_user_id() );

		if ( get_transient( $transient_key ) ) {
			return;
		}

		set_transient( $transient_key, 1, HOUR_IN_SECONDS );

		$this->send_event( 'admin_page_view', admin_url( 'admin.php?page=' . $page ) );
	}

	public function track_feature( $feature_name ) {
		$this->send_event( $feature_name );
	}

	public function test_tracking() {
		$params = [
			'idsite'      => $this->site_id,
			'rec'         => 1,
			'apiv'        => 1,
			'action_name' => 'test_event',
			'url'         => home_url(),
			'rand'        => wp_rand( 100000, 999999 ),
			'_id'         => substr( md5( home_url() ), 0, 16 ),
			'bots'        => 1,
		];

		$tracking_url = add_query_arg( $params, $this->matomo_url );

		$response = wp_remote_get(
			$tracking_url,
			[
				'timeout'     => 10,
				'blocking'    => true,
				'httpversion' => '1.1',
				'sslverify'   => false,
			]
		);

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
				'url'     => $tracking_url,
			];
		}

		$body = wp_remote_retrieve_body( $response );

		return [
			'success'  => true,
			'code'     => wp_remote_retrieve_response_code( $response ),
			'url'      => $tracking_url,
			'body'     => $body,
			'headers'  => wp_remote_retrieve_headers( $response ),
		];
	}

	private function send_event( $action_name, $url = null ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $url === null ) {
			$url = home_url();
		}

		$params = [
			'idsite'      => $this->site_id,
			'rec'         => 1,
			'apiv'        => 1,
			'action_name' => $action_name,
			'url'         => $url,
			'rand'        => wp_rand( 100000, 999999 ),
			'_id'         => substr( md5( home_url() ), 0, 16 ),
			'bots'        => 1,
		];

		$tracking_url = add_query_arg( $params, $this->matomo_url );

		$response = wp_remote_get(
			$tracking_url,
			[
				'timeout'     => 5,
				'blocking'    => false,
				'httpversion' => '1.1',
				'sslverify'   => false,
			]
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( is_wp_error( $response ) ) {
				error_log( 'WP SEO Pilot Analytics Error: ' . $response->get_error_message() );
			} else {
				error_log( 'WP SEO Pilot Analytics Sent: ' . $tracking_url );
			}
		}
	}
}
