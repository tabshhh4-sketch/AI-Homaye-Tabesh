# PR7 Implementation Guide - Omni-Store Infrastructure

## Overview

PR7 implements a sophisticated multi-layered memory engine called **Omni-Store** that provides context persistence, behavior graph analysis, and cross-device synchronization for the Homa AI assistant.

## Architecture

### Three-Layer Memory System

```
┌─────────────────────────────────────────────────────────┐
│                    OMNI-STORE                            │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  Layer 1: SHORT-TERM (Flash Memory)                      │
│  ├── wp_homa_vault                                       │
│  ├── Current form states in RAM                          │
│  └── Real-time user input tracking                       │
│                                                           │
│  Layer 2: MID-TERM (Working Memory)                      │
│  ├── wp_homa_sessions                                    │
│  ├── Session snapshots                                   │
│  ├── Compressed chat summaries                           │
│  └── Form data snapshots                                 │
│                                                           │
│  Layer 3: LONG-TERM (Archive Memory)                     │
│  ├── wp_homa_user_interests                              │
│  ├── User persona profiles                               │
│  ├── Category interest scores                            │
│  └── Behavioral patterns                                 │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

## Database Schema

### Table 1: wp_homa_vault (Short-term Flash)

```sql
CREATE TABLE wp_homa_vault (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    session_token VARCHAR(100),
    context_key VARCHAR(50),      -- 'calculator_state', 'form_field_tirage'
    context_value JSON,            -- Stores any data structure
    ai_insight TEXT,               -- AI's analysis of this data
    updated_at TIMESTAMP
);
```

**Purpose**: Store current form states and real-time user interactions.

### Table 2: wp_homa_sessions (Mid-term Working)

```sql
CREATE TABLE wp_homa_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_token VARCHAR(100) UNIQUE,
    last_url TEXT,
    form_snapshot JSON,            -- Complete form state at snapshot
    chat_summary TEXT,             -- AI-compressed conversation
    updated_at DATETIME,
    expires_at DATETIME            -- 48-hour TTL
);
```

**Purpose**: Store session snapshots for restoration after page refresh or device switch.

### Table 3: wp_homa_user_interests (Long-term Archive)

```sql
CREATE TABLE wp_homa_user_interests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id_or_token VARCHAR(100),
    category_slug VARCHAR(50),
    interest_score INT,            -- Increments with each interaction
    source_referral VARCHAR(50),   -- 'torob', 'google', 'organic'
    last_interaction TIMESTAMP,
    UNIQUE KEY (user_id_or_token, category_slug)
);
```

**Purpose**: Build behavioral graph for persona detection and interest tracking.

## PHP Components

### 1. HT_Vault_Manager.php

**Core Storage Manager**

```php
// Store a value
HT_Vault_Manager::store('tirage', 500);

// Retrieve a value
$tirage = HT_Vault_Manager::get('tirage');

// Save session snapshot
HT_Vault_Manager::save_session_snapshot(
    $current_url,
    ['tirage' => 500, 'paper' => 'glossy'],
    'کاربر در حال بررسی چاپ کتاب است'
);

// Track interest
HT_Vault_Manager::track_interest('book-printing', 5, 'torob');

// Get memory summary for AI
$summary = HT_Vault_Manager::get_memory_summary();
```

### 2. HT_Context_Compressor.php

**Summarization Engine**

```php
// Compress chat messages
$messages = [
    ['role' => 'user', 'content' => 'میخوام کتاب چاپ کنم'],
    ['role' => 'assistant', 'content' => 'چند صفحه است؟'],
    // ... more messages
];

$compressed = HT_Context_Compressor::compress_messages($messages);
// Result: "کاربر علاقه‌مند به چاپ است. کاربر درخواست کرد: کتاب"

// Enrich prompt with context
$enriched = HT_Context_Compressor::enrich_prompt(
    'قیمت چاپ چقدر میشه?',
    [
        'session' => ['tirage' => 500],
        'interests' => [['category' => 'book-printing', 'score' => 10]]
    ]
);
```

### 3. HT_Persona_Engine.php

**Behavior Analysis**

```php
// Analyze user persona
$persona = HT_Persona_Engine::analyze_user_persona();
// Returns: [
//   'persona' => 'author',
//   'label' => 'نویسنده',
//   'confidence' => 75,
//   'description' => '...'
// ]

// Get persona strategy
$strategy = HT_Persona_Engine::get_persona_strategy('author');
// Returns: ['tone' => 'friendly', 'focus' => 'quality', ...]

// Check if from Torob
if (HT_Persona_Engine::is_from_torob()) {
    // Apply price-sensitive strategy
}

// Generate "Who am I?" response
$response = HT_Persona_Engine::generate_who_am_i_response();
```

### 4. HT_Vault_REST_API.php

**REST API Endpoints**

All endpoints are under `/wp-json/homaye-tabesh/v1/`:

- `POST /vault/sync` - Sync vault data
- `GET /vault/restore` - Restore session data
- `POST /vault/clear` - Clear all data
- `POST /session/snapshot` - Save snapshot
- `GET /persona/analyze` - Get persona analysis
- `POST /interest/track` - Track category interest
- `GET /memory/summary` - Get memory summary for AI
- `POST /context/compress` - Compress chat context

## JavaScript Components

### 1. homa-vault.js

**Client-side Storage Manager**

```javascript
// Update local cache and sync to server
window.HomaStore.update({
    field: 'tirage',
    value: 500
});

// Get value from cache
const tirage = window.HomaStore.get('tirage');

// Restore from server
const data = await window.HomaStore.restore();

// Save snapshot
await window.HomaStore.saveSnapshot('کاربر در حال بررسی است');

// Track interest
await window.HomaStore.trackInterest('book-printing', 5, 'organic');

// Get persona
const persona = await window.HomaStore.getPersona();

// Clear vault
await window.HomaStore.clear();
```

### 2. Event Bus Integration

The vault automatically connects to the Event Bus:

```javascript
// Automatically syncs on form changes
window.Homa.emit('site:input_change', {
    field: 'tirage',
    value: 500
});

// Automatically saves snapshot on price calculation
window.Homa.emit('calculator:price_calculated', {
    price: 150000
});

// Auto-restore on page load
window.addEventListener('DOMContentLoaded', async () => {
    const restored = await window.HomaStore.restore();
    if (restored && restored.session) {
        // Show restore prompt
        window.Homa.emit('vault:restore_prompt', {
            message: 'مشخصات قبلی رو لود کنم؟'
        });
    }
});
```

## Integration with AI Prompts

The Omni-Store memory is automatically injected into AI prompts via `HT_Prompt_Builder_Service`:

```php
// In HT_Prompt_Builder_Service::build_system_instruction()
private function get_memory_context(): string
{
    $memory_summary = HT_Vault_Manager::get_memory_summary();
    $persona_prefix = HT_Persona_Engine::get_persona_prompt_prefix();
    
    return "## حافظه و زمینه\n" . $memory_summary . "\n" . $persona_prefix;
}
```

Example enriched prompt:

```
## حافظه و زمینه (Omni-Store Memory)

Context from current session:
- tirage: 500
- paper_type: "glossy"
- binding: "paperback"

User interests: book-printing (score: 10), invoice-printing (score: 3)

توجه: تحلیل رفتار نشان می‌دهد که کاربر احتمالاً یک نویسنده است.
استراتژی پیشنهادی: لحن friendly and supportive، تمرکز بر quality and personal attention.

پیام فعلی: قیمت چاپ چقدر میشه؟
```

## Security & Data Management

### Session Token Management

```php
// Get or create session token
$token = HT_Vault_Manager::get_session_token();

// For logged-in users: "user_{user_id}"
// For guests: "guest_{random_hash}"
```

### Data Expiration

- Guest sessions expire after 48 hours
- Automatic cleanup via daily cron job:

```php
wp_schedule_event(time(), 'daily', 'homa_cleanup_expired_sessions');
add_action('homa_cleanup_expired_sessions', [HT_Vault_Manager::class, 'cleanup_expired_sessions']);
```

### Data Sanitization

All user input is sanitized before storage:

```php
$data = [
    'context_value' => wp_json_encode($context_value),  // JSON encoded
    'session_token' => sanitize_text_field($session_token)
];
```

### Conflict Resolution

Timestamp-based conflict resolution:

```sql
-- Always use latest data
WHERE updated_at = (SELECT MAX(updated_at) FROM ...)
```

## Testing

### Using validate-pr7.html

Open `validate-pr7.html` in your browser to test:

1. **Database Tables** - Verify all tables exist
2. **REST API** - Test all endpoints
3. **Form Demo** - Simulate user input and see real-time sync
4. **Persona Engine** - Track interests and analyze persona
5. **Memory Restoration** - Test session recovery

### Manual Testing

```php
// Test 1: Store and retrieve
HT_Vault_Manager::store('test', ['value' => 123]);
$result = HT_Vault_Manager::get('test');
// Should return: ['value' => 123]

// Test 2: Session snapshot
HT_Vault_Manager::save_session_snapshot(
    home_url('/test'),
    ['field1' => 'value1'],
    'Test summary'
);
$session = HT_Vault_Manager::get_session_snapshot();
// Should return session with form_snapshot

// Test 3: Interest tracking
HT_Vault_Manager::track_interest('test-category', 5);
$interests = HT_Vault_Manager::get_user_interests();
// Should include 'test-category' with score 5

// Test 4: Persona analysis
$persona = HT_Persona_Engine::analyze_user_persona();
// Should return persona data with confidence score
```

### Console Testing

```javascript
// Check if vault is loaded
console.log(window.Homa.vault);

// Simulate form change
window.Homa.emit('site:input_change', {
    field: 'tirage',
    value: 500
});

// Check cache
console.log(window.HomaStore.get('tirage'));  // Should be 500

// Test API
await window.HomaAPI.get('/vault/restore');
```

## Performance Considerations

1. **Debounced Sync**: Form changes are synced with 1-second debounce to prevent excessive API calls

2. **Local Cache**: Frequently accessed data is cached locally for instant retrieval

3. **Indexed Queries**: All database tables have proper indexes on frequently queried columns

4. **JSON Storage**: Uses JSON for flexible data structures without schema changes

5. **Compressed Summaries**: Long conversations are compressed to ~500 tokens max

## Cross-Device Synchronization

```javascript
// Device 1 (Mobile) - User fills form
window.HomaStore.update({ field: 'tirage', value: 500 });
// Automatically synced to server with session_token

// Device 2 (Desktop) - User logs in
await window.HomaStore.restore();
// Automatically restores session with same session_token
// Shows prompt: "مشخصات قبلی رو لود کنم؟"
```

## Troubleshooting

### Issue: Data not syncing

**Solution**: Check browser console for errors. Verify REST API endpoints are accessible:

```javascript
console.log(homaConfig.restUrl);  // Should show API URL
```

### Issue: Session not restoring

**Solution**: Check if session has expired (48 hours). Clear cookies and start fresh:

```javascript
await window.HomaStore.clear();
```

### Issue: Persona not detected

**Solution**: Ensure enough interaction data. Persona requires minimum confidence score:

```php
if ($persona_data['confidence'] < 10) {
    return ''; // Not enough data
}
```

## Future Enhancements

1. **Vector Embeddings**: Store semantic vectors for better context matching
2. **Machine Learning**: Train models on behavioral patterns
3. **Advanced Compression**: Use AI-powered summarization
4. **Real-time Sync**: WebSocket-based instant synchronization
5. **Predictive Loading**: Preload likely next steps based on persona

## Summary

PR7's Omni-Store provides a robust, multi-layered memory system that:

- ✅ Stores user context across sessions and devices
- ✅ Compresses conversations to save tokens
- ✅ Analyzes user behavior to determine persona
- ✅ Enriches AI prompts with relevant context
- ✅ Maintains data security and privacy
- ✅ Cleans up expired data automatically
- ✅ Provides comprehensive REST API
- ✅ Integrates seamlessly with existing Event Bus

This infrastructure enables Homa to provide truly personalized, context-aware assistance to users.
