<?php
/**
 * Health Check REST API
 * Provides comprehensive health check endpoints for plugin monitoring
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * REST API endpoint for plugin health checks
 */
class HT_Health_Check_API
{
    /**
     * Register REST API endpoints
     *
     * @return void
     */
    public function register_endpoints(): void
    {
        try {
            // Health check endpoint - public for monitoring tools
            register_rest_route('homaye/v1', '/health', [
                'methods' => 'GET',
                'callback' => [$this, 'health_check'],
                'permission_callback' => '__return_true', // Public endpoint
            ]);

            // Detailed health check - requires admin privileges
            register_rest_route('homaye/v1', '/health/detailed', [
                'methods' => 'GET',
                'callback' => [$this, 'detailed_health_check'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

            // API endpoints status - requires admin privileges
            register_rest_route('homaye/v1', '/health/endpoints', [
                'methods' => 'GET',
                'callback' => [$this, 'check_endpoints'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

        } catch (\Throwable $e) {
            HT_Error_Handler::log_exception($e, 'health_api_registration');
        }
    }

    /**
     * Basic health check endpoint
     * Returns simple status for uptime monitoring
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function health_check(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            global $wpdb;

            // Check database connectivity
            $db_check = $wpdb->get_var("SELECT 1");
            
            // Check critical table existence
            $critical_tables = [
                'homaye_persona_scores',
                'homa_sessions',
            ];
            
            $tables_ok = true;
            foreach ($critical_tables as $table) {
                $table_name = $wpdb->prefix . $table;
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                    $tables_ok = false;
                    break;
                }
            }

            $status = 'healthy';
            if (!$db_check || !$tables_ok) {
                $status = 'degraded';
            }

            return new \WP_REST_Response([
                'status' => $status,
                'timestamp' => current_time('mysql'),
                'version' => HT_VERSION,
                'database' => $db_check ? 'ok' : 'error',
                'tables' => $tables_ok ? 'ok' : 'missing',
            ], 200);

        } catch (\Throwable $e) {
            HT_Error_Handler::log_exception($e, 'health_check_endpoint');
            
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Detailed health check with comprehensive diagnostics
     * Requires admin privileges
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function detailed_health_check(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            if (!class_exists('\HomayeTabesh\HT_Activator')) {
                return new \WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Activator class not found',
                ], 500);
            }

            $report = HT_Activator::run_health_check();
            
            return new \WP_REST_Response($report, 200);

        } catch (\Throwable $e) {
            HT_Error_Handler::log_exception($e, 'detailed_health_check');
            
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Detailed health check failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status of all registered REST API endpoints
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function check_endpoints(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $expected_endpoints = [
                '/homaye/v1/health' => [
                    'description' => 'Basic health check',
                    'public' => true,
                ],
                '/homaye/v1/health/detailed' => [
                    'description' => 'Detailed health diagnostics',
                    'public' => false,
                ],
                '/homaye/v1/health/endpoints' => [
                    'description' => 'API endpoints status',
                    'public' => false,
                ],
                '/homaye/v1/chat' => [
                    'description' => 'AI chat interface',
                    'public' => false,
                ],
                '/homaye/v1/telemetry' => [
                    'description' => 'User behavior tracking',
                    'public' => false,
                ],
                '/homaye/v1/lead' => [
                    'description' => 'Lead management',
                    'public' => false,
                ],
                '/homaye/v1/vault/store' => [
                    'description' => 'Omni-Store vault storage',
                    'public' => false,
                ],
                '/homaye/v1/atlas/insights' => [
                    'description' => 'Atlas analytics insights',
                    'public' => false,
                ],
            ];

            $rest_server = rest_get_server();
            $registered_routes = $rest_server->get_routes();
            
            $endpoint_status = [];
            foreach ($expected_endpoints as $route => $details) {
                $is_registered = isset($registered_routes[$route]);
                $endpoint_status[] = [
                    'route' => $route,
                    'description' => $details['description'],
                    'public' => $details['public'],
                    'registered' => $is_registered,
                    'status' => $is_registered ? 'active' : 'missing',
                ];
            }

            $active_count = count(array_filter($endpoint_status, function($ep) {
                return $ep['registered'];
            }));
            $total_count = count($endpoint_status);

            return new \WP_REST_Response([
                'status' => 'ok',
                'summary' => [
                    'active' => $active_count,
                    'total' => $total_count,
                    'percentage' => round(($active_count / $total_count) * 100, 2),
                ],
                'endpoints' => $endpoint_status,
                'timestamp' => current_time('mysql'),
            ], 200);

        } catch (\Throwable $e) {
            HT_Error_Handler::log_exception($e, 'check_endpoints');
            
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Endpoint check failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
