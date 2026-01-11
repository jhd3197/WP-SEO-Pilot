<?php
/**
 * Assistants REST Controller
 *
 * @package WPSEOPilot
 * @since 0.2.0
 */

namespace WPSEOPilot\Api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load assistant classes.
require_once __DIR__ . '/Assistants/class-base-assistant.php';
require_once __DIR__ . '/Assistants/class-general-seo-assistant.php';
require_once __DIR__ . '/Assistants/class-seo-reporter-assistant.php';

use WPSEOPilot\Api\Assistants\General_SEO_Assistant;
use WPSEOPilot\Api\Assistants\SEO_Reporter_Assistant;

/**
 * REST API controller for AI assistants.
 */
class Assistants_Controller extends REST_Controller {

    /**
     * Registered built-in assistants.
     *
     * @var array
     */
    private $assistants = [];

    /**
     * Custom models table name.
     *
     * @var string
     */
    private $custom_models_table;

    /**
     * Custom assistants table name.
     *
     * @var string
     */
    private $custom_assistants_table;

    /**
     * Usage tracking table name.
     *
     * @var string
     */
    private $usage_table;

    /**
     * Supported providers.
     *
     * @var array
     */
    private $providers = [
        'openai' => [
            'name'    => 'OpenAI',
            'api_url' => 'https://api.openai.com/v1/chat/completions',
        ],
        'anthropic' => [
            'name'    => 'Anthropic',
            'api_url' => 'https://api.anthropic.com/v1/messages',
        ],
        'google' => [
            'name'    => 'Google AI',
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
        ],
        'openai_compatible' => [
            'name'    => 'OpenAI Compatible',
            'api_url' => '',
        ],
        'lmstudio' => [
            'name'    => 'LM Studio',
            'api_url' => 'http://localhost:1234/v1/chat/completions',
        ],
        'ollama' => [
            'name'    => 'Ollama',
            'api_url' => 'http://localhost:11434/api/chat',
        ],
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->custom_models_table = $wpdb->prefix . 'wpseopilot_custom_models';
        $this->custom_assistants_table = $wpdb->prefix . 'wpseopilot_custom_assistants';
        $this->usage_table = $wpdb->prefix . 'wpseopilot_assistant_usage';

        // Register built-in assistants.
        $this->register_assistant( new General_SEO_Assistant() );
        $this->register_assistant( new SEO_Reporter_Assistant() );
    }

    /**
     * Register an assistant.
     *
     * @param \WPSEOPilot\Api\Assistants\Base_Assistant $assistant Assistant instance.
     */
    public function register_assistant( $assistant ) {
        $this->assistants[ $assistant->get_id() ] = $assistant;
    }

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get all assistants (built-in + custom).
        register_rest_route( $this->namespace, '/assistants', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_assistants' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // Get single assistant.
        register_rest_route( $this->namespace, '/assistants/(?P<id>[a-z0-9-]+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_assistant' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // Chat with assistant.
        register_rest_route( $this->namespace, '/assistants/chat', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'chat' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // Execute assistant action.
        register_rest_route( $this->namespace, '/assistants/action', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'execute_action' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // === Custom Assistants CRUD ===
        register_rest_route( $this->namespace, '/assistants/custom', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_custom_assistants' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_custom_assistant' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/assistants/custom/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_custom_assistant' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_custom_assistant' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_custom_assistant' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        // === Usage Stats ===
        register_rest_route( $this->namespace, '/assistants/stats', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_usage_stats' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/assistants/stats/(?P<id>[a-z0-9-]+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_assistant_stats' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );
    }

    /**
     * Get all available assistants (built-in + custom).
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_assistants( $request ) {
        $assistants = [];

        // Add built-in assistants.
        foreach ( $this->assistants as $assistant ) {
            $assistants[] = [
                'id'               => $assistant->get_id(),
                'name'             => $assistant->get_name(),
                'description'      => $assistant->get_description(),
                'initial_message'  => $assistant->get_initial_message(),
                'suggested_prompts'=> $assistant->get_suggested_prompts(),
                'actions'          => $assistant->get_available_actions(),
                'is_builtin'       => true,
                'color'            => $this->get_builtin_color( $assistant->get_id() ),
                'icon'             => $this->get_builtin_icon( $assistant->get_id() ),
            ];
        }

        // Add custom assistants.
        $custom = $this->get_custom_assistants_list();
        foreach ( $custom as $ca ) {
            if ( $ca['is_active'] ) {
                $assistants[] = [
                    'id'               => 'custom_' . $ca['id'],
                    'name'             => $ca['name'],
                    'description'      => $ca['description'],
                    'initial_message'  => $ca['initial_message'] ?? '',
                    'suggested_prompts'=> json_decode( $ca['suggested_prompts'] ?? '[]', true ),
                    'actions'          => [],
                    'is_builtin'       => false,
                    'is_custom'        => true,
                    'custom_id'        => $ca['id'],
                    'color'            => $ca['color'] ?? '#6366f1',
                    'icon'             => $ca['icon'] ?? '🤖',
                ];
            }
        }

        return $this->success( $assistants );
    }

    /**
     * Get builtin assistant color.
     */
    private function get_builtin_color( $id ) {
        $colors = [
            'general-seo'  => '#3b82f6',
            'seo-reporter' => '#8b5cf6',
        ];
        return $colors[ $id ] ?? '#6366f1';
    }

    /**
     * Get builtin assistant icon.
     */
    private function get_builtin_icon( $id ) {
        $icons = [
            'general-seo'  => '💬',
            'seo-reporter' => '📊',
        ];
        return $icons[ $id ] ?? '🤖';
    }

    /**
     * Get a single assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_assistant( $request ) {
        $id = sanitize_text_field( $request->get_param( 'id' ) );

        if ( ! isset( $this->assistants[ $id ] ) ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $assistant = $this->assistants[ $id ];

        return $this->success( [
            'id'               => $assistant->get_id(),
            'name'             => $assistant->get_name(),
            'description'      => $assistant->get_description(),
            'initial_message'  => $assistant->get_initial_message(),
            'suggested_prompts'=> $assistant->get_suggested_prompts(),
            'actions'          => $assistant->get_available_actions(),
        ] );
    }

    /**
     * Handle chat message.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function chat( $request ) {
        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_params();
        }

        $assistant_id = isset( $params['assistant'] ) ? sanitize_text_field( $params['assistant'] ) : '';
        $message = isset( $params['message'] ) ? sanitize_textarea_field( $params['message'] ) : '';
        $context = isset( $params['context'] ) ? $params['context'] : [];

        if ( empty( $assistant_id ) ) {
            return $this->error( __( 'Assistant ID is required.', 'wp-seo-pilot' ), 'missing_assistant', 400 );
        }

        if ( empty( $message ) ) {
            return $this->error( __( 'Message is required.', 'wp-seo-pilot' ), 'missing_message', 400 );
        }

        // Check if it's a custom assistant.
        if ( strpos( $assistant_id, 'custom_' ) === 0 ) {
            return $this->chat_with_custom_assistant( $assistant_id, $message, $context );
        }

        // Check if it's a built-in assistant.
        if ( ! isset( $this->assistants[ $assistant_id ] ) ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $assistant = $this->assistants[ $assistant_id ];

        // Check if this is an action request.
        if ( ! empty( $context['action'] ) ) {
            $result = $assistant->process_action( $context['action'], $context );
            return $this->success( $result );
        }

        // Build prompt with context.
        $system_prompt = $assistant->get_system_prompt();
        $user_prompt = $assistant->build_prompt( $message, $context );

        // Call AI.
        $response = $this->call_ai( $system_prompt, $user_prompt );

        if ( is_wp_error( $response ) ) {
            return $this->error( $response->get_error_message(), 'ai_error', 500 );
        }

        // Track usage.
        $this->track_usage( $assistant_id );

        // Parse response.
        $parsed = $assistant->parse_response( $response );

        return $this->success( $parsed );
    }

    /**
     * Handle chat with custom assistant.
     *
     * @param string $assistant_id Assistant ID (custom_123 format).
     * @param string $message      User message.
     * @param array  $context      Context data.
     * @return \WP_REST_Response
     */
    private function chat_with_custom_assistant( $assistant_id, $message, $context ) {
        $custom_id = intval( str_replace( 'custom_', '', $assistant_id ) );
        $assistant = $this->get_custom_assistant_by_id( $custom_id );

        if ( ! $assistant ) {
            return $this->error( __( 'Custom assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        if ( ! $assistant['is_active'] ) {
            return $this->error( __( 'This assistant is not active.', 'wp-seo-pilot' ), 'inactive', 400 );
        }

        $system_prompt = $assistant['system_prompt'];
        $user_prompt = $message;

        // Use custom model if specified, otherwise use default.
        $model_id = ! empty( $assistant['model_id'] ) ? $assistant['model_id'] : null;

        // Call AI.
        $response = $this->call_ai( $system_prompt, $user_prompt, $model_id );

        if ( is_wp_error( $response ) ) {
            return $this->error( $response->get_error_message(), 'ai_error', 500 );
        }

        // Track usage.
        $this->track_usage( $assistant_id );

        return $this->success( [
            'message' => $response,
            'actions' => [],
        ] );
    }

    /**
     * Execute an assistant action.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function execute_action( $request ) {
        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_params();
        }

        $assistant_id = isset( $params['assistant'] ) ? sanitize_text_field( $params['assistant'] ) : '';
        $action = isset( $params['action'] ) ? sanitize_text_field( $params['action'] ) : '';
        $context = isset( $params['context'] ) ? $params['context'] : [];

        if ( empty( $assistant_id ) || empty( $action ) ) {
            return $this->error( __( 'Assistant ID and action are required.', 'wp-seo-pilot' ), 'missing_params', 400 );
        }

        if ( ! isset( $this->assistants[ $assistant_id ] ) ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $assistant = $this->assistants[ $assistant_id ];
        $result = $assistant->process_action( $action, $context );

        return $this->success( $result );
    }

    /**
     * Call AI API using configured model.
     *
     * @param string      $system_prompt System prompt.
     * @param string      $user_prompt   User prompt.
     * @param string|null $model_id      Optional model ID to use.
     * @return string|\WP_Error
     */
    private function call_ai( $system_prompt, $user_prompt, $model_id = null ) {
        $model = $model_id ?? get_option( 'wpseopilot_ai_model', 'gpt-4o-mini' );

        // Check if using custom model.
        if ( strpos( $model, 'custom_' ) === 0 ) {
            $custom_id = intval( str_replace( 'custom_', '', $model ) );
            return $this->call_custom_model( $custom_id, $system_prompt, $user_prompt );
        }

        // Use default OpenAI.
        $api_key = get_option( 'wpseopilot_openai_api_key', '' );
        if ( empty( $api_key ) ) {
            return new \WP_Error( 'no_api_key', __( 'No API key configured. Please set up your AI provider in Settings.', 'wp-seo-pilot' ) );
        }

        return $this->call_openai( $api_key, $model, $system_prompt, $user_prompt );
    }

    /**
     * Call OpenAI API.
     *
     * @param string $api_key       API key.
     * @param string $model         Model ID.
     * @param string $system_prompt System prompt.
     * @param string $user_prompt   User prompt.
     * @return string|\WP_Error
     */
    private function call_openai( $api_key, $model, $system_prompt, $user_prompt ) {
        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'    => wp_json_encode( [
                'model'       => $model,
                'messages'    => [
                    [ 'role' => 'system', 'content' => $system_prompt ],
                    [ 'role' => 'user', 'content' => $user_prompt ],
                ],
                'max_tokens'  => 1000,
                'temperature' => 0.7,
            ] ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            $error_message = $body['error']['message'] ?? __( 'API error', 'wp-seo-pilot' );
            return new \WP_Error( 'api_error', $error_message );
        }

        return trim( $body['choices'][0]['message']['content'] ?? '' );
    }

    /**
     * Call custom model.
     *
     * @param int    $custom_id     Custom model ID.
     * @param string $system_prompt System prompt.
     * @param string $user_prompt   User prompt.
     * @return string|\WP_Error
     */
    private function call_custom_model( $custom_id, $system_prompt, $user_prompt ) {
        global $wpdb;

        $model = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->custom_models_table} WHERE id = %d", $custom_id ),
            ARRAY_A
        );

        if ( ! $model ) {
            return new \WP_Error( 'model_not_found', __( 'Custom model not found.', 'wp-seo-pilot' ) );
        }

        if ( ! $model['is_active'] ) {
            return new \WP_Error( 'model_inactive', __( 'Custom model is not active.', 'wp-seo-pilot' ) );
        }

        $provider = $model['provider'];
        $api_url = ! empty( $model['api_url'] ) ? $model['api_url'] : ( $this->providers[ $provider ]['api_url'] ?? '' );
        $api_key = $model['api_key'] ?? '';
        $model_id = $model['model_id'];
        $temperature = floatval( $model['temperature'] ?? 0.7 );
        $max_tokens = intval( $model['max_tokens'] ?? 1000 );

        switch ( $provider ) {
            case 'openai':
            case 'openai_compatible':
            case 'lmstudio':
                return $this->call_openai_compatible( $api_url, $api_key, $model_id, $system_prompt, $user_prompt, $max_tokens, $temperature );

            case 'anthropic':
                return $this->call_anthropic( $api_url, $api_key, $model_id, $system_prompt, $user_prompt, $max_tokens, $temperature );

            case 'google':
                return $this->call_google( $api_url, $api_key, $model_id, $system_prompt, $user_prompt, $max_tokens, $temperature );

            case 'ollama':
                return $this->call_ollama( $api_url, $model_id, $system_prompt, $user_prompt, $max_tokens, $temperature );

            default:
                return new \WP_Error( 'unsupported_provider', __( 'Unsupported provider.', 'wp-seo-pilot' ) );
        }
    }

    /**
     * Call OpenAI-compatible API.
     */
    private function call_openai_compatible( $api_url, $api_key, $model, $system, $prompt, $max_tokens, $temperature ) {
        $headers = [ 'Content-Type' => 'application/json' ];

        if ( ! empty( $api_key ) ) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $response = wp_remote_post( $api_url, [
            'headers' => $headers,
            'body'    => wp_json_encode( [
                'model'       => $model,
                'messages'    => [
                    [ 'role' => 'system', 'content' => $system ],
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
                'max_tokens'  => $max_tokens,
                'temperature' => $temperature,
            ] ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            $error_message = $body['error']['message'] ?? __( 'API error', 'wp-seo-pilot' );
            return new \WP_Error( 'api_error', $error_message );
        }

        return trim( $body['choices'][0]['message']['content'] ?? '' );
    }

    /**
     * Call Anthropic API.
     */
    private function call_anthropic( $api_url, $api_key, $model, $system, $prompt, $max_tokens, $temperature ) {
        $response = wp_remote_post( $api_url, [
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01',
            ],
            'body'    => wp_json_encode( [
                'model'      => $model,
                'max_tokens' => $max_tokens,
                'system'     => $system,
                'messages'   => [
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
            ] ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            $error_message = $body['error']['message'] ?? __( 'Anthropic API error', 'wp-seo-pilot' );
            return new \WP_Error( 'api_error', $error_message );
        }

        return trim( $body['content'][0]['text'] ?? '' );
    }

    /**
     * Call Google AI API.
     */
    private function call_google( $api_url, $api_key, $model, $system, $prompt, $max_tokens, $temperature ) {
        $url = str_replace( '{model}', $model, $api_url ) . '?key=' . $api_key;

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [
                'contents' => [
                    [
                        'parts' => [
                            [ 'text' => $system . "\n\n" . $prompt ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $max_tokens,
                    'temperature'     => $temperature,
                ],
            ] ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            $error_message = $body['error']['message'] ?? __( 'Google AI API error', 'wp-seo-pilot' );
            return new \WP_Error( 'api_error', $error_message );
        }

        return trim( $body['candidates'][0]['content']['parts'][0]['text'] ?? '' );
    }

    /**
     * Call Ollama API.
     */
    private function call_ollama( $api_url, $model, $system, $prompt, $max_tokens, $temperature ) {
        $response = wp_remote_post( $api_url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [
                'model'    => $model,
                'messages' => [
                    [ 'role' => 'system', 'content' => $system ],
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
                'stream'   => false,
                'options'  => [
                    'temperature' => $temperature,
                    'num_predict' => $max_tokens,
                ],
            ] ),
            'timeout' => 120,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            $error_message = $body['error'] ?? __( 'Ollama API error', 'wp-seo-pilot' );
            return new \WP_Error( 'api_error', $error_message );
        }

        return trim( $body['message']['content'] ?? '' );
    }

    // =========================================================================
    // CUSTOM ASSISTANTS CRUD
    // =========================================================================

    /**
     * Get all custom assistants.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_custom_assistants( $request ) {
        $assistants = $this->get_custom_assistants_list();

        // Add usage stats to each assistant.
        foreach ( $assistants as &$assistant ) {
            $assistant['usage'] = $this->get_assistant_usage_count( 'custom_' . $assistant['id'] );
        }

        return $this->success( $assistants );
    }

    /**
     * Get a single custom assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_custom_assistant( $request ) {
        $id = intval( $request->get_param( 'id' ) );
        $assistant = $this->get_custom_assistant_by_id( $id );

        if ( ! $assistant ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $assistant['usage'] = $this->get_assistant_usage_count( 'custom_' . $id );

        return $this->success( $assistant );
    }

    /**
     * Create a custom assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function create_custom_assistant( $request ) {
        global $wpdb;

        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_params();
        }

        if ( empty( $params['name'] ) ) {
            return $this->error( __( 'Name is required.', 'wp-seo-pilot' ), 'missing_name', 400 );
        }

        if ( empty( $params['system_prompt'] ) ) {
            return $this->error( __( 'System prompt is required.', 'wp-seo-pilot' ), 'missing_prompt', 400 );
        }

        // Ensure table exists.
        $this->maybe_create_assistants_table();

        $data = [
            'name'              => sanitize_text_field( $params['name'] ),
            'description'       => sanitize_textarea_field( $params['description'] ?? '' ),
            'system_prompt'     => sanitize_textarea_field( $params['system_prompt'] ),
            'initial_message'   => sanitize_textarea_field( $params['initial_message'] ?? '' ),
            'suggested_prompts' => wp_json_encode( $params['suggested_prompts'] ?? [] ),
            'icon'              => sanitize_text_field( $params['icon'] ?? '🤖' ),
            'color'             => sanitize_hex_color( $params['color'] ?? '#6366f1' ) ?: '#6366f1',
            'model_id'          => sanitize_text_field( $params['model_id'] ?? '' ),
            'is_active'         => isset( $params['is_active'] ) ? ( $params['is_active'] ? 1 : 0 ) : 1,
            'created_at'        => current_time( 'mysql' ),
            'updated_at'        => current_time( 'mysql' ),
        ];

        $result = $wpdb->insert( $this->custom_assistants_table, $data );

        if ( false === $result ) {
            return $this->error( __( 'Failed to create assistant.', 'wp-seo-pilot' ), 'db_error', 500 );
        }

        return $this->success( [ 'id' => $wpdb->insert_id ], __( 'Assistant created successfully.', 'wp-seo-pilot' ) );
    }

    /**
     * Update a custom assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function update_custom_assistant( $request ) {
        global $wpdb;

        $id = intval( $request->get_param( 'id' ) );
        $existing = $this->get_custom_assistant_by_id( $id );

        if ( ! $existing ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_params();
        }

        $data = [ 'updated_at' => current_time( 'mysql' ) ];

        if ( isset( $params['name'] ) ) {
            $data['name'] = sanitize_text_field( $params['name'] );
        }
        if ( isset( $params['description'] ) ) {
            $data['description'] = sanitize_textarea_field( $params['description'] );
        }
        if ( isset( $params['system_prompt'] ) ) {
            $data['system_prompt'] = sanitize_textarea_field( $params['system_prompt'] );
        }
        if ( isset( $params['initial_message'] ) ) {
            $data['initial_message'] = sanitize_textarea_field( $params['initial_message'] );
        }
        if ( isset( $params['suggested_prompts'] ) ) {
            $data['suggested_prompts'] = wp_json_encode( $params['suggested_prompts'] );
        }
        if ( isset( $params['icon'] ) ) {
            $data['icon'] = sanitize_text_field( $params['icon'] );
        }
        if ( isset( $params['color'] ) ) {
            $data['color'] = sanitize_hex_color( $params['color'] ) ?: '#6366f1';
        }
        if ( isset( $params['model_id'] ) ) {
            $data['model_id'] = sanitize_text_field( $params['model_id'] );
        }
        if ( isset( $params['is_active'] ) ) {
            $data['is_active'] = $params['is_active'] ? 1 : 0;
        }

        $wpdb->update( $this->custom_assistants_table, $data, [ 'id' => $id ] );

        return $this->success( null, __( 'Assistant updated successfully.', 'wp-seo-pilot' ) );
    }

    /**
     * Delete a custom assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function delete_custom_assistant( $request ) {
        global $wpdb;

        $id = intval( $request->get_param( 'id' ) );
        $existing = $this->get_custom_assistant_by_id( $id );

        if ( ! $existing ) {
            return $this->error( __( 'Assistant not found.', 'wp-seo-pilot' ), 'not_found', 404 );
        }

        $wpdb->delete( $this->custom_assistants_table, [ 'id' => $id ] );

        return $this->success( null, __( 'Assistant deleted successfully.', 'wp-seo-pilot' ) );
    }

    // =========================================================================
    // USAGE STATS
    // =========================================================================

    /**
     * Get overall usage stats.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_usage_stats( $request ) {
        global $wpdb;

        $this->maybe_create_usage_table();

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->usage_table
        ) );

        if ( ! $table_exists ) {
            return $this->success( [
                'total_messages' => 0,
                'today'          => 0,
                'this_week'      => 0,
                'this_month'     => 0,
                'by_assistant'   => [],
            ] );
        }

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->usage_table}" );
        $today = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE DATE(created_at) = %s",
            current_time( 'Y-m-d' )
        ) );
        $this_week = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE created_at >= %s",
            date( 'Y-m-d', strtotime( '-7 days' ) )
        ) );
        $this_month = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE created_at >= %s",
            date( 'Y-m-01' )
        ) );

        $by_assistant = $wpdb->get_results(
            "SELECT assistant_id, COUNT(*) as count FROM {$this->usage_table} GROUP BY assistant_id ORDER BY count DESC",
            ARRAY_A
        );

        return $this->success( [
            'total_messages' => intval( $total ),
            'today'          => intval( $today ),
            'this_week'      => intval( $this_week ),
            'this_month'     => intval( $this_month ),
            'by_assistant'   => $by_assistant,
        ] );
    }

    /**
     * Get usage stats for a specific assistant.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_assistant_stats( $request ) {
        global $wpdb;

        $assistant_id = sanitize_text_field( $request->get_param( 'id' ) );

        $this->maybe_create_usage_table();

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->usage_table
        ) );

        if ( ! $table_exists ) {
            return $this->success( [
                'assistant_id'   => $assistant_id,
                'total_messages' => 0,
                'today'          => 0,
                'this_week'      => 0,
                'this_month'     => 0,
            ] );
        }

        $total = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE assistant_id = %s",
            $assistant_id
        ) );
        $today = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE assistant_id = %s AND DATE(created_at) = %s",
            $assistant_id,
            current_time( 'Y-m-d' )
        ) );
        $this_week = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE assistant_id = %s AND created_at >= %s",
            $assistant_id,
            date( 'Y-m-d', strtotime( '-7 days' ) )
        ) );

        return $this->success( [
            'assistant_id'   => $assistant_id,
            'total_messages' => intval( $total ),
            'today'          => intval( $today ),
            'this_week'      => intval( $this_week ),
        ] );
    }

    /**
     * Track assistant usage.
     *
     * @param string $assistant_id Assistant ID.
     * @param int    $tokens_used  Estimated tokens used.
     */
    private function track_usage( $assistant_id, $tokens_used = 0 ) {
        global $wpdb;

        $this->maybe_create_usage_table();

        $wpdb->insert( $this->usage_table, [
            'assistant_id' => $assistant_id,
            'user_id'      => get_current_user_id(),
            'tokens_used'  => $tokens_used,
            'created_at'   => current_time( 'mysql' ),
        ] );
    }

    /**
     * Get usage count for an assistant.
     *
     * @param string $assistant_id Assistant ID.
     * @return int
     */
    private function get_assistant_usage_count( $assistant_id ) {
        global $wpdb;

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->usage_table
        ) );

        if ( ! $table_exists ) {
            return 0;
        }

        return intval( $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE assistant_id = %s",
            $assistant_id
        ) ) );
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get custom assistants list.
     *
     * @return array
     */
    private function get_custom_assistants_list() {
        global $wpdb;

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->custom_assistants_table
        ) );

        if ( ! $table_exists ) {
            return [];
        }

        return $wpdb->get_results(
            "SELECT * FROM {$this->custom_assistants_table} ORDER BY created_at DESC",
            ARRAY_A
        ) ?? [];
    }

    /**
     * Get custom assistant by ID.
     *
     * @param int $id Assistant ID.
     * @return array|null
     */
    private function get_custom_assistant_by_id( $id ) {
        global $wpdb;

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->custom_assistants_table
        ) );

        if ( ! $table_exists ) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->custom_assistants_table} WHERE id = %d", $id ),
            ARRAY_A
        );
    }

    /**
     * Create custom assistants table.
     */
    private function maybe_create_assistants_table() {
        global $wpdb;

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->custom_assistants_table
        ) );

        if ( $table_exists ) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->custom_assistants_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            system_prompt longtext NOT NULL,
            initial_message text,
            suggested_prompts longtext,
            icon varchar(50) DEFAULT '🤖',
            color varchar(20) DEFAULT '#6366f1',
            model_id varchar(255) DEFAULT '',
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY is_active (is_active)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Create usage tracking table.
     */
    private function maybe_create_usage_table() {
        global $wpdb;

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->usage_table
        ) );

        if ( $table_exists ) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->usage_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            assistant_id varchar(100) NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            tokens_used int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY assistant_id (assistant_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
