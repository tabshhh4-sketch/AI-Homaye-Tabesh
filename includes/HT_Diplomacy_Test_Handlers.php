<?php
/**
 * Diplomacy Test Handlers - AJAX endpoints for validation page
 *
 * @package HomayeTabesh
 * @since PR14
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * هندلرهای تست دیپلماسی
 * 
 * این کلاس endpoint های AJAX برای صفحه اعتبارسنجی فراهم می‌کند
 */
class HT_Diplomacy_Test_Handlers
{
    /**
     * GeoLocation service
     */
    private HT_GeoLocation_Service $geo_service;

    /**
     * Translation cache manager
     */
    private HT_Translation_Cache_Manager $cache_manager;

    /**
     * Render buffer filter
     */
    private Homa_Render_Buffer_Filter $render_filter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->geo_service = new HT_GeoLocation_Service();
        $this->cache_manager = new HT_Translation_Cache_Manager();
        $this->render_filter = new Homa_Render_Buffer_Filter();

        $this->init_ajax_handlers();
    }

    /**
     * Initialize AJAX handlers
     * 
     * @return void
     */
    private function init_ajax_handlers(): void
    {
        // GeoIP test
        add_action('wp_ajax_homa_test_geoip', [$this, 'ajax_test_geoip']);
        add_action('wp_ajax_nopriv_homa_test_geoip', [$this, 'ajax_test_geoip']);

        // Get countries list
        add_action('wp_ajax_homa_get_countries', [$this, 'ajax_get_countries']);
        add_action('wp_ajax_nopriv_homa_get_countries', [$this, 'ajax_get_countries']);

        // Cache statistics
        add_action('wp_ajax_homa_cache_stats', [$this, 'ajax_cache_stats']);
        add_action('wp_ajax_nopriv_homa_cache_stats', [$this, 'ajax_cache_stats']);

        // Database check
        add_action('wp_ajax_homa_check_database', [$this, 'ajax_check_database']);
        add_action('wp_ajax_nopriv_homa_check_database', [$this, 'ajax_check_database']);

        // Test translation
        add_action('wp_ajax_homa_test_translate', [$this, 'ajax_test_translate']);
        add_action('wp_ajax_nopriv_homa_test_translate', [$this, 'ajax_test_translate']);
    }

    /**
     * AJAX handler: Test GeoIP detection
     * 
     * @return void
     */
    public function ajax_test_geoip(): void
    {
        $ip = $this->geo_service->get_user_ip();
        $country_code = $this->geo_service->detect_country($ip);
        $is_arabic = $this->geo_service->is_arabic_visitor($ip);
        $should_show_popup = $this->render_filter->should_show_translation_popup();

        $country_name_persian = '';
        $country_name_arabic = '';

        if ($country_code !== false) {
            $country_name_persian = $this->geo_service->get_country_name_persian($country_code);
            $country_name_arabic = $this->geo_service->get_country_name_arabic($country_code);
        }

        wp_send_json_success([
            'ip' => $ip,
            'country_code' => $country_code,
            'is_arabic' => $is_arabic,
            'should_show_popup' => $should_show_popup,
            'country_name_persian' => $country_name_persian,
            'country_name_arabic' => $country_name_arabic,
        ]);
    }

    /**
     * AJAX handler: Get monitored countries list
     * 
     * @return void
     */
    public function ajax_get_countries(): void
    {
        $countries = $this->geo_service->get_allowed_arabic_countries();

        wp_send_json_success([
            'countries' => $countries,
            'count' => count($countries),
        ]);
    }

    /**
     * AJAX handler: Get cache statistics
     * 
     * @return void
     */
    public function ajax_cache_stats(): void
    {
        $stats = $this->cache_manager->get_cache_stats();

        wp_send_json_success($stats);
    }

    /**
     * AJAX handler: Check database table
     * 
     * @return void
     */
    public function ajax_check_database(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homa_translations';

        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

        if (!$table_exists) {
            wp_send_json_error([
                'message' => 'Table does not exist',
                'table_name' => $table_name,
            ]);
            return;
        }

        // Get row count
        $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        // Get table structure
        $columns = $wpdb->get_results("DESCRIBE $table_name", ARRAY_A);
        $column_names = array_column($columns, 'Field');

        wp_send_json_success([
            'table_exists' => true,
            'table_name' => $table_name,
            'row_count' => (int) $row_count,
            'columns' => $column_names,
        ]);
    }

    /**
     * AJAX handler: Test translation
     * 
     * @return void
     */
    public function ajax_test_translate(): void
    {
        $text = sanitize_text_field($_POST['text'] ?? 'سلام، به سایت ما خوش آمدید!');
        $target_lang = sanitize_text_field($_POST['lang'] ?? 'ar');

        $translated = $this->cache_manager->get_translation($text, $target_lang);

        wp_send_json_success([
            'original' => $text,
            'translated' => $translated,
            'language' => $target_lang,
        ]);
    }
}
