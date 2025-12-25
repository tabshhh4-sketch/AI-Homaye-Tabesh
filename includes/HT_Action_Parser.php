<?php
/**
 * Action Parser
 * Parses AI responses and extracts UI actions
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * موتور تجزیه پاسخ‌های AI
 * تشخیص بخش‌های پاسخ و دستورات فنی
 */
class HT_Action_Parser
{
    /**
     * Allowed action types
     */
    private const ALLOWED_ACTIONS = [
        'highlight_element',
        'show_tooltip',
        'scroll_to',
        'open_modal',
        'update_calculator',
        'suggest_product',
        'show_discount',
        'change_css',
        'redirect',
        'none',
    ];

    /**
     * Parse AI response
     *
     * @param array $ai_response Raw AI response
     * @return array Parsed response with actions
     */
    public function parse_response(array $ai_response): array
    {
        if (!isset($ai_response['success']) || !$ai_response['success']) {
            return $this->get_error_response($ai_response['error'] ?? 'Unknown error');
        }

        // Check if response has structured data
        if (isset($ai_response['data']) && is_array($ai_response['data'])) {
            return $this->parse_structured_response($ai_response['data']);
        }

        // Try to parse raw text
        if (isset($ai_response['raw_text'])) {
            return $this->parse_text_response($ai_response['raw_text']);
        }

        return $this->get_error_response('Invalid response format');
    }

    /**
     * Parse structured JSON response
     *
     * @param array $data Structured data
     * @return array Parsed response
     */
    private function parse_structured_response(array $data): array
    {
        $parsed = [
            'success' => true,
            'thought' => $data['thought'] ?? '',
            'response' => $data['response'] ?? '',
            'action' => null,
            'persona_update' => $data['persona_update'] ?? null,
        ];

        // Parse action if present
        if (!empty($data['action']) && $this->is_valid_action($data['action'])) {
            $parsed['action'] = [
                'type' => $data['action'],
                'target' => $data['target'] ?? null,
                'data' => $data['data'] ?? [],
            ];
        }

        return $parsed;
    }

    /**
     * Parse text response (fallback)
     *
     * @param string $text Raw text response
     * @return array Parsed response
     */
    private function parse_text_response(string $text): array
    {
        // Try to extract JSON from text
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $json_data = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                return $this->parse_structured_response($json_data);
            }
        }

        // Fallback to plain text response
        return [
            'success' => true,
            'thought' => '',
            'response' => $text,
            'action' => null,
            'persona_update' => null,
        ];
    }

    /**
     * Validate action type
     *
     * @param string $action Action type
     * @return bool True if valid
     */
    private function is_valid_action(string $action): bool
    {
        return in_array($action, self::ALLOWED_ACTIONS, true);
    }

    /**
     * Get error response
     *
     * @param string $error Error message
     * @return array Error response
     */
    private function get_error_response(string $error): array
    {
        return [
            'success' => false,
            'error' => $error,
            'response' => 'متأسفانه در حال حاضر نمی‌توانم پاسخگوی شما باشم. لطفاً دوباره تلاش کنید.',
            'action' => null,
        ];
    }

    /**
     * Extract action parameters
     *
     * @param array $action Action data
     * @return array Validated action parameters
     */
    public function extract_action_params(array $action): array
    {
        if (empty($action['type'])) {
            return [];
        }

        $params = [
            'type' => $action['type'],
        ];

        // Validate and extract based on action type
        switch ($action['type']) {
            case 'highlight_element':
            case 'show_tooltip':
            case 'scroll_to':
                $params['target'] = $this->sanitize_selector($action['target'] ?? '');
                $params['message'] = $action['data']['message'] ?? '';
                break;

            case 'open_modal':
                $params['modal_id'] = $this->sanitize_id($action['target'] ?? '');
                $params['title'] = $action['data']['title'] ?? '';
                $params['content'] = $action['data']['content'] ?? '';
                break;

            case 'update_calculator':
                $params['field'] = $this->sanitize_id($action['target'] ?? '');
                $params['value'] = $action['data']['value'] ?? '';
                break;

            case 'suggest_product':
                $params['product_id'] = absint($action['data']['product_id'] ?? 0);
                $params['reason'] = $action['data']['reason'] ?? '';
                break;

            case 'show_discount':
                $params['discount_code'] = sanitize_text_field($action['data']['code'] ?? '');
                $params['message'] = $action['data']['message'] ?? '';
                break;

            case 'change_css':
                $params['target'] = $this->sanitize_selector($action['target'] ?? '');
                $params['property'] = sanitize_key($action['data']['property'] ?? '');
                $params['value'] = sanitize_text_field($action['data']['value'] ?? '');
                break;

            case 'redirect':
                $params['url'] = esc_url_raw($action['data']['url'] ?? '');
                $params['delay'] = absint($action['data']['delay'] ?? 0);
                break;
        }

        return $params;
    }

    /**
     * Sanitize CSS selector
     *
     * @param string $selector CSS selector
     * @return string Sanitized selector
     */
    private function sanitize_selector(string $selector): string
    {
        // Allow only safe CSS selector characters
        return preg_replace('/[^a-zA-Z0-9\-_#.\s\[\]\=\>\~\+\*]/', '', $selector);
    }

    /**
     * Sanitize ID
     *
     * @param string $id Element ID
     * @return string Sanitized ID
     */
    private function sanitize_id(string $id): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $id);
    }

    /**
     * Validate response structure
     *
     * @param array $response Response to validate
     * @return bool True if valid
     */
    public function is_valid_response(array $response): bool
    {
        // Must have success flag
        if (!isset($response['success'])) {
            return false;
        }

        // If successful, must have response text
        if ($response['success'] && empty($response['response'])) {
            return false;
        }

        // If action present, validate it
        if (isset($response['action']) && $response['action'] !== null) {
            if (!isset($response['action']['type']) || 
                !$this->is_valid_action($response['action']['type'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert response to frontend format
     *
     * @param array $parsed_response Parsed response
     * @return array Frontend-ready response
     */
    public function to_frontend_format(array $parsed_response): array
    {
        $output = [
            'success' => $parsed_response['success'],
            'message' => $parsed_response['response'] ?? '',
            'timestamp' => current_time('mysql'),
        ];

        // Add thought process if available (for debugging)
        if (!empty($parsed_response['thought'])) {
            $output['debug'] = [
                'thought' => $parsed_response['thought'],
            ];
        }

        // Add action if present
        if (!empty($parsed_response['action'])) {
            $action_params = $this->extract_action_params($parsed_response['action']);
            if (!empty($action_params)) {
                $output['action'] = $action_params;
            }
        }

        // Add persona update if present
        if (!empty($parsed_response['persona_update'])) {
            $output['persona_update'] = $parsed_response['persona_update'];
        }

        return $output;
    }

    /**
     * Log action execution
     *
     * @param string $user_identifier User identifier
     * @param array $action Action data
     * @param bool $success Execution success
     * @return void
     */
    public function log_action(string $user_identifier, array $action, bool $success): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'homaye_action_log';

        // Check if table exists, if not, skip logging
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        if (!$table_exists) {
            return;
        }

        $wpdb->insert(
            $table_name,
            [
                'user_identifier' => $user_identifier,
                'action_type' => $action['type'] ?? 'unknown',
                'action_data' => wp_json_encode($action),
                'success' => $success ? 1 : 0,
                'timestamp' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );
    }
}
