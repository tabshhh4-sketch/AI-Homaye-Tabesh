# Chat Memory Implementation Documentation

## مستندات پیاده‌سازی حافظه گفتگو - Chat Memory Persistence

این سند راهنمای کامل برای پیاده‌سازی و تست سیستم حافظه گفتگوی پایدار هما می‌باشد.

---

## 🎯 Problem Solved

**مشکل اصلی:**
- هما پیامهای قبلی را فراموش می‌کرد (حتی برای ادمین)
- greeting در هر بار بازدید صفحه تکرار می‌شد
- حافظه session در دیتابیس به درستی ذخیره و بازیابی نمی‌شد

**راه حل:**
- ایجاد جدول `wp_homaye_chat_memory` برای ذخیره پیامها
- بهبود مدیریت session cookies
- اتصال کامل frontend و backend برای ذخیره و بازیابی خودکار
- منطق هوشمند برای جلوگیری از تکرار greeting

---

## 📊 Database Schema

### جدول: `wp_homaye_chat_memory`

```sql
CREATE TABLE wp_homaye_chat_memory (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    session_id varchar(100) NOT NULL,
    user_identifier varchar(100) NOT NULL,
    user_role varchar(20) DEFAULT 'guest',
    message_type varchar(20) NOT NULL,
    message_content text NOT NULL,
    ai_metadata json DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY session_id (session_id),
    KEY user_identifier (user_identifier),
    KEY message_type (message_type),
    KEY created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**فیلدها:**
- `session_id`: توکن منحصربفرد session (مثال: `user_1` یا `guest_abc123...`)
- `user_identifier`: شناسه کاربر (برای logged in users مشابه session_id)
- `user_role`: نقش کاربر (`admin`, `customer`, `guest`)
- `message_type`: نوع پیام (`user` یا `assistant`)
- `message_content`: محتوای پیام
- `ai_metadata`: اطلاعات اضافی (actions, commands, etc.) در فرمت JSON
- `created_at`: زمان ایجاد پیام

---

## 🔌 REST API Endpoints

### 1. Get Chat History (بازیابی پیامها)

```bash
# GET Request
curl -X GET "https://yourdomain.com/wp-json/homaye-tabesh/v1/chat/memory?limit=50" \
  -H "X-WP-Nonce: YOUR_NONCE_HERE"
```

**پارامترها:**
- `limit` (optional): تعداد پیام‌های برگشتی (پیش‌فرض: 50)
- `session_id` (optional): session خاص (اگر نباشد از session فعلی استفاده می‌شود)

**پاسخ نمونه:**
```json
{
  "success": true,
  "messages": [
    {
      "type": "assistant",
      "content": "سلام! به چاپکو خوش آمدید",
      "metadata": {
        "actions": [
          {"label": "معرفی خدمات", "action": "show_services"}
        ]
      },
      "timestamp": "2024-01-15 10:30:00"
    },
    {
      "type": "user",
      "content": "میخوام یک کتاب چاپ کنم",
      "metadata": [],
      "timestamp": "2024-01-15 10:31:00"
    }
  ],
  "has_history": true,
  "session_token": "guest_a1b2c3d4...",
  "count": 2
}
```

### 2. Save Chat Message (ذخیره پیام)

```bash
# POST Request
curl -X POST "https://yourdomain.com/wp-json/homaye-tabesh/v1/chat/memory" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE_HERE" \
  -d '{
    "message_type": "user",
    "message_content": "سلام، میخوام درباره خدمات بدونم",
    "ai_metadata": {}
  }'
```

**پاسخ نمونه:**
```json
{
  "success": true,
  "message": "Chat message saved successfully",
  "session_token": "guest_a1b2c3d4..."
}
```

### 3. Clear Chat History (پاک کردن تاریخچه)

```bash
# POST Request
curl -X POST "https://yourdomain.com/wp-json/homaye-tabesh/v1/chat/memory/clear" \
  -H "X-WP-Nonce: YOUR_NONCE_HERE"
```

**پاسخ نمونه:**
```json
{
  "success": true,
  "message": "Chat history cleared successfully"
}
```

### 4. Main Chat Endpoint (ارسال پیام و دریافت پاسخ)

این endpoint به صورت خودکار پیام کاربر و پاسخ AI را ذخیره می‌کند:

```bash
# POST Request
curl -X POST "https://yourdomain.com/wp-json/homaye/v1/ai/chat" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE_HERE" \
  -d '{
    "message": "میخوام یک کتاب چاپ کنم",
    "persona": null,
    "context": {
      "page": "/services/",
      "formData": {},
      "currentInput": null
    }
  }'
```

---

## 🔍 SQL Test Queries

### بررسی تمام پیام‌های یک session

```sql
SELECT 
    message_type,
    message_content,
    user_role,
    created_at
FROM wp_homaye_chat_memory 
WHERE session_id = 'YOUR_SESSION_ID'
ORDER BY created_at ASC;
```

### شمارش پیام‌های هر session

```sql
SELECT 
    session_id,
    user_role,
    COUNT(*) as message_count,
    MIN(created_at) as first_message,
    MAX(created_at) as last_message
FROM wp_homaye_chat_memory 
GROUP BY session_id, user_role
ORDER BY last_message DESC;
```

### پیدا کردن sessions فعال اخیر

```sql
SELECT 
    session_id,
    user_identifier,
    user_role,
    COUNT(*) as messages,
    MAX(created_at) as last_activity
FROM wp_homaye_chat_memory 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY session_id, user_identifier, user_role
ORDER BY last_activity DESC;
```

### بررسی greeting messages

```sql
SELECT 
    session_id,
    message_content,
    created_at
FROM wp_homaye_chat_memory 
WHERE message_type = 'assistant' 
  AND message_content LIKE '%سلام%'
  OR message_content LIKE '%خوش آمدید%'
ORDER BY created_at DESC
LIMIT 20;
```

### پاک کردن پیام‌های قدیمی (بیش از 7 روز)

```sql
DELETE FROM wp_homaye_chat_memory 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### آمار کلی حافظه گفتگو

```sql
SELECT 
    COUNT(DISTINCT session_id) as total_sessions,
    COUNT(*) as total_messages,
    COUNT(CASE WHEN message_type = 'user' THEN 1 END) as user_messages,
    COUNT(CASE WHEN message_type = 'assistant' THEN 1 END) as ai_messages,
    COUNT(CASE WHEN user_role = 'admin' THEN 1 END) as admin_messages,
    COUNT(CASE WHEN user_role = 'customer' THEN 1 END) as customer_messages,
    COUNT(CASE WHEN user_role = 'guest' THEN 1 END) as guest_messages
FROM wp_homaye_chat_memory;
```

---

## 🧪 JavaScript Testing Examples

### Test 1: بررسی session token

```javascript
// در Browser Console
console.log('Session Token:', document.cookie.match(/homa_session_token=([^;]+)/)?.[1]);
```

### Test 2: بازیابی تاریخچه گفتگو

```javascript
// در Browser Console
fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
  headers: {
    'X-WP-Nonce': window.homayeParallelUIConfig?.nonce || ''
  }
})
.then(res => res.json())
.then(data => {
  console.log('Chat History:', data);
  console.log('Message Count:', data.count);
  console.log('Has History:', data.has_history);
});
```

### Test 3: ذخیره یک پیام تستی

```javascript
// در Browser Console
fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': window.homayeParallelUIConfig?.nonce || ''
  },
  body: JSON.stringify({
    message_type: 'user',
    message_content: 'این یک پیام تستی است',
    ai_metadata: { test: true }
  })
})
.then(res => res.json())
.then(data => console.log('Save Result:', data));
```

### Test 4: پاک کردن تاریخچه

```javascript
// در Browser Console
fetch('/wp-json/homaye-tabesh/v1/chat/memory/clear', {
  method: 'POST',
  headers: {
    'X-WP-Nonce': window.homayeParallelUIConfig?.nonce || ''
  }
})
.then(res => res.json())
.then(data => console.log('Clear Result:', data));
```

---

## 🎭 Test Scenarios

### Scenario 1: Guest User - First Visit

**مراحل:**
1. پاک کردن cookies مرورگر
2. بازدید از سایت
3. باز کردن sidebar هما
4. بررسی که greeting نمایش داده می‌شود
5. ارسال یک پیام
6. بررسی ذخیره در database
7. Refresh صفحه
8. بررسی که پیامها بازیابی می‌شوند و greeting تکرار نمی‌شود

**کد تست:**
```javascript
// قبل از refresh
const messagesBeforeRefresh = await fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
  headers: { 'X-WP-Nonce': window.homayeParallelUIConfig.nonce }
}).then(r => r.json());

console.log('Messages before refresh:', messagesBeforeRefresh.count);

// بعد از refresh
// صفحه را refresh کنید سپس:
const messagesAfterRefresh = await fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
  headers: { 'X-WP-Nonce': window.homayeParallelUIConfig.nonce }
}).then(r => r.json());

console.log('Messages after refresh:', messagesAfterRefresh.count);
console.log('Messages match:', messagesBeforeRefresh.count === messagesAfterRefresh.count);
```

### Scenario 2: Admin User - Multiple Sessions

**مراحل:**
1. لاگین به عنوان admin
2. ارسال چند پیام
3. لاگ اوت و لاگین دوباره
4. بررسی که تاریخچه همچنان موجود است

**SQL بررسی:**
```sql
SELECT 
    session_id,
    COUNT(*) as messages,
    user_role
FROM wp_homaye_chat_memory 
WHERE user_identifier LIKE 'user_%'
GROUP BY session_id, user_role;
```

### Scenario 3: Guest to Logged-in Transition

**مراحل:**
1. بازدید به عنوان guest و ارسال پیام
2. ثبت‌نام یا لاگین
3. بررسی که session_id تغییر کرده است
4. بررسی که پیامهای قدیمی (guest) همچنان قابل مشاهده هستند

**توضیح:**
- Session token برای guest: `guest_abc123...`
- Session token بعد از login: `user_1`
- هر دو session در database ذخیره می‌شوند
- Frontend می‌تواند تاریخچه مرتبط با هر session را بارگذاری کند

---

## 🔧 Backend PHP Methods

### HT_Vault_Manager Methods

```php
// Get current session token
$session_token = HT_Vault_Manager::get_session_token();

// Ensure session token is properly set
$session_token = HT_Vault_Manager::ensure_session_token();

// Store a chat message
$success = HT_Vault_Manager::store_chat_message(
    'user',  // message_type
    'سلام، میخوام کتاب چاپ کنم',  // message_content
    ['intent' => 'book_printing']  // ai_metadata (optional)
);

// Get chat messages for current session
$messages = HT_Vault_Manager::get_chat_messages(50);  // limit: 50

// Get messages for specific session
$messages = HT_Vault_Manager::get_chat_messages(50, 'guest_abc123...');

// Check if chat history exists
$has_history = HT_Vault_Manager::has_chat_history();

// Clear chat history
$success = HT_Vault_Manager::clear_chat_history();
```

---

## 🎨 Frontend Integration

### React Component Usage

```jsx
// در HomaSidebar component

// 1. بارگذاری تاریخچه از database در mount
useEffect(() => {
    loadChatHistoryFromDatabase();
}, []);

// 2. Function برای بارگذاری
const loadChatHistoryFromDatabase = async () => {
    const response = await fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
        headers: { 'X-WP-Nonce': window.homayeParallelUIConfig?.nonce }
    });
    
    const data = await response.json();
    if (data.success && data.messages.length > 0) {
        // Add messages to state
        data.messages.forEach(msg => addMessage({
            id: Date.now() + Math.random(),
            type: msg.type,
            content: msg.content,
            timestamp: new Date(msg.timestamp)
        }));
    }
};

// 3. جلوگیری از نمایش greeting در صورت وجود تاریخچه
const fetchUserRoleContext = async () => {
    // ...
    // Only show greeting if no messages exist
    if (messages.length === 0 && data.welcome_message) {
        addMessage(welcomeMessage);
        saveChatMessageToDatabase('assistant', data.welcome_message);
    }
};
```

---

## 🚀 Performance Notes

### بهینه‌سازی پرس‌وجوها

1. **Index ها**: جدول دارای index های مناسب برای:
   - `session_id`: برای فیلتر سریع پیامها
   - `user_identifier`: برای جستجوی پیامهای یک کاربر
   - `created_at`: برای مرتب‌سازی زمانی

2. **Limit در بازیابی**: پیش‌فرض 50 پیام آخر را بازیابی می‌کند

3. **Session Token Caching**: token در cookie ذخیره می‌شود برای جلوگیری از تولید مجدد

### پاکسازی خودکار

برای جلوگیری از رشد بی‌رویه database، می‌توانید یک cron job تنظیم کنید:

```php
// در wp-config.php یا plugin
add_action('homa_daily_cleanup', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'homaye_chat_memory';
    
    // Delete messages older than 30 days
    $wpdb->query("
        DELETE FROM $table 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
});

// Schedule the event
if (!wp_next_scheduled('homa_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'homa_daily_cleanup');
}
```

---

## 🐛 Troubleshooting

### مشکل: پیامها ذخیره نمی‌شوند

**بررسی:**
```sql
SHOW CREATE TABLE wp_homaye_chat_memory;
```

اگر جدول وجود ندارد:
1. افزونه را deactivate و سپس activate کنید
2. یا مستقیماً SQL را اجرا کنید

### مشکل: Session token تغییر می‌کند

**بررسی Cookie:**
```javascript
console.log(document.cookie);
```

**راه حل:**
- مطمئن شوید که `setcookie` با پارامترهای درست فراخوانی می‌شود
- `httponly` flag را check کنید
- Path را به `/` تنظیم کنید

### مشکل: Greeting هنوز تکرار می‌شود

**بررسی:**
```javascript
// در console قبل از باز کردن sidebar
fetch('/wp-json/homaye-tabesh/v1/chat/memory')
  .then(r => r.json())
  .then(d => console.log('Has history:', d.has_history, 'Count:', d.count));
```

اگر `has_history = true` اما greeting تکرار می‌شود:
- مطمئن شوید که `messages.length === 0` در fetchUserRoleContext چک می‌شود
- از cache browser نباشد

---

## 📝 Migration Notes

اگر قبلاً نسخه قدیمی‌تر افزونه را داشتید:

1. جدول جدید به صورت خودکار ایجاد می‌شود
2. تاریخچه localStorage همچنان کار می‌کند (به عنوان backup)
3. پیامهای جدید در database ذخیره می‌شوند

برای migrate کردن تاریخچه localStorage به database:

```javascript
// در Browser Console
const history = JSON.parse(localStorage.getItem('homa_chat_history') || '{}');
if (history.messages) {
    for (const msg of history.messages) {
        await fetch('/wp-json/homaye-tabesh/v1/chat/memory', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.homayeParallelUIConfig.nonce
            },
            body: JSON.stringify({
                message_type: msg.type,
                message_content: msg.content,
                ai_metadata: msg.actions ? { actions: msg.actions } : {}
            })
        });
    }
    console.log('Migration complete!');
}
```

---

## ✅ Verification Checklist

- [ ] جدول `wp_homaye_chat_memory` در database ایجاد شده
- [ ] Session token در cookie به درستی set می‌شود
- [ ] پیامهای کاربر و AI در database ذخیره می‌شوند
- [ ] بعد از refresh صفحه، پیامها بازیابی می‌شوند
- [ ] Greeting فقط یک بار (اولین بار) نمایش داده می‌شود
- [ ] برای admin و guest به درستی کار می‌کند
- [ ] Metadata و actions به درستی ذخیره می‌شوند
- [ ] API endpoints پاسخ صحیح می‌دهند

---

## 📞 Support

در صورت بروز مشکل:
1. Error logs WordPress را بررسی کنید
2. Browser console را چک کنید
3. SQL queries تستی را اجرا کنید
4. با تیم توسعه تماس بگیرید

---

**تاریخ ایجاد:** 2024-12-28  
**نسخه:** 1.0.0  
**نویسنده:** Homa AI Development Team
