<?php
/**
 * Admin Settings Page
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * Admin settings and configuration
 */
class HT_Admin
{
    /**
     * Initialize admin hooks
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu(): void
    {
        add_menu_page(
            __('همای تابش', 'homaye-tabesh'),
            __('همای تابش', 'homaye-tabesh'),
            'manage_options',
            'homaye-tabesh',
            [$this, 'render_settings_page'],
            'dashicons-superhero',
            30
        );

        add_submenu_page(
            'homaye-tabesh',
            __('تنظیمات', 'homaye-tabesh'),
            __('تنظیمات', 'homaye-tabesh'),
            'manage_options',
            'homaye-tabesh',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'homaye-tabesh',
            __('آمار پرسونا', 'homaye-tabesh'),
            __('آمار پرسونا', 'homaye-tabesh'),
            'manage_options',
            'homaye-tabesh-personas',
            [$this, 'render_personas_page']
        );

        add_submenu_page(
            'homaye-tabesh',
            __('مرکز کنترل اطلس', 'homaye-tabesh'),
            __('🗺️ مرکز کنترل اطلس', 'homaye-tabesh'),
            'manage_options',
            'homaye-tabesh-atlas',
            [$this, 'render_atlas_page']
        );

        add_submenu_page(
            'homaye-tabesh',
            __('ناظر کل افزونه‌ها', 'homaye-tabesh'),
            __('🔍 ناظر کل', 'homaye-tabesh'),
            'manage_options',
            'homaye-tabesh-observer',
            [$this, 'render_observer_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings(): void
    {
        register_setting('homaye_tabesh_settings', 'ht_gemini_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting('homaye_tabesh_settings', 'ht_tracking_enabled', [
            'type' => 'boolean',
            'default' => true,
        ]);

        register_setting('homaye_tabesh_settings', 'ht_divi_integration', [
            'type' => 'boolean',
            'default' => true,
        ]);

        register_setting('homaye_tabesh_settings', 'ht_min_score_threshold', [
            'type' => 'integer',
            'default' => 50,
            'sanitize_callback' => 'absint',
        ]);

        // Add settings section
        add_settings_section(
            'ht_main_section',
            __('تنظیمات اصلی', 'homaye-tabesh'),
            null,
            'homaye-tabesh'
        );

        // Add settings fields
        add_settings_field(
            'ht_gemini_api_key',
            __('کلید API گوگل Gemini', 'homaye-tabesh'),
            [$this, 'render_api_key_field'],
            'homaye-tabesh',
            'ht_main_section'
        );

        add_settings_field(
            'ht_tracking_enabled',
            __('ردیابی رفتار', 'homaye-tabesh'),
            [$this, 'render_tracking_field'],
            'homaye-tabesh',
            'ht_main_section'
        );

        add_settings_field(
            'ht_divi_integration',
            __('یکپارچه‌سازی با Divi', 'homaye-tabesh'),
            [$this, 'render_divi_field'],
            'homaye-tabesh',
            'ht_main_section'
        );

        add_settings_field(
            'ht_min_score_threshold',
            __('حداقل امتیاز پرسونا', 'homaye-tabesh'),
            [$this, 'render_threshold_field'],
            'homaye-tabesh',
            'ht_main_section'
        );
    }

    /**
     * Render API key field
     */
    public function render_api_key_field(): void
    {
        $value = get_option('ht_gemini_api_key', '');
        ?>
        <input type="text" 
               id="ht_gemini_api_key" 
               name="ht_gemini_api_key" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="AIza...">
        <p class="description">
            کلید API خود را از 
            <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a> 
            دریافت کنید.
        </p>
        <?php
    }

    /**
     * Render tracking field
     */
    public function render_tracking_field(): void
    {
        $value = get_option('ht_tracking_enabled', true);
        ?>
        <label>
            <input type="checkbox" 
                   name="ht_tracking_enabled" 
                   value="1" 
                   <?php checked($value); ?>>
            فعال‌سازی ردیابی رفتار کاربران
        </label>
        <?php
    }

    /**
     * Render Divi integration field
     */
    public function render_divi_field(): void
    {
        $value = get_option('ht_divi_integration', true);
        ?>
        <label>
            <input type="checkbox" 
                   name="ht_divi_integration" 
                   value="1" 
                   <?php checked($value); ?>>
            فعال‌سازی ردیابی خودکار المان‌های Divi
        </label>
        <?php
    }

    /**
     * Render threshold field
     */
    public function render_threshold_field(): void
    {
        $value = get_option('ht_min_score_threshold', 50);
        ?>
        <input type="number" 
               id="ht_min_score_threshold" 
               name="ht_min_score_threshold" 
               value="<?php echo esc_attr($value); ?>" 
               min="0" 
               max="1000"
               step="10">
        <p class="description">
            حداقل امتیازی که یک کاربر باید کسب کند تا پرسونا شناسایی شود.
        </p>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap homaye-tabesh-admin">
            <h1><?php echo esc_html__('تنظیمات همای تابش', 'homaye-tabesh'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('homaye_tabesh_settings');
                do_settings_sections('homaye-tabesh');
                submit_button('ذخیره تنظیمات');
                ?>
            </form>

            <hr>

            <h2>وضعیت سیستم</h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong>نسخه PHP:</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>نسخه WordPress:</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>قالب فعال:</strong></td>
                        <td><?php echo wp_get_theme()->get('Name'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>WooCommerce:</strong></td>
                        <td><?php echo class_exists('WooCommerce') ? '✓ نصب شده' : '✗ نصب نشده'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Divi Theme:</strong></td>
                        <td><?php 
                            $theme = wp_get_theme();
                            echo ($theme->get('Name') === 'Divi' || $theme->get('Template') === 'Divi') ? '✓ فعال' : '✗ غیرفعال'; 
                        ?></td>
                    </tr>
                    <tr>
                        <td><strong>API Key تنظیم شده:</strong></td>
                        <td><?php echo !empty(get_option('ht_gemini_api_key')) ? '✓ بله' : '✗ خیر'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render personas statistics page
     */
    public function render_personas_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'homaye_persona_scores';

        // Get statistics
        $stats = $wpdb->get_results(
            "SELECT persona_type, COUNT(*) as count, AVG(score) as avg_score, MAX(score) as max_score 
             FROM $table_name 
             GROUP BY persona_type 
             ORDER BY count DESC"
        );

        ?>
        <div class="wrap homaye-tabesh-admin">
            <h1><?php echo esc_html__('آمار پرسونا', 'homaye-tabesh'); ?></h1>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>نوع پرسونا</th>
                        <th>تعداد کاربران</th>
                        <th>میانگین امتیاز</th>
                        <th>بیشترین امتیاز</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats)): ?>
                        <tr>
                            <td colspan="4">هنوز داده‌ای وجود ندارد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><strong><?php echo esc_html($stat->persona_type); ?></strong></td>
                                <td><?php echo esc_html($stat->count); ?></td>
                                <td><?php echo round($stat->avg_score, 2); ?></td>
                                <td>
                                    <span class="homaye-tabesh-persona-score <?php 
                                        echo $stat->max_score >= 100 ? 'high' : ($stat->max_score >= 50 ? 'medium' : 'low'); 
                                    ?>">
                                        <?php echo esc_html($stat->max_score); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>رویدادهای اخیر</h2>
            <?php
            $events_table = $wpdb->prefix . 'homaye_telemetry_events';
            $recent_events = $wpdb->get_results(
                "SELECT * FROM $events_table ORDER BY timestamp DESC LIMIT 20"
            );
            ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>شناسه کاربر</th>
                        <th>نوع رویداد</th>
                        <th>کلاس المان</th>
                        <th>زمان</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_events)): ?>
                        <tr>
                            <td colspan="4">هنوز رویدادی ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_events as $event): ?>
                            <tr>
                                <td><?php echo esc_html(substr($event->user_identifier, 0, 20)); ?>...</td>
                                <td><?php echo esc_html($event->event_type); ?></td>
                                <td><?php echo esc_html($event->element_class); ?></td>
                                <td><?php echo esc_html($event->timestamp); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Atlas Control Center page
     */
    public function render_atlas_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Enqueue Atlas React app
        wp_enqueue_script(
            'atlas-dashboard',
            HT_PLUGIN_URL . 'assets/build/atlas-dashboard.js',
            ['wp-element'],
            HT_VERSION,
            true
        );

        wp_enqueue_style(
            'atlas-dashboard',
            HT_PLUGIN_URL . 'assets/css/atlas-dashboard.css',
            [],
            HT_VERSION
        );

        // Localize script with API endpoints
        $user = wp_get_current_user();
        $is_admin = in_array('administrator', (array) $user->roles, true);
        
        wp_localize_script('atlas-dashboard', 'atlasConfig', [
            'apiUrl' => rest_url('homaye/v1/atlas'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userRole' => $is_admin ? 'administrator' : 'manager',
        ]);

        ?>
        <div class="wrap homaye-tabesh-atlas">
            <h1><?php echo esc_html__('🗺️ مرکز کنترل اطلس (Atlas Control Center)', 'homaye-tabesh'); ?></h1>
            <p class="description">
                <?php echo esc_html__('سیستم هوش تجاری و موتور تصمیم‌گیری داده‌محور', 'homaye-tabesh'); ?>
            </p>
            <div id="atlas-dashboard-root"></div>
        </div>
        <?php
    }

    /**
     * Render Global Observer page (PR13)
     */
    public function render_observer_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap" style="direction: rtl; text-align: right;">
            <h1><?php echo esc_html__('ناظر کل افزونه‌ها', 'homaye-tabesh'); ?> 🔍</h1>
            <p><?php echo esc_html__('مدیریت نظارت بر افزونه‌ها و استخراج اطلاعات برای هوش مصنوعی', 'homaye-tabesh'); ?></p>
            
            <div id="observer-container">
                <div class="card" style="margin-top: 20px;">
                    <h2>وضعیت ناظر کل</h2>
                    <div id="observer-status">
                        <p>در حال بارگذاری...</p>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>افزونه‌های نصب شده</h2>
                    <p>افزونه‌های تحت نظر با ✅ مشخص شده‌اند. برای اضافه/حذف کردن افزونه از لیست نظارت، روی دکمه کلیک کنید.</p>
                    <div id="plugins-list">
                        <p>در حال بارگذاری...</p>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>تغییرات اخیر</h2>
                    <div id="recent-changes">
                        <p>در حال بارگذاری...</p>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>فکت‌های استخراج شده</h2>
                    <div id="recent-facts">
                        <p>در حال بارگذاری...</p>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>عملیات</h2>
                    <button id="refresh-metadata-btn" class="button button-primary">
                        به‌روزرسانی متادیتا
                    </button>
                    <span id="refresh-status"></span>
                </div>
            </div>
            
            <script>
                jQuery(document).ready(function($) {
                    const API_BASE = '<?php echo esc_url(rest_url('homaye/v1')); ?>';
                    const NONCE = '<?php echo wp_create_nonce('wp_rest'); ?>';

                    // Load observer status
                    function loadObserverStatus() {
                        $.ajax({
                            url: API_BASE + '/observer/status',
                            method: 'GET',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            success: function(response) {
                                if (response.success) {
                                    const data = response.data;
                                    $('#observer-status').html(\`
                                        <ul style="list-style: none; padding: 0;">
                                            <li>✅ تعداد افزونه‌های تحت نظر: <strong>\${data.monitored_count}</strong></li>
                                            <li>✅ افزونه‌های فعال: <strong>\${data.active_count}</strong></li>
                                            <li>✅ آخرین همگام‌سازی: <strong>\${data.last_sync}</strong></li>
                                        </ul>
                                    \`);
                                }
                            },
                            error: function() {
                                $('#observer-status').html('<p style="color: red;">خطا در بارگذاری</p>');
                            }
                        });
                    }

                    // Load plugins list
                    function loadPluginsList() {
                        $.ajax({
                            url: API_BASE + '/observer/plugins',
                            method: 'GET',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            success: function(response) {
                                if (response.success) {
                                    let html = '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
                                    html += '<thead><tr>';
                                    html += '<th>نام افزونه</th>';
                                    html += '<th>نسخه</th>';
                                    html += '<th>وضعیت</th>';
                                    html += '<th>نظارت</th>';
                                    html += '<th>عملیات</th>';
                                    html += '</tr></thead><tbody>';

                                    response.data.forEach(function(plugin) {
                                        const activeText = plugin.is_active ? '✅ فعال' : '❌ غیرفعال';
                                        const monitorText = plugin.is_monitored ? '✅ تحت نظر' : '➖ خیر';
                                        const btnText = plugin.is_monitored ? 'حذف از نظارت' : 'اضافه به نظارت';
                                        const btnClass = plugin.is_monitored ? 'button' : 'button button-primary';
                                        
                                        html += '<tr>';
                                        html += \`<td><strong>\${plugin.name}</strong><br/><small>\${plugin.description}</small></td>\`;
                                        html += \`<td>\${plugin.version}</td>\`;
                                        html += \`<td>\${activeText}</td>\`;
                                        html += \`<td>\${monitorText}</td>\`;
                                        html += \`<td><button class="toggle-monitor \${btnClass}" data-path="\${plugin.path}" data-monitored="\${plugin.is_monitored}">\${btnText}</button></td>\`;
                                        html += '</tr>';
                                    });

                                    html += '</tbody></table>';
                                    $('#plugins-list').html(html);

                                    // Bind toggle events
                                    $('.toggle-monitor').on('click', function() {
                                        const btn = $(this);
                                        const path = btn.data('path');
                                        const isMonitored = btn.data('monitored');
                                        toggleMonitoring(path, isMonitored, btn);
                                    });
                                }
                            },
                            error: function() {
                                $('#plugins-list').html('<p style="color: red;">خطا در بارگذاری</p>');
                            }
                        });
                    }

                    // Toggle monitoring
                    function toggleMonitoring(path, isMonitored, btn) {
                        const endpoint = isMonitored ? '/observer/monitor/remove' : '/observer/monitor/add';
                        
                        btn.prop('disabled', true).text('در حال پردازش...');

                        $.ajax({
                            url: API_BASE + endpoint,
                            method: 'POST',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            data: {
                                plugin_path: path
                            },
                            success: function(response) {
                                if (response.success) {
                                    loadObserverStatus();
                                    loadPluginsList();
                                }
                            },
                            error: function() {
                                alert('خطا در انجام عملیات');
                                btn.prop('disabled', false);
                            }
                        });
                    }

                    // Load recent changes
                    function loadRecentChanges() {
                        $.ajax({
                            url: API_BASE + '/observer/changes',
                            method: 'GET',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            success: function(response) {
                                if (response.success && response.data.length > 0) {
                                    let html = '<table class="wp-list-table widefat" style="width: 100%;">';
                                    html += '<thead><tr><th>نوع رویداد</th><th>زمان</th></tr></thead><tbody>';

                                    response.data.forEach(function(change) {
                                        html += '<tr>';
                                        html += \`<td>\${change.event_type}</td>\`;
                                        html += \`<td>\${change.created_at}</td>\`;
                                        html += '</tr>';
                                    });

                                    html += '</tbody></table>';
                                    $('#recent-changes').html(html);
                                } else {
                                    $('#recent-changes').html('<p>هیچ تغییری ثبت نشده است.</p>');
                                }
                            },
                            error: function() {
                                $('#recent-changes').html('<p style="color: red;">خطا در بارگذاری</p>');
                            }
                        });
                    }

                    // Load recent facts
                    function loadRecentFacts() {
                        $.ajax({
                            url: API_BASE + '/observer/facts',
                            method: 'GET',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            success: function(response) {
                                if (response.success && response.data.length > 0) {
                                    let html = '<ul style="list-style: disc; padding-right: 20px;">';

                                    response.data.forEach(function(fact) {
                                        html += \`<li><strong>\${fact.fact}</strong> <small>(\${fact.created_at})</small></li>\`;
                                    });

                                    html += '</ul>';
                                    $('#recent-facts').html(html);
                                } else {
                                    $('#recent-facts').html('<p>هیچ فکتی استخراج نشده است.</p>');
                                }
                            },
                            error: function() {
                                $('#recent-facts').html('<p style="color: red;">خطا در بارگذاری</p>');
                            }
                        });
                    }

                    // Refresh metadata
                    $('#refresh-metadata-btn').on('click', function() {
                        const btn = $(this);
                        btn.prop('disabled', true).text('در حال به‌روزرسانی...');
                        $('#refresh-status').text('');

                        $.ajax({
                            url: API_BASE + '/observer/refresh',
                            method: 'POST',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', NONCE);
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#refresh-status').html('<span style="color: green;">✅ متادیتا به‌روزرسانی شد!</span>');
                                    loadObserverStatus();
                                }
                                btn.prop('disabled', false).text('به‌روزرسانی متادیتا');
                            },
                            error: function() {
                                $('#refresh-status').html('<span style="color: red;">❌ خطا در به‌روزرسانی</span>');
                                btn.prop('disabled', false).text('به‌روزرسانی متادیتا');
                            }
                        });
                    });

                    // Initial load
                    loadObserverStatus();
                    loadPluginsList();
                    loadRecentChanges();
                    loadRecentFacts();

                    // Auto-refresh every 30 seconds
                    setInterval(function() {
                        loadRecentChanges();
                        loadRecentFacts();
                    }, 30000);
                });
            </script>

            <style>
                .card {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                    padding: 20px;
                }
                .card h2 {
                    margin-top: 0;
                    font-size: 18px;
                    font-weight: 600;
                }
                #observer-container ul {
                    margin: 10px 0;
                }
                #observer-container ul li {
                    margin: 8px 0;
                    font-size: 14px;
                }
            </style>
        </div>
        <?php
    }
}
