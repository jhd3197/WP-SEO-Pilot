<?php
/**
 * Settings REST Controller
 *
 * @package WPSEOPilot
 * @since 0.2.0
 */

namespace WPSEOPilot\Api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST controller for plugin settings.
 */
class Settings_Controller extends REST_Controller {

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get/Update all settings
        register_rest_route( $this->namespace, '/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_settings' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'update_settings' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // Get/Update single setting
        register_rest_route( $this->namespace, '/settings/(?P<key>[a-zA-Z0-9_-]+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_setting' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update_setting' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );
    }

    /**
     * Get all plugin settings.
     *
     * @return \WP_REST_Response
     */
    public function get_settings() {
        global $wpdb;

        $options = $wpdb->get_results(
            "SELECT option_name, option_value
             FROM {$wpdb->options}
             WHERE option_name LIKE 'wpseopilot_%'"
        );

        $settings = [];
        foreach ( $options as $opt ) {
            $key = str_replace( 'wpseopilot_', '', $opt->option_name );
            $settings[ $key ] = maybe_unserialize( $opt->option_value );
        }

        return $this->success( $settings );
    }

    /**
     * Get a single setting.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_setting( $request ) {
        $key   = $request->get_param( 'key' );
        $value = get_option( 'wpseopilot_' . $key );

        return $this->success( [
            'key'   => $key,
            'value' => $value,
        ] );
    }

    /**
     * Update multiple settings.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function update_settings( $request ) {
        $settings = $request->get_json_params();

        foreach ( $settings as $key => $value ) {
            update_option( 'wpseopilot_' . $key, $value );
        }

        // Sync breadcrumb settings to consolidated option for the service.
        $this->sync_breadcrumb_settings( $settings );

        // Sync IndexNow settings to consolidated option for the service.
        $this->sync_indexnow_settings( $settings );

        return $this->success( null, __( 'Settings saved successfully.', 'wp-seo-pilot' ) );
    }

    /**
     * Sync breadcrumb settings to the consolidated option.
     *
     * @param array $settings All settings from request.
     * @return void
     */
    private function sync_breadcrumb_settings( $settings ) {
        $breadcrumb_keys = [
            'breadcrumb_separator'        => 'separator',
            'breadcrumb_separator_custom' => 'separator_custom',
            'breadcrumb_show_home'        => 'show_home',
            'breadcrumb_home_label'       => 'home_label',
            'breadcrumb_show_current'     => 'show_current',
            'breadcrumb_link_current'     => 'link_current',
            'breadcrumb_truncate_length'  => 'truncate_length',
            'breadcrumb_show_on_front'    => 'show_on_front',
            'breadcrumb_style_preset'     => 'style_preset',
            'module_breadcrumbs'          => 'enabled',
        ];

        $breadcrumb_settings = get_option( 'wpseopilot_breadcrumb_settings', [] );
        $updated             = false;

        foreach ( $breadcrumb_keys as $request_key => $service_key ) {
            if ( isset( $settings[ $request_key ] ) ) {
                $breadcrumb_settings[ $service_key ] = $settings[ $request_key ];
                $updated = true;
            }
        }

        if ( $updated ) {
            update_option( 'wpseopilot_breadcrumb_settings', $breadcrumb_settings );
        }
    }

    /**
     * Sync IndexNow settings to the consolidated option.
     *
     * @param array $settings All settings from request.
     * @return void
     */
    private function sync_indexnow_settings( $settings ) {
        $indexnow_keys = [
            'module_indexnow'              => 'enabled',
            'indexnow_submit_on_publish'   => 'submit_on_publish',
            'indexnow_submit_on_update'    => 'submit_on_update',
        ];

        $indexnow_settings = get_option( 'wpseopilot_indexnow_settings', [] );
        $updated           = false;

        foreach ( $indexnow_keys as $request_key => $service_key ) {
            if ( isset( $settings[ $request_key ] ) ) {
                $indexnow_settings[ $service_key ] = $settings[ $request_key ];
                $updated = true;
            }
        }

        // Generate API key when enabling IndexNow for the first time.
        if ( $updated && ! empty( $indexnow_settings['enabled'] ) && empty( $indexnow_settings['api_key'] ) ) {
            $indexnow_service = \WPSEOPilot\Plugin::instance()->get( 'indexnow' );
            if ( $indexnow_service ) {
                $indexnow_settings['api_key'] = $indexnow_service->generate_api_key();
                flush_rewrite_rules();
            }
        }

        if ( $updated ) {
            update_option( 'wpseopilot_indexnow_settings', $indexnow_settings );
        }
    }

    /**
     * Update a single setting.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function update_setting( $request ) {
        $key   = $request->get_param( 'key' );
        $body  = $request->get_json_params();
        $value = isset( $body['value'] ) ? $body['value'] : null;

        update_option( 'wpseopilot_' . $key, $value );

        return $this->success( [
            'key'   => $key,
            'value' => $value,
        ], __( 'Setting saved.', 'wp-seo-pilot' ) );
    }
}
