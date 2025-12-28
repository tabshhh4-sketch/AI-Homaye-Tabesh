<?php
/**
 * Vault Manager - Omni-Store Memory Engine
 *
 * @package HomayeTabesh
 * @since PR7
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * مدیریت حافظه چندلایه (Vault Storage)
 * 
 * Three-layer memory architecture:
 * 1. Short-term (Flash): wp_homa_vault - Current form states
 * 2. Mid-term (Working): wp_homa_sessions - Session snapshots
 * 3. Long-term (Archive): wp_homa_user_interests - User persona & behavior
 */
class HT_Vault_Manager
{
    /**
     * Get or create session token for current user
     *
     * @return string
     */
    public static function get_session_token(): string
    {
        // Check if logged in user
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        }

        // Check for existing session token in cookie
        $cookie_name = 'homa_session_token';
        if (isset($_COOKIE[$cookie_name])) {
            return sanitize_text_field($_COOKIE[$cookie_name]);
        }

        // Generate new session token
        $token = 'guest_' . bin2hex(random_bytes(16));
        
        // Set cookie for 48 hours
        setcookie($cookie_name, $token, time() + (48 * 3600), '/');
        
        return $token;
    }

    /**
     * Store a value in the vault (Short-term Flash memory)
     *
     * @param string $context_key The key to store (e.g., 'calculator_state')
     * @param mixed $context_value The value to store
     * @param string|null $ai_insight Optional AI analysis of this context
     * @return bool Success status
     */
    public static function store(string $context_key, $context_value, ?string $ai_insight = null): bool
    {
        global $wpdb;
        
        $session_token = self::get_session_token();
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        $table_name = $wpdb->prefix . 'homa_vault';

        // Check if record exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE session_token = %s AND context_key = %s",
            $session_token,
            $context_key
        ));

        $data = [
            'session_token' => $session_token,
            'user_id' => $user_id,
            'context_key' => $context_key,
            'context_value' => wp_json_encode($context_value),
            'ai_insight' => $ai_insight,
            'updated_at' => current_time('mysql')
        ];

        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $table_name,
                $data,
                ['id' => $existing],
                ['%s', '%d', '%s', '%s', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $table_name,
                $data,
                ['%s', '%d', '%s', '%s', '%s', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * Retrieve a value from the vault
     *
     * @param string $context_key The key to retrieve
     * @param string|null $session_token Optional specific session token
     * @return mixed|null The stored value or null if not found
     */
    public static function get(string $context_key, ?string $session_token = null)
    {
        global $wpdb;
        
        $session_token = $session_token ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_vault';

        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT context_value, ai_insight FROM $table_name WHERE session_token = %s AND context_key = %s",
            $session_token,
            $context_key
        ));

        if ($record) {
            return json_decode($record->context_value, true);
        }

        return null;
    }

    /**
     * Get all vault data for a session
     *
     * @param string|null $session_token Optional specific session token
     * @return array Array of context data
     */
    public static function get_all(?string $session_token = null): array
    {
        global $wpdb;
        
        $session_token = $session_token ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_vault';

        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT context_key, context_value, ai_insight FROM $table_name WHERE session_token = %s",
            $session_token
        ));

        $data = [];
        foreach ($records as $record) {
            $data[$record->context_key] = [
                'value' => json_decode($record->context_value, true),
                'insight' => $record->ai_insight
            ];
        }

        return $data;
    }

    /**
     * Save session snapshot (Mid-term Working memory)
     *
     * @param string $last_url Current page URL
     * @param array $form_snapshot Form field values
     * @param string|null $chat_summary AI-generated chat summary
     * @return bool Success status
     */
    public static function save_session_snapshot(string $last_url, array $form_snapshot, ?string $chat_summary = null): bool
    {
        global $wpdb;
        
        $session_token = self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_sessions';
        
        // Calculate expiration (48 hours from now)
        $expires_at = date('Y-m-d H:i:s', time() + (48 * 3600));

        // Check if session exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE session_token = %s",
            $session_token
        ));

        $data = [
            'session_token' => $session_token,
            'last_url' => $last_url,
            'form_snapshot' => wp_json_encode($form_snapshot),
            'chat_summary' => $chat_summary,
            'updated_at' => current_time('mysql'),
            'expires_at' => $expires_at
        ];

        if ($existing) {
            // Update existing session
            $result = $wpdb->update(
                $table_name,
                $data,
                ['id' => $existing],
                ['%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new session
            $result = $wpdb->insert(
                $table_name,
                $data,
                ['%s', '%s', '%s', '%s', '%s', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * Get session snapshot
     *
     * @param string|null $session_token Optional specific session token
     * @return array|null Session data or null if not found
     */
    public static function get_session_snapshot(?string $session_token = null): ?array
    {
        global $wpdb;
        
        $session_token = $session_token ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_sessions';

        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT last_url, form_snapshot, chat_summary, updated_at FROM $table_name 
             WHERE session_token = %s AND (expires_at IS NULL OR expires_at > NOW())",
            $session_token
        ));

        if ($session) {
            return [
                'last_url' => $session->last_url,
                'form_snapshot' => json_decode($session->form_snapshot, true),
                'chat_summary' => $session->chat_summary,
                'updated_at' => $session->updated_at
            ];
        }

        return null;
    }

    /**
     * Track user interest (Long-term Archive memory)
     *
     * @param string $category_slug Category being viewed
     * @param int $score_increment Score to add (default: 1)
     * @param string $source_referral Traffic source (e.g., 'torob', 'google')
     * @return bool Success status
     */
    public static function track_interest(string $category_slug, int $score_increment = 1, string $source_referral = 'organic'): bool
    {
        global $wpdb;
        
        $user_id_or_token = self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_user_interests';

        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, interest_score FROM $table_name WHERE user_id_or_token = %s AND category_slug = %s",
            $user_id_or_token,
            $category_slug
        ));

        if ($existing) {
            // Update existing interest
            $new_score = $existing->interest_score + $score_increment;
            $result = $wpdb->update(
                $table_name,
                [
                    'interest_score' => $new_score,
                    'last_interaction' => current_time('mysql')
                ],
                ['id' => $existing->id],
                ['%d', '%s'],
                ['%d']
            );
        } else {
            // Insert new interest
            $result = $wpdb->insert(
                $table_name,
                [
                    'user_id_or_token' => $user_id_or_token,
                    'category_slug' => $category_slug,
                    'interest_score' => $score_increment,
                    'source_referral' => $source_referral,
                    'last_interaction' => current_time('mysql')
                ],
                ['%s', '%s', '%d', '%s', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * Get user interests
     *
     * @param int $limit Maximum number of interests to return
     * @return array Array of interests sorted by score
     */
    public static function get_user_interests(int $limit = 10): array
    {
        global $wpdb;
        
        $user_id_or_token = self::get_session_token();
        $table_name = $wpdb->prefix . 'homa_user_interests';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT category_slug, interest_score, source_referral, last_interaction 
             FROM $table_name 
             WHERE user_id_or_token = %s 
             ORDER BY interest_score DESC 
             LIMIT %d",
            $user_id_or_token,
            $limit
        ));

        return array_map(function($row) {
            return [
                'category' => $row->category_slug,
                'score' => (int)$row->interest_score,
                'source' => $row->source_referral,
                'last_interaction' => $row->last_interaction
            ];
        }, $results);
    }

    /**
     * Clear expired sessions (cleanup job)
     *
     * @return int Number of sessions deleted
     */
    public static function cleanup_expired_sessions(): int
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'homa_sessions';

        $deleted = $wpdb->query(
            "DELETE FROM $table_name WHERE expires_at IS NOT NULL AND expires_at < NOW()"
        );

        return (int)$deleted;
    }

    /**
     * Clear all vault data for a session
     *
     * @param string|null $session_token Optional specific session token
     * @return bool Success status
     */
    public static function clear_session(?string $session_token = null): bool
    {
        global $wpdb;
        
        $session_token = $session_token ?? self::get_session_token();
        
        // Clear vault data
        $wpdb->delete(
            $wpdb->prefix . 'homa_vault',
            ['session_token' => $session_token],
            ['%s']
        );

        // Clear session snapshot
        $wpdb->delete(
            $wpdb->prefix . 'homa_sessions',
            ['session_token' => $session_token],
            ['%s']
        );

        return true;
    }

    /**
     * Get memory summary for AI prompt enrichment
     *
     * @return string Formatted memory summary
     */
    public static function get_memory_summary(): string
    {
        $vault_data = self::get_all();
        $session = self::get_session_snapshot();
        $interests = self::get_user_interests(5);

        $summary = [];

        // Add vault context
        if (!empty($vault_data)) {
            $summary[] = "Context from current session:";
            foreach ($vault_data as $key => $data) {
                if (!empty($data['value'])) {
                    $summary[] = "- {$key}: " . json_encode($data['value']);
                }
            }
        }

        // Add session info
        if ($session && !empty($session['chat_summary'])) {
            $summary[] = "Previous conversation summary: " . $session['chat_summary'];
        }

        // Add interests
        if (!empty($interests)) {
            $categories = array_map(function($interest) {
                return $interest['category'] . " (score: {$interest['score']})";
            }, $interests);
            $summary[] = "User interests: " . implode(', ', $categories);
        }

        return implode("\n", $summary);
    }

    /**
     * Store a chat message in persistent memory
     *
     * @param string $message_type Type: 'user' or 'assistant'
     * @param string $message_content The message content
     * @param array $ai_metadata Optional metadata (actions, commands, etc.)
     * @return bool Success status
     */
    public static function store_chat_message(string $message_type, string $message_content, array $ai_metadata = []): bool
    {
        global $wpdb;

        $session_id = self::get_session_token();
        $user_identifier = is_user_logged_in() ? 'user_' . get_current_user_id() : $session_id;
        $user_role = self::get_user_role();
        $table_name = $wpdb->prefix . 'homaye_chat_memory';

        $result = $wpdb->insert(
            $table_name,
            [
                'session_id' => $session_id,
                'user_identifier' => $user_identifier,
                'user_role' => $user_role,
                'message_type' => $message_type,
                'message_content' => $message_content,
                'ai_metadata' => !empty($ai_metadata) ? wp_json_encode($ai_metadata) : null,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $result !== false;
    }

    /**
     * Retrieve chat messages for current session
     *
     * @param int $limit Maximum number of messages to retrieve (0 = all)
     * @param string|null $session_id Optional specific session ID
     * @return array Array of chat messages
     */
    public static function get_chat_messages(int $limit = 50, ?string $session_id = null): array
    {
        global $wpdb;

        $session_id = $session_id ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homaye_chat_memory';

        $query = $wpdb->prepare(
            "SELECT message_type, message_content, ai_metadata, created_at 
             FROM $table_name 
             WHERE session_id = %s 
             ORDER BY created_at ASC",
            $session_id
        );

        if ($limit > 0) {
            $query .= $wpdb->prepare(" LIMIT %d", $limit);
        }

        $results = $wpdb->get_results($query);

        $messages = [];
        foreach ($results as $row) {
            $messages[] = [
                'type' => $row->message_type,
                'content' => $row->message_content,
                'metadata' => $row->ai_metadata ? json_decode($row->ai_metadata, true) : [],
                'timestamp' => $row->created_at
            ];
        }

        return $messages;
    }

    /**
     * Check if chat history exists for current session
     *
     * @param string|null $session_id Optional specific session ID
     * @return bool True if chat history exists
     */
    public static function has_chat_history(?string $session_id = null): bool
    {
        global $wpdb;

        $session_id = $session_id ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homaye_chat_memory';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE session_id = %s",
            $session_id
        ));

        return (int)$count > 0;
    }

    /**
     * Clear chat history for current session
     *
     * @param string|null $session_id Optional specific session ID
     * @return bool Success status
     */
    public static function clear_chat_history(?string $session_id = null): bool
    {
        global $wpdb;

        $session_id = $session_id ?? self::get_session_token();
        $table_name = $wpdb->prefix . 'homaye_chat_memory';

        $result = $wpdb->delete(
            $table_name,
            ['session_id' => $session_id],
            ['%s']
        );

        return $result !== false;
    }

    /**
     * Get user role for current user
     *
     * @return string User role
     */
    private static function get_user_role(): string
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('administrator', (array) $user->roles)) {
                return 'admin';
            }
            if (in_array('customer', (array) $user->roles)) {
                return 'customer';
            }
            return 'customer'; // Default for logged-in users
        }
        return 'guest';
    }

    /**
     * Improve session token generation with better persistence
     * This method is called on init to ensure cookie is properly set
     *
     * @return string Session token
     */
    public static function ensure_session_token(): string
    {
        $cookie_name = 'homa_session_token';
        
        // For logged-in users, use user ID
        if (is_user_logged_in()) {
            $token = 'user_' . get_current_user_id();
            // Update cookie to match
            if (!isset($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] !== $token) {
                setcookie($cookie_name, $token, time() + (48 * 3600), '/', '', is_ssl(), true);
            }
            return $token;
        }

        // For guests, ensure persistent cookie
        if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
            $token = sanitize_text_field($_COOKIE[$cookie_name]);
            // Refresh cookie expiration
            setcookie($cookie_name, $token, time() + (48 * 3600), '/', '', is_ssl(), true);
            return $token;
        }

        // Generate new token for new guests
        $token = 'guest_' . bin2hex(random_bytes(16));
        setcookie($cookie_name, $token, time() + (48 * 3600), '/', '', is_ssl(), true);
        
        return $token;
    }
}
