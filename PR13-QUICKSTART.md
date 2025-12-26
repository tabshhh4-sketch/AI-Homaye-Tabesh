# PR13 Quick Start Guide

## راهنمای سریع ناظر کل (Global Inspector)

---

### 🚀 شروع سریع

#### 1. دسترسی به داشبورد

```
پنل مدیریت WordPress → همای تابش → 🔍 ناظر کل
```

#### 2. اضافه کردن افزونه به نظارت

1. در صفحه "ناظر کل"، لیست افزونه‌های نصب شده را مشاهده کنید
2. روی دکمه "اضافه به نظارت" کنار افزونه مورد نظر کلیک کنید
3. صبر کنید تا متادیتا استخراج شود

#### 3. مشاهده فکت‌های استخراج شده

فکت‌های استخراج شده در بخش "فکت‌های استخراج شده" نمایش داده می‌شوند.

---

### 💻 استفاده از API

#### دریافت وضعیت ناظر

```javascript
fetch('/wp-json/homaye/v1/observer/status', {
    headers: {
        'X-WP-Nonce': wpNonce
    }
})
.then(res => res.json())
.then(data => console.log(data));
```

#### اضافه کردن افزونه به نظارت

```javascript
fetch('/wp-json/homaye/v1/observer/monitor/add', {
    method: 'POST',
    headers: {
        'X-WP-Nonce': wpNonce,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        plugin_path: 'woocommerce/woocommerce.php'
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

#### رفرش متادیتا

```javascript
fetch('/wp-json/homaye/v1/observer/refresh', {
    method: 'POST',
    headers: {
        'X-WP-Nonce': wpNonce
    }
})
.then(res => res.json())
.then(data => console.log(data));
```

---

### 🔧 استفاده در کد PHP

#### دریافت وضعیت ناظر

```php
$observer = HT_Global_Observer_Core::instance();
$summary = $observer->get_monitoring_summary();

echo "تعداد افزونه‌های تحت نظر: " . $summary['monitored_count'];
```

#### استخراج متادیتا

```php
$engine = new HT_Metadata_Mining_Engine();
$metadata = $engine->get_metadata_for_ai();

// نمایش متادیتای WooCommerce
if (isset($metadata['woocommerce'])) {
    print_r($metadata['woocommerce']);
}
```

#### دریافت فکت‌های Knowledge Base

```php
$kb = new HT_Knowledge_Base();
$facts = $kb->get_plugin_facts();

foreach ($facts['plugins'] as $slug => $plugin) {
    echo "افزونه: " . $slug . "\n";
    print_r($plugin['settings']);
}
```

#### استفاده از Sanitizer

```php
$sanitizer = new HT_Safety_Data_Sanitizer();

// فیلتر داده‌های حساس
$safe_data = $sanitizer->sanitize_array($data);

// بررسی امنیت
if ($sanitizer->is_safe_for_ai($data)) {
    echo "داده امن است";
}
```

---

### 📋 سناریوهای کاربردی

#### سناریو 1: نظارت بر تغییرات قیمت WooCommerce

```php
// وقتی قیمت محصولی تغییر می‌کند، ناظر خودکار آن را ثبت می‌کند
// فکت جدید در knowledge base اضافه می‌شود:
// "قیمت محصول X به 50000 تومان تغییر کرد"
```

#### سناریو 2: شناسایی فعال شدن جشنواره

```php
// وقتی یک کد تخفیف جدید در WooCommerce ایجاد می‌شود
// ناظر تغییر را تشخیص داده و به knowledge base اضافه می‌کند
// هما می‌تواند به کاربران بگوید: "الان 20% تخفیف داریم!"
```

#### سناریو 3: پایش افزونه تابش

```php
// اگر افزونه تابش (چاپخانه) نصب است:
$scanner = new HT_Plugin_Scanner();
$scanner->add_monitored_plugin('tabesh-order-system/tabesh.php');

// حالا هر تغییری در تنظیمات تابش خودکار ثبت می‌شود
```

---

### ⚙️ تنظیمات پیشرفته

#### تغییر بازه رفرش خودکار

```php
// در wp-config.php یا functions.php

// تغییر از twicedaily به hourly
remove_action('homa_refresh_plugin_metadata', [HT_Metadata_Mining_Engine::class, 'metadata_refresh_cron']);
wp_clear_scheduled_hook('homa_refresh_plugin_metadata');

if (!wp_next_scheduled('homa_refresh_plugin_metadata')) {
    wp_schedule_event(time(), 'hourly', 'homa_refresh_plugin_metadata');
}
add_action('homa_refresh_plugin_metadata', [HT_Metadata_Mining_Engine::class, 'metadata_refresh_cron']);
```

#### افزودن کلمات کلیدی حساس سفارشی

```php
// در functions.php
add_filter('ht_sensitive_keywords', function($keywords) {
    $keywords[] = 'my_custom_key';
    $keywords[] = 'secret_data';
    return $keywords;
});
```

---

### 🐛 عیب‌یابی

#### مشکل: افزونه در لیست نظارت قرار نمی‌گیرد

**راه‌حل:**
1. بررسی کنید که افزونه نصب و فعال است
2. کش مرورگر را پاک کنید
3. لاگ WordPress را بررسی کنید

#### مشکل: متادیتا استخراج نمی‌شود

**راه‌حل:**
1. از داشبورد "ناظر کل"، دکمه "به‌روزرسانی متادیتا" را بزنید
2. بررسی کنید که افزونه تنظیماتی در wp_options دارد
3. لاگ PHP را بررسی کنید

#### مشکل: فکت‌ها نمایش داده نمی‌شوند

**راه‌حل:**
```php
// پاکسازی کش
delete_transient('homa_recent_facts');
delete_option('ht_plugin_facts_cache');

// رفرش دستی
$kb = new HT_Knowledge_Base();
HT_Knowledge_Base::auto_sync_metadata();
```

---

### 📊 مانیتورینگ و لاگ

#### مشاهده لاگ تغییرات

```sql
SELECT * FROM wp_homa_observer_log 
ORDER BY created_at DESC 
LIMIT 20;
```

#### مشاهده فکت‌های ثبت شده

```sql
SELECT * FROM wp_homa_knowledge 
WHERE source = 'global_observer' 
ORDER BY created_at DESC 
LIMIT 20;
```

#### پاکسازی دستی لاگ‌های قدیمی

```php
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->prefix}homa_observer_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
```

---

### 🎯 بهترین شیوه‌ها

1. **افزونه‌های مهم را تحت نظر قرار دهید**: WooCommerce، افزونه‌های پرداخت، افزونه‌های اختصاصی

2. **بررسی دوره‌ای فکت‌ها**: حداقل هفته‌ای یکبار فکت‌های استخراج شده را بررسی کنید

3. **تست امنیت**: اطمینان حاصل کنید که اطلاعات حساس فیلتر می‌شوند

4. **پایش Performance**: در صورت کند شدن سایت، بازه رفرش را افزایش دهید

5. **Backup**: قبل از اضافه کردن افزونه‌های جدید به نظارت، از دیتابیس backup بگیرید

---

### 📞 پشتیبانی

در صورت بروز مشکل:
1. لاگ خطاهای WordPress را بررسی کنید
2. فایل `validate-pr13.html` را اجرا کنید
3. از تست‌های داخلی استفاده کنید

---

**نسخه**: 1.0.0  
**تاریخ**: 2024-01-15  
**نویسنده**: Tabshhh4
