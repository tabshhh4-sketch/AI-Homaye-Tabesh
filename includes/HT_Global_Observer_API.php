<?php
/**
 * Global Observer REST API
 *
 * @package HomayeTabesh
 * @since PR13
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * API برای مدیریت ناظر کل و تنظیمات افزونه‌ها
 */
class HT_Global_Observer_API
{
    /**
     * Global Observer instance
     */
    private HT_Global_Observer_Core $observer;

    /**
     * Plugin scanner
     */
    private HT_Plugin_Scanner $scanner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->observer = HT_Global_Observer_Core::instance();
        $this->scanner = new HT_Plugin_Scanner();
    }

    /**
     * Register REST API endpoints
     *
     * @return void
     */
    public function register_endpoints(): void
    {
        // دریافت خلاصه وضعیت ناظر
        register_rest_route('homaye/v1', '/observer/status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_observer_status'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // دریافت لیست افزونه‌های نصب شده
        register_rest_route('homaye/v1', '/observer/plugins', [
            'methods' => 'GET',
            'callback' => [$this, 'get_installed_plugins'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // اضافه کردن افزونه به لیست نظارت
        register_rest_route('homaye/v1', '/observer/monitor/add', [
            'methods' => 'POST',
            'callback' => [$this, 'add_to_monitoring'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'plugin_path' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);

        // حذف افزونه از لیست نظارت
        register_rest_route('homaye/v1', '/observer/monitor/remove', [
            'methods' => 'POST',
            'callback' => [$this, 'remove_from_monitoring'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'plugin_path' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);

        // دریافت لاگ تغییرات اخیر
        register_rest_route('homaye/v1', '/observer/changes', [
            'methods' => 'GET',
            'callback' => [$this, 'get_recent_changes'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // دریافت فکت‌های اخیر
        register_rest_route('homaye/v1', '/observer/facts', [
            'methods' => 'GET',
            'callback' => [$this, 'get_recent_facts'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // دریافت متادیتای افزونه‌ها
        register_rest_route('homaye/v1', '/observer/metadata', [
            'methods' => 'GET',
            'callback' => [$this, 'get_plugins_metadata'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // رفرش دستی متادیتا
        register_rest_route('homaye/v1', '/observer/refresh', [
            'methods' => 'POST',
            'callback' => [$this, 'refresh_metadata'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);
    }

    /**
     * Check if user has admin permissions
     *
     * @return bool
     */
    public function check_admin_permission(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * Get observer status
     *
     * @return \WP_REST_Response
     */
    public function get_observer_status(): \WP_REST_Response
    {
        $summary = $this->observer->get_monitoring_summary();

        return new \WP_REST_Response([
            'success' => true,
            'data' => $summary,
        ], 200);
    }

    /**
     * Get installed plugins
     *
     * @return \WP_REST_Response
     */
    public function get_installed_plugins(): \WP_REST_Response
    {
        $plugins = $this->scanner->get_installed_plugins();

        return new \WP_REST_Response([
            'success' => true,
            'data' => $plugins,
        ], 200);
    }

    /**
     * Add plugin to monitoring
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function add_to_monitoring(\WP_REST_Request $request): \WP_REST_Response
    {
        $plugin_path = sanitize_text_field($request->get_param('plugin_path'));

        $result = $this->scanner->add_monitored_plugin($plugin_path);

        if ($result) {
            // رفرش متادیتا بعد از اضافه کردن
            $engine = new HT_Metadata_Mining_Engine();
            $engine->refresh_metadata();

            return new \WP_REST_Response([
                'success' => true,
                'message' => 'افزونه به لیست نظارت اضافه شد.',
            ], 200);
        }

        return new \WP_REST_Response([
            'success' => false,
            'message' => 'خطا در اضافه کردن افزونه.',
        ], 500);
    }

    /**
     * Remove plugin from monitoring
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function remove_from_monitoring(\WP_REST_Request $request): \WP_REST_Response
    {
        $plugin_path = sanitize_text_field($request->get_param('plugin_path'));

        $result = $this->scanner->remove_monitored_plugin($plugin_path);

        if ($result) {
            return new \WP_REST_Response([
                'success' => true,
                'message' => 'افزونه از لیست نظارت حذف شد.',
            ], 200);
        }

        return new \WP_REST_Response([
            'success' => false,
            'message' => 'خطا در حذف افزونه.',
        ], 500);
    }

    /**
     * Get recent changes
     *
     * @return \WP_REST_Response
     */
    public function get_recent_changes(): \WP_REST_Response
    {
        $changes = $this->observer->get_recent_changes(50);

        return new \WP_REST_Response([
            'success' => true,
            'data' => $changes,
        ], 200);
    }

    /**
     * Get recent facts
     *
     * @return \WP_REST_Response
     */
    public function get_recent_facts(): \WP_REST_Response
    {
        $facts = $this->observer->get_recent_facts(50);

        return new \WP_REST_Response([
            'success' => true,
            'data' => $facts,
        ], 200);
    }

    /**
     * Get plugins metadata
     *
     * @return \WP_REST_Response
     */
    public function get_plugins_metadata(): \WP_REST_Response
    {
        $engine = new HT_Metadata_Mining_Engine();
        $metadata = $engine->get_metadata_for_ai();

        return new \WP_REST_Response([
            'success' => true,
            'data' => $metadata,
        ], 200);
    }

    /**
     * Refresh metadata manually
     *
     * @return \WP_REST_Response
     */
    public function refresh_metadata(): \WP_REST_Response
    {
        $engine = new HT_Metadata_Mining_Engine();
        $metadata = $engine->refresh_metadata();

        // به‌روزرسانی زمان آخرین همگام‌سازی
        update_option('homa_last_metadata_sync', current_time('mysql'));

        return new \WP_REST_Response([
            'success' => true,
            'message' => 'متادیتای افزونه‌ها به‌روزرسانی شد.',
            'data' => [
                'plugins_count' => count($metadata),
                'last_sync' => current_time('mysql'),
            ],
        ], 200);
    }
}
