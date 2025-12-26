<?php
/**
 * Diplomacy Frontend Controller - Handles Translation UI
 *
 * @package HomayeTabesh
 * @since PR14
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * کنترلر فرانتند دیپلماسی
 * 
 * این کلاس مسئول نمایش پاپ‌آپ و مدیریت UI ترجمه است
 */
class HT_Diplomacy_Frontend
{
    /**
     * Render buffer filter
     */
    private Homa_Render_Buffer_Filter $render_filter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->render_filter = new Homa_Render_Buffer_Filter();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     * 
     * @return void
     */
    private function init_hooks(): void
    {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Inject translation data
        add_action('wp_footer', [$this, 'inject_translation_data']);

        // Add language switcher to footer
        add_action('wp_footer', [$this, 'add_language_switcher'], 999);

        // Handle AJAX translation toggle
        add_action('wp_ajax_homa_toggle_translation', [$this, 'ajax_toggle_translation']);
        add_action('wp_ajax_nopriv_homa_toggle_translation', [$this, 'ajax_toggle_translation']);
    }

    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueue_assets(): void
    {
        // Don't load on admin pages
        if (is_admin()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'homa-diplomacy',
            HT_PLUGIN_URL . 'assets/css/homa-diplomacy.css',
            [],
            HT_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'homa-diplomacy',
            HT_PLUGIN_URL . 'assets/js/homa-diplomacy.js',
            ['jquery'],
            HT_VERSION,
            true
        );

        // Add Arabic font if needed
        if (isset($_COOKIE['homa_translate_to']) && $_COOKIE['homa_translate_to'] === 'ar') {
            wp_enqueue_style(
                'noto-kufi-arabic',
                'https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;600;700&display=swap',
                [],
                null
            );
        }
    }

    /**
     * Inject translation data into footer
     * 
     * @return void
     */
    public function inject_translation_data(): void
    {
        if (is_admin()) {
            return;
        }

        $should_show = $this->render_filter->should_show_translation_popup();
        $country_info = $this->render_filter->get_detected_country_info();

        ?>
        <script type="text/javascript">
        var homaTranslationData = {
            shouldShow: <?php echo $should_show ? 'true' : 'false'; ?>,
            countryCode: '<?php echo esc_js($country_info['country_code'] ?? ''); ?>',
            countryNamePersian: '<?php echo esc_js($country_info['country_name_persian'] ?? ''); ?>',
            countryNameArabic: '<?php echo esc_js($country_info['country_name_arabic'] ?? ''); ?>',
            currentLang: '<?php echo isset($_COOKIE['homa_translate_to']) ? esc_js($_COOKIE['homa_translate_to']) : 'fa'; ?>',
            ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            nonce: '<?php echo wp_create_nonce('homa_translation_nonce'); ?>'
        };
        </script>
        <?php
    }

    /**
     * Add language switcher to footer
     * 
     * @return void
     */
    public function add_language_switcher(): void
    {
        if (is_admin()) {
            return;
        }

        // Don't show if translation is disabled in settings
        if (!get_option('ht_translation_enabled', true)) {
            return;
        }

        $current_lang = $_COOKIE['homa_translate_to'] ?? 'fa';
        $flag = $current_lang === 'ar' ? '🇸🇦' : '🇮🇷';
        $text = $current_lang === 'ar' ? 'العربية' : 'فارسی';

        ?>
        <div class="homa-language-switcher" data-lang="<?php echo $current_lang === 'ar' ? 'fa' : 'ar'; ?>">
            <span class="flag-icon"><?php echo $flag; ?></span>
            <span><?php echo $text; ?></span>
        </div>
        <?php
    }

    /**
     * Handle AJAX translation toggle
     * 
     * @return void
     */
    public function ajax_toggle_translation(): void
    {
        check_ajax_referer('homa_translation_nonce', 'nonce');

        $action = sanitize_text_field($_POST['action_type'] ?? 'enable');
        $lang = sanitize_text_field($_POST['lang'] ?? 'ar');

        if ($action === 'enable') {
            $this->render_filter->enable_translation($lang);
            wp_send_json_success([
                'message' => 'Translation enabled',
                'lang' => $lang
            ]);
        } else {
            $this->render_filter->disable_translation();
            wp_send_json_success([
                'message' => 'Translation disabled'
            ]);
        }
    }

    /**
     * Get translation statistics for current page
     * 
     * @return array
     */
    public function get_page_translation_stats(): array
    {
        return $this->render_filter->get_cache_stats();
    }
}
