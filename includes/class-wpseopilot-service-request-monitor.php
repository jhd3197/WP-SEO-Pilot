<?php
/**
 * Logs 404s and surfaces suggestions.
 *
 * @package WPSEOPilot
 */

namespace WPSEOPilot\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Request monitoring service.
 */
class Request_Monitor {

	private const SCHEMA_VERSION = 4;
	private const SCHEMA_OPTION  = 'wpseopilot_404_log_schema';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpseopilot_404_log';
	}

	/**
	 * Boot hooks.
	 *
	 * @return void
	 */
	public function boot() {
		$this->maybe_upgrade_schema();

		if ( false === get_option( 'wpseopilot_enable_404_logging', false ) ) {
			add_option( 'wpseopilot_enable_404_logging', '1' );
		}

		if ( '1' !== get_option( 'wpseopilot_enable_404_logging', '1' ) ) {
			return;
		}

		add_action( 'template_redirect', [ $this, 'maybe_log_404' ] );
		add_action( 'admin_menu', [ $this, 'register_page' ] );
	}

	/**
	 * Create required tables.
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$this->table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			request_uri varchar(255) NOT NULL,
			user_agent varchar(255) DEFAULT '',
			device_label varchar(80) DEFAULT '',
			hits bigint(20) unsigned NOT NULL DEFAULT 1,
			last_seen datetime NOT NULL,
			PRIMARY KEY (id),
			KEY request_uri (request_uri)
		) {$charset};";

		dbDelta( $sql );

		update_option( self::SCHEMA_OPTION, (string) self::SCHEMA_VERSION );

	}

	/**
	 * Ensure the custom 404 table schema is current.
	 *
	 * @return void
	 */
	private function maybe_upgrade_schema() {
		$current = (int) get_option( self::SCHEMA_OPTION, 0 );

		if ( $current < self::SCHEMA_VERSION ) {
			$this->create_tables();
			return;
		}

		if ( ! $this->has_column( 'device_label' ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema corrections require direct queries.
			$wpdb->query( "ALTER TABLE {$this->table} ADD COLUMN device_label varchar(80) DEFAULT ''" );
		}
	}

	/**
	 * Register admin report page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'wpseopilot',
			__( '404 Error Log', 'wp-seo-pilot' ),
			__( '404 Error Log', 'wp-seo-pilot' ),
			'manage_options',
			'wpseopilot-404',
			[ $this, 'render_page' ],
			12
		);
	}

	/**
	 * Render 404 report.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sort     = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'recent';
		$per_page = isset( $_GET['per_page'] ) ? max( 1, min( 200, absint( $_GET['per_page'] ) ) ) : 50;
		$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;

		if ( ! in_array( $sort, [ 'recent', 'top' ], true ) ) {
			$sort = 'recent';
		}

		$order_by = ( 'top' === $sort ) ? 'hits' : 'last_seen';

		$offset = ( $page - 1 ) * $per_page;

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom ordering/pagination requires direct queries.
		$total_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom ordering/pagination requires direct queries.
		$order_by_sql = ( 'hits' === $order_by ) ? 'hits' : 'last_seen';
		$sql          = $wpdb->prepare(
			"SELECT * FROM {$this->table} ORDER BY {$order_by_sql} DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom ordering/pagination requires direct queries.
		$rows = $wpdb->get_results( $sql );

		if ( $rows ) {
			$rows = $this->hydrate_device_labels( $rows );
		}

		$total_pages = max( 1, (int) ceil( $total_count / $per_page ) );
		$base_url    = menu_page_url( 'wpseopilot-404', false );

		include WPSEOPILOT_PATH . 'templates/404-log.php';
	}

	/**
	 * Maybe log a 404 event.
	 *
	 * @return void
	 */
	public function maybe_log_404() {
		if ( ! is_404() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$request     = wp_parse_url( $request_uri, PHP_URL_PATH );
		if ( $request ) {
			$request = sanitize_text_field( $request );
		} else {
			$request = '/';
		}
		if ( '/' !== $request ) {
			$request = rtrim( $request, '/' );
			if ( '' === $request ) {
				$request = '/';
			}
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$device     = $this->describe_device_from_user_agent( $user_agent );
		$now        = current_time( 'mysql' );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Each request URI must be checked against the custom 404 log table directly.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT id, hits, user_agent FROM ' . $this->table . ' WHERE request_uri = %s LIMIT 1',
				$request
			)
		);

		if ( $row ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Updating the custom 404 log table requires a direct query.
			$wpdb->update(
				$this->table,
				[
					'hits'         => (int) $row->hits + 1,
					'last_seen'    => $now,
					'user_agent'   => $user_agent ?: $row->user_agent,
					'device_label' => $device,
				],
				[ 'id' => $row->id ]
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Inserting new 404 rows requires writing to the custom table directly.
			$wpdb->insert(
				$this->table,
				[
					'request_uri'  => $request,
					'user_agent'   => $user_agent,
					'device_label' => $device,
					'hits'         => 1,
					'last_seen'    => $now,
				],
				[ '%s', '%s', '%s', '%d', '%s' ]
			);
		}
	}

	/**
	 * Build a simplified device label from a user agent string.
	 *
	 * @param string $user_agent Raw user agent string.
	 * @return string
	 */
	private function describe_device_from_user_agent( $user_agent ) {
		if ( empty( $user_agent ) ) {
			return __( 'Unknown device', 'wp-seo-pilot' );
		}

		$browser_label  = $this->detect_browser_label( $user_agent );
		$platform_label = $this->detect_platform_label( $user_agent );

		$parts = array_filter( [ $platform_label, $browser_label ] );

		if ( ! empty( $parts ) ) {
			return trim( implode( ' ', $parts ) );
		}

		return substr( $user_agent, 0, 80 );
	}

	/**
	 * Ensure device labels are present for the provided rows, backfilling as needed.
	 *
	 * @param array<int,\stdClass> $rows Log rows.
	 * @return array<int,\stdClass>
	 */
	private function hydrate_device_labels( $rows ) {
		global $wpdb;

		foreach ( $rows as $row ) {
			if ( ! empty( $row->device_label ) ) {
				continue;
			}

			$label = $this->describe_device_from_user_agent( $row->user_agent ?? '' );

			if ( empty( $label ) || empty( $row->id ) ) {
				continue;
			}

			$row->device_label = $label;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Backfilling derived column requires direct updates.
			$wpdb->update(
				$this->table,
				[ 'device_label' => $label ],
				[ 'id' => (int) $row->id ],
				[ '%s' ],
				[ '%d' ]
			);
		}

		return $rows;
	}

	/**
	 * Detect browser name using the bundled UA parser fallback.
	 *
	 * @param string $user_agent Raw user agent.
	 * @return string
	 */
	private function detect_browser_label( $user_agent ) {
		if ( $this->load_browser_parser() ) {
			try {
				$result = \useragent_detect_browser::analyze( $user_agent );
			} catch ( \Throwable $e ) {
				$result = null;
			}

			if ( is_array( $result ) ) {
				if ( ! empty( $result['title'] ) ) {
					return trim( $result['title'] );
				}

				if ( ! empty( $result['name'] ) ) {
					return trim( $result['name'] );
				}
			}
		}

		if ( preg_match( '/^([A-Za-z0-9\\-\\.]+)(?:\\/[0-9A-Za-z\\.]+)?/i', $user_agent, $matches ) ) {
			$token = $matches[1];

			if ( stripos( $token, 'mozilla' ) !== 0 ) {
				return $token;
			}
		}

		return '';
	}

	/**
	 * Attempt to determine the platform/OS from the user agent.
	 *
	 * @param string $user_agent Raw user agent.
	 * @return string
	 */
	private function detect_platform_label( $user_agent ) {
		$ua = strtolower( $user_agent );

		$map = [
			'windows phone' => 'Windows Phone',
			'windows nt 10' => 'Windows 10',
			'windows nt 6.3' => 'Windows 8.1',
			'windows nt 6.2' => 'Windows 8',
			'windows nt 6.1' => 'Windows 7',
			'windows nt 6.0' => 'Windows Vista',
			'windows nt 5.1' => 'Windows XP',
			'android'       => 'Android',
			'iphone'        => 'iPhone',
			'ipad'          => 'iPad',
			'ipod'          => 'iPod',
			'mac os x'      => 'macOS',
			'macintosh'     => 'macOS',
			'cros'          => 'Chrome OS',
			'linux'         => 'Linux',
			'bb10'          => 'BlackBerry',
			'blackberry'    => 'BlackBerry',
			'playstation'   => 'PlayStation',
			'nintendo'      => 'Nintendo',
			'xbox'          => 'Xbox',
			'go-http-client'=> 'Go-http-client',
		];

		foreach ( $map as $needle => $label ) {
			if ( false !== strpos( $ua, $needle ) ) {
				return $label;
			}
		}

		return '';
	}

	/**
	 * Load the third-party browser detection class if needed.
	 *
	 * @return bool
	 */
	private function load_browser_parser() {
		if ( class_exists( '\useragent_detect_browser' ) ) {
			return true;
		}

		$parser = WPSEOPILOT_PATH . 'includes/useragent_detect_browser.php';

		if ( file_exists( $parser ) ) {
			require_once $parser;
		}

		return class_exists( '\useragent_detect_browser' );
	}

	/**
	 * Whether the custom 404 table contains a specific column.
	 *
	 * @param string $column Column name.
	 * @return bool
	 */
	private function has_column( $column ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Inspecting table schema requires a direct query.
		$existing = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) );

		return ! empty( $existing );
	}

}
