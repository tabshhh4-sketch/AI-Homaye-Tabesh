<?php
/**
 * GeoLocation Service - IP-Based Country Detection
 *
 * @package HomayeTabesh
 * @since PR14
 */

declare(strict_types=1);

namespace HomayeTabesh;

/**
 * سرویس تشخیص موقعیت جغرافیایی
 * 
 * این کلاس با استفاده از IP کاربر، کشور او را شناسایی می‌کند
 * و بر اساس لیست کشورهای عربی، پیشنهاد ترجمه می‌دهد
 */
class HT_GeoLocation_Service
{
    /**
     * GeoIP service URL (using ipapi.co free service)
     */
    private const GEOIP_SERVICE_URL = 'https://ipapi.co/%s/country/';

    /**
     * Fallback GeoIP service (ip-api.com)
     */
    private const FALLBACK_GEOIP_URL = 'http://ip-api.com/json/%s';

    /**
     * Cache expiration time (1 hour)
     */
    private const CACHE_EXPIRATION = 3600;

    /**
     * Default Arabic countries list
     */
    private const DEFAULT_ARABIC_COUNTRIES = [
        'SA', // Saudi Arabia
        'AE', // United Arab Emirates
        'IQ', // Iraq
        'QA', // Qatar
        'KW', // Kuwait
        'OM', // Oman
        'BH', // Bahrain
        'YE', // Yemen
        'JO', // Jordan
        'LB', // Lebanon
        'SY', // Syria
        'EG', // Egypt
        'LY', // Libya
        'TN', // Tunisia
        'DZ', // Algeria
        'MA', // Morocco
        'SD', // Sudan
        'SO', // Somalia
        'DJ', // Djibouti
        'KM', // Comoros
        'MR', // Mauritania
    ];

    /**
     * Get user's IP address
     * 
     * @return string
     */
    public function get_user_ip(): string
    {
        // Check for various proxy headers
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // For X-Forwarded-For, get the first IP
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Detect country code from IP address
     * 
     * @param string|null $ip IP address (null = auto-detect)
     * @return string|false Country code (ISO 3166-1 alpha-2) or false on failure
     */
    public function detect_country(?string $ip = null): string|false
    {
        if ($ip === null) {
            $ip = $this->get_user_ip();
        }

        // Check if localhost/private IP
        if ($ip === '127.0.0.1' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'IR'; // Default to Iran for local testing
        }

        // Check cache first
        $cache_key = 'ht_geoip_' . md5($ip);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }

        // Try primary service
        $country_code = $this->query_primary_service($ip);
        
        // Try fallback service if primary fails
        if ($country_code === false) {
            $country_code = $this->query_fallback_service($ip);
        }

        // Cache the result
        if ($country_code !== false) {
            set_transient($cache_key, $country_code, self::CACHE_EXPIRATION);
        }

        return $country_code;
    }

    /**
     * Query primary GeoIP service (ipapi.co)
     * 
     * @param string $ip
     * @return string|false
     */
    private function query_primary_service(string $ip): string|false
    {
        $url = sprintf(self::GEOIP_SERVICE_URL, $ip);
        
        $response = wp_remote_get($url, [
            'timeout' => 5,
            'headers' => [
                'User-Agent' => 'HomayeTabesh-GeoLocation/1.0'
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code === 200 && strlen($body) === 2) {
            return strtoupper($body);
        }

        return false;
    }

    /**
     * Query fallback GeoIP service (ip-api.com)
     * 
     * @param string $ip
     * @return string|false
     */
    private function query_fallback_service(string $ip): string|false
    {
        $url = sprintf(self::FALLBACK_GEOIP_URL, $ip);
        
        $response = wp_remote_get($url, [
            'timeout' => 5,
            'headers' => [
                'User-Agent' => 'HomayeTabesh-GeoLocation/1.0'
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['countryCode']) && $data['status'] === 'success') {
            return strtoupper($data['countryCode']);
        }

        return false;
    }

    /**
     * Check if visitor is from an Arabic country
     * 
     * @param string|null $ip Optional IP address
     * @return bool
     */
    public function is_arabic_visitor(?string $ip = null): bool
    {
        $country_code = $this->detect_country($ip);
        
        if ($country_code === false) {
            return false;
        }

        $allowed_countries = $this->get_allowed_arabic_countries();
        
        return in_array($country_code, $allowed_countries, true);
    }

    /**
     * Get list of allowed Arabic countries from settings
     * 
     * @return array
     */
    public function get_allowed_arabic_countries(): array
    {
        $countries = get_option('ht_arabic_countries', self::DEFAULT_ARABIC_COUNTRIES);
        
        if (!is_array($countries) || empty($countries)) {
            return self::DEFAULT_ARABIC_COUNTRIES;
        }

        return $countries;
    }

    /**
     * Get default Arabic countries list
     * 
     * @return array
     */
    public static function get_default_arabic_countries(): array
    {
        return self::DEFAULT_ARABIC_COUNTRIES;
    }

    /**
     * Get country name in Persian
     * 
     * @param string $country_code ISO country code
     * @return string
     */
    public function get_country_name_persian(string $country_code): string
    {
        $countries = [
            'SA' => 'عربستان سعودی',
            'AE' => 'امارات متحده عربی',
            'IQ' => 'عراق',
            'QA' => 'قطر',
            'KW' => 'کویت',
            'OM' => 'عمان',
            'BH' => 'بحرین',
            'YE' => 'یمن',
            'JO' => 'اردن',
            'LB' => 'لبنان',
            'SY' => 'سوریه',
            'EG' => 'مصر',
            'LY' => 'لیبی',
            'TN' => 'تونس',
            'DZ' => 'الجزایر',
            'MA' => 'مراکش',
            'SD' => 'سودان',
            'SO' => 'سومالی',
            'DJ' => 'جیبوتی',
            'KM' => 'کومور',
            'MR' => 'موریتانی',
            'IR' => 'ایران',
        ];

        return $countries[$country_code] ?? $country_code;
    }

    /**
     * Get country name in Arabic
     * 
     * @param string $country_code ISO country code
     * @return string
     */
    public function get_country_name_arabic(string $country_code): string
    {
        $countries = [
            'SA' => 'المملكة العربية السعودية',
            'AE' => 'الإمارات العربية المتحدة',
            'IQ' => 'العراق',
            'QA' => 'قطر',
            'KW' => 'الكويت',
            'OM' => 'عمان',
            'BH' => 'البحرين',
            'YE' => 'اليمن',
            'JO' => 'الأردن',
            'LB' => 'لبنان',
            'SY' => 'سوريا',
            'EG' => 'مصر',
            'LY' => 'ليبيا',
            'TN' => 'تونس',
            'DZ' => 'الجزائر',
            'MA' => 'المغرب',
            'SD' => 'السودان',
            'SO' => 'الصومال',
            'DJ' => 'جيبوتي',
            'KM' => 'جزر القمر',
            'MR' => 'موريتانيا',
            'IR' => 'إيران',
        ];

        return $countries[$country_code] ?? $country_code;
    }

    /**
     * Clear GeoIP cache for specific IP or all
     * 
     * @param string|null $ip
     * @return void
     */
    public function clear_cache(?string $ip = null): void
    {
        if ($ip !== null) {
            $cache_key = 'ht_geoip_' . md5($ip);
            delete_transient($cache_key);
        } else {
            // Clear all GeoIP transients
            global $wpdb;
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE '_transient_ht_geoip_%' 
                OR option_name LIKE '_transient_timeout_ht_geoip_%'"
            );
        }
    }
}
