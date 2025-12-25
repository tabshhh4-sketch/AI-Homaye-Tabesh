# PR7 Quick Reference - Omni-Store

## 🚀 Quick Start

### For Developers

#### PHP Usage

```php
// Store data
HT_Vault_Manager::store('tirage', 500);

// Get data
$value = HT_Vault_Manager::get('tirage');

// Save snapshot
HT_Vault_Manager::save_session_snapshot(
    $current_url,
    ['tirage' => 500, 'paper' => 'glossy'],
    'کاربر در حال بررسی است'
);

// Track interest
HT_Vault_Manager::track_interest('book-printing', 5, 'torob');

// Get memory for AI
$memory = HT_Vault_Manager::get_memory_summary();

// Analyze persona
$persona = HT_Persona_Engine::analyze_user_persona();

// Compress messages
$compressed = HT_Context_Compressor::compress_messages($messages);
```

#### JavaScript Usage

```javascript
// Update and sync
window.HomaStore.update({
    field: 'tirage',
    value: 500
});

// Get value
const tirage = window.HomaStore.get('tirage');

// Restore session
await window.HomaStore.restore();

// Save snapshot
await window.HomaStore.saveSnapshot('Chat summary');

// Track interest
await window.HomaStore.trackInterest('book-printing', 5);

// Get persona
const persona = await window.HomaStore.getPersona();

// Clear vault
await window.HomaStore.clear();
```

#### Event Bus Integration

```javascript
// Form change auto-syncs
window.Homa.emit('site:input_change', {
    field: 'tirage',
    value: 500
});

// Price calculation auto-snapshots
window.Homa.emit('calculator:price_calculated', {
    price: 150000
});

// Listen for restore prompt
window.Homa.on('vault:restore_prompt', (data) => {
    if (confirm(data.message)) {
        // Restore form fields
    }
});
```

## 📚 Database Tables

### wp_homa_vault
Short-term flash memory for current states
```sql
SELECT * FROM wp_homa_vault WHERE session_token = 'user_123';
```

### wp_homa_sessions
Mid-term working memory with snapshots
```sql
SELECT * FROM wp_homa_sessions WHERE session_token = 'user_123' AND expires_at > NOW();
```

### wp_homa_user_interests
Long-term archive for persona & interests
```sql
SELECT * FROM wp_homa_user_interests 
WHERE user_id_or_token = 'user_123' 
ORDER BY interest_score DESC;
```

## 🌐 REST API Endpoints

Base URL: `/wp-json/homaye-tabesh/v1`

### Sync Data
```bash
curl -X POST /wp-json/homaye-tabesh/v1/vault/sync \
  -H "Content-Type: application/json" \
  -d '{"field": "tirage", "value": 500, "page_url": "/calculator"}'
```

### Restore Session
```bash
curl /wp-json/homaye-tabesh/v1/vault/restore
```

### Get Persona
```bash
curl /wp-json/homaye-tabesh/v1/persona/analyze
```

### Track Interest
```bash
curl -X POST /wp-json/homaye-tabesh/v1/interest/track \
  -H "Content-Type: application/json" \
  -d '{"category": "book-printing", "score": 5, "source": "organic"}'
```

### Get Memory Summary
```bash
curl /wp-json/homaye-tabesh/v1/memory/summary
```

## 🎭 Persona Types

| Persona | Persian | Strategy |
|---------|---------|----------|
| `author` | نویسنده | Friendly, quality-focused |
| `publisher` | ناشر | Professional, volume pricing |
| `designer` | گرافیست | Technical, quality details |
| `loyal_customer` | مشتری وفادار | Warm, loyalty rewards |
| `casual_browser` | استعلامگیرنده گذرا | Informative, competitive pricing |
| `price_sensitive` | حساس به قیمت | Value-focused, discounts |

## 🔧 Common Tasks

### Clear Expired Sessions (Manual)
```php
$deleted = HT_Vault_Manager::cleanup_expired_sessions();
echo "Deleted {$deleted} expired sessions";
```

### Get User Session Token
```php
$token = HT_Vault_Manager::get_session_token();
// Returns: "user_123" or "guest_abc123..."
```

### Check if from Torob
```php
if (HT_Persona_Engine::is_from_torob()) {
    // Apply price-sensitive strategy
}
```

### Generate "Who Am I?" Response
```php
$response = HT_Persona_Engine::generate_who_am_i_response();
// Returns personalized message based on user history
```

### Compress Context for AI
```php
$messages = [
    ['role' => 'user', 'content' => 'میخوام کتاب چاپ کنم'],
    ['role' => 'assistant', 'content' => 'چند صفحه است?']
];
$compressed = HT_Context_Compressor::compress_messages($messages);
```

## 🐛 Debugging

### Check if Vault is Loaded
```javascript
console.log(window.Homa.vault);
console.log(window.HomaStore);
console.log(window.HomaAPI);
```

### View Current Cache
```javascript
console.log(window.HomaStore.getAll());
```

### Test API Connection
```javascript
window.HomaAPI.get('/vault/restore').then(console.log);
```

### View Event History
```javascript
// In console (if event bus has history)
console.log(window.Homa.eventHistory);
```

## ⚙️ Configuration

### Session Token Cookie
- **Name**: `homa_session_token`
- **Expires**: 48 hours
- **Path**: `/`

### Debounce Delay
Located in `homa-vault.js`:
```javascript
debounceDelay: 1000  // milliseconds
```

### Token Limit
Located in `HT_Context_Compressor.php`:
```php
private const MAX_TOKENS = 500;
```

### Cleanup Schedule
Located in `HT_Core.php`:
```php
wp_schedule_event(time(), 'daily', 'homa_cleanup_expired_sessions');
```

## 🧪 Testing Checklist

- [ ] Database tables created
- [ ] PHP classes loaded without errors
- [ ] JavaScript loaded in browser
- [ ] REST API endpoints accessible
- [ ] Form changes trigger sync
- [ ] Session restoration works
- [ ] Persona detection works
- [ ] Memory summary includes context
- [ ] Cleanup cron job scheduled
- [ ] Cross-device sync works

## 📝 Integration Example

Complete example of integrating Omni-Store with a form:

```html
<form id="print-calculator">
    <input type="number" name="tirage" value="500">
    <select name="paper_type">
        <option value="80gr">80 گرم</option>
        <option value="glossy">گلاسه</option>
    </select>
    <button type="submit">محاسبه قیمت</button>
</form>

<script>
// Auto-sync on change
document.getElementById('print-calculator').addEventListener('change', (e) => {
    window.Homa.emit('site:input_change', {
        field: e.target.name,
        value: e.target.value
    });
});

// Save snapshot on submit
document.getElementById('print-calculator').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Calculate price...
    const price = 150000;
    
    // Emit price calculated event (auto-snapshots)
    window.Homa.emit('calculator:price_calculated', { price });
    
    // Track interest
    await window.HomaStore.trackInterest('book-printing', 3);
});

// Auto-restore on load
window.addEventListener('DOMContentLoaded', async () => {
    const data = await window.HomaStore.restore();
    
    if (data && data.session && data.session.form_snapshot) {
        if (confirm('مشخصات قبلی رو لود کنم؟')) {
            // Fill form with restored data
            const form = document.getElementById('print-calculator');
            Object.entries(data.session.form_snapshot).forEach(([key, value]) => {
                const field = form.elements[key];
                if (field) field.value = value;
            });
        }
    }
});
</script>
```

## 🔗 Related PRs

- **PR6.5**: Event Bus (Required)
- **PR5**: Conversion Sessions
- **PR4**: Decision Triggers
- **PR3**: Perception Bridge
- **PR2**: Knowledge Base
- **PR1**: Core Infrastructure

## 📞 Support

For issues or questions:
1. Check `PR7-IMPLEMENTATION.md` for detailed docs
2. Open `validate-pr7.html` for interactive testing
3. Review console logs for errors
4. Check database tables for data

---

**Version**: PR7 (Omni-Store)  
**Status**: ✅ Production Ready  
**License**: GPL v3 or later
