<?php
/**
 * Usage Examples for PR #2 Features
 * Advanced Telemetry, Persona Scoring & WooCommerce Integration
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example 1: Get WooCommerce Context
 * Retrieve current cart and product information
 */
function ht_example_get_woocommerce_context() {
    $core = \HomayeTabesh\HT_Core::instance();
    $woo_context = $core->woo_context;
    
    // Get full context
    $context = $woo_context->get_full_context();
    
    echo "Cart Status: " . $context['cart']['status'] . "\n";
    if ($context['cart']['status'] === 'has_items') {
        echo "Items in cart: " . $context['cart']['item_count'] . "\n";
        echo "Total: " . $context['cart']['total_formatted'] . "\n";
    }
    
    // Get formatted context for AI
    $ai_context = $woo_context->format_for_ai($context);
    echo $ai_context;
}

/**
 * Example 2: Check Persona Score and Stats
 * Get user's persona analysis
 */
function ht_example_check_persona() {
    $core = \HomayeTabesh\HT_Core::instance();
    $persona_manager = $core->memory;
    
    $user_id = 'user_123'; // or use guest_xxx
    
    // Get full analysis
    $analysis = $persona_manager->get_full_analysis($user_id);
    
    echo "Dominant Persona: " . $analysis['dominant']['type'] . "\n";
    echo "Score: " . $analysis['dominant']['score'] . "\n";
    echo "Confidence: " . $analysis['dominant']['confidence'] . "%\n";
    
    echo "\nAll Scores:\n";
    foreach ($analysis['scores'] as $persona => $score) {
        echo "- $persona: $score\n";
    }
    
    echo "\nBehavior Summary:\n";
    echo $analysis['behavior'];
}

/**
 * Example 3: Identify Divi Module
 * Detect module type and get business logic mapping
 */
function ht_example_identify_divi_module() {
    $core = \HomayeTabesh\HT_Core::instance();
    $divi_bridge = $core->divi_bridge;
    
    $element_class = 'et_pb_pricing_table et_pb_module';
    $element_data = [
        'text' => 'محاسبه قیمت چاپ کتاب - تیراژ بالا',
    ];
    
    // Identify module
    $module = $divi_bridge->identify_module($element_class);
    if ($module) {
        echo "Module Type: " . $module['type'] . "\n";
        echo "Category: " . $module['category'] . "\n";
        echo "Intent: " . $module['intent'] . "\n";
    }
    
    // Detect content pattern
    $pattern = $divi_bridge->detect_content_pattern(
        $element_data['text'],
        $element_class
    );
    
    if ($pattern) {
        echo "\nDetected Pattern: " . $pattern['pattern'] . "\n";
        echo "Persona Weights:\n";
        foreach ($pattern['persona_weight'] as $persona => $weight) {
            echo "- $persona: +$weight points\n";
        }
    }
    
    // Get persona weights
    $weights = $divi_bridge->get_persona_weights($element_class, $element_data);
    echo "\nTotal Persona Weights:\n";
    foreach ($weights as $persona => $weight) {
        echo "- $persona: $weight\n";
    }
}

/**
 * Example 4: Check AI Trigger Status
 * Determine if conditions are met to call Gemini
 */
function ht_example_check_ai_trigger() {
    $core = \HomayeTabesh\HT_Core::instance();
    $decision_trigger = $core->decision_trigger;
    
    $user_id = 'user_123';
    
    // Check if AI should be triggered
    $trigger_check = $decision_trigger->should_trigger_ai($user_id);
    
    if ($trigger_check['trigger']) {
        echo "AI Trigger: READY ✓\n";
        echo "Reason: " . $trigger_check['reason'] . "\n";
        echo "Persona: " . $trigger_check['persona']['type'] . "\n";
        echo "Event Count: " . $trigger_check['event_count'] . "\n";
    } else {
        echo "AI Trigger: NOT READY ✗\n";
        echo "Reason: " . $trigger_check['reason'] . "\n";
        
        if (isset($trigger_check['score'])) {
            echo "Current Score: " . $trigger_check['score'] . "\n";
            echo "Required: " . $trigger_check['threshold'] . "\n";
        }
    }
    
    // Get statistics
    $stats = $decision_trigger->get_trigger_stats($user_id);
    echo "\nTrigger Statistics:\n";
    echo "Score Progress: " . round($stats['score_percentage']) . "%\n";
    echo "Events: " . $stats['event_count'] . "/" . $stats['min_events'] . "\n";
}

/**
 * Example 5: Execute AI Decision
 * Trigger Gemini when conditions are met
 */
function ht_example_execute_ai_decision() {
    $core = \HomayeTabesh\HT_Core::instance();
    $decision_trigger = $core->decision_trigger;
    
    $user_id = 'user_123';
    $user_prompt = 'چه نوع چاپی برای کتابم مناسب است؟';
    
    // Execute AI decision
    $result = $decision_trigger->execute_ai_decision($user_id, $user_prompt);
    
    if ($result['success']) {
        echo "AI Response:\n";
        echo $result['response'] . "\n";
        
        echo "\nContext Used:\n";
        echo "Persona: " . $result['context']['persona']['dominant']['type'] . "\n";
        echo "Activity: " . $result['context']['recent_activity']['total_events'] . " events\n";
    } else {
        echo "AI not triggered: " . $result['reason'] . "\n";
    }
}

/**
 * Example 6: Manual Persona Score Update
 * Add score to specific persona based on custom logic
 */
function ht_example_update_persona_score() {
    $core = \HomayeTabesh\HT_Core::instance();
    $persona_manager = $core->memory;
    
    $user_id = 'user_123';
    
    // Simulate event: User viewed calculator for 10+ seconds
    $event_type = 'module_dwell';
    $element_class = 'et_pb_module calculator-module';
    $element_data = [
        'text' => 'محاسبه‌گر قیمت چاپ با تیراژ',
        'dwell_time' => 12000, // 12 seconds
    ];
    
    // Add score with dynamic calculation
    $persona_manager->add_score(
        $user_id,
        'author', // Primary persona
        5, // Base score
        $event_type,
        $element_class,
        $element_data
    );
    
    echo "Score updated successfully!\n";
    
    // Check new scores
    $scores = $persona_manager->get_scores($user_id);
    foreach ($scores as $persona => $score) {
        echo "$persona: $score\n";
    }
}

/**
 * Example 7: REST API Calls from JavaScript
 * Example AJAX calls to new endpoints
 */
function ht_example_rest_api_javascript() {
    ?>
    <script>
    // Get WooCommerce Context
    async function getWooContext() {
        const response = await fetch('/wp-json/homaye/v1/context/woocommerce', {
            headers: {
                'X-WP-Nonce': homayeConfig.nonce
            }
        });
        const data = await response.json();
        console.log('WooCommerce Context:', data);
    }
    
    // Get Persona Stats
    async function getPersonaStats() {
        const response = await fetch('/wp-json/homaye/v1/persona/stats', {
            headers: {
                'X-WP-Nonce': homayeConfig.nonce
            }
        });
        const data = await response.json();
        console.log('Persona Analysis:', data.analysis);
    }
    
    // Check AI Trigger
    async function checkAITrigger() {
        const response = await fetch('/wp-json/homaye/v1/trigger/check', {
            headers: {
                'X-WP-Nonce': homayeConfig.nonce
            }
        });
        const data = await response.json();
        console.log('AI Trigger Status:', data.trigger);
        console.log('Stats:', data.stats);
        
        if (data.trigger.trigger) {
            console.log('Ready to call AI!');
        }
    }
    
    // Send custom tracking event
    async function sendCustomEvent(eventType, element) {
        const response = await fetch('/wp-json/homaye/v1/telemetry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': homayeConfig.nonce
            },
            body: JSON.stringify({
                event_type: eventType,
                element_class: element.className,
                element_data: {
                    text: element.textContent,
                    tag: element.tagName
                }
            })
        });
        const data = await response.json();
        console.log('Event recorded:', data);
    }
    </script>
    <?php
}

/**
 * Example 8: Integration with Theme/Plugin
 * Hook into WordPress to enhance behavior
 */
function ht_example_integration_hooks() {
    // Add custom tracking to specific pages
    add_action('wp_footer', function() {
        if (is_product()) {
            ?>
            <script>
            // Track product page view with enhanced data
            document.addEventListener('DOMContentLoaded', function() {
                // Check AI trigger status every 30 seconds
                setInterval(async function() {
                    const response = await fetch('/wp-json/homaye/v1/trigger/check');
                    const data = await response.json();
                    
                    if (data.trigger.trigger) {
                        console.log('AI is ready to help!');
                        // Show chatbot or AI assistant
                    }
                }, 30000);
            });
            </script>
            <?php
        }
    });
    
    // Add persona-based content
    add_filter('the_content', function($content) {
        $core = \HomayeTabesh\HT_Core::instance();
        $persona_manager = $core->memory;
        
        // Get current user's persona
        if (isset($_COOKIE['ht_user_id'])) {
            $user_id = sanitize_text_field($_COOKIE['ht_user_id']);
            $persona = $persona_manager->get_dominant_persona($user_id);
            
            // Add personalized message based on persona
            if ($persona['confidence'] > 50) {
                $message = match($persona['type']) {
                    'author' => '<div class="persona-message">💡 به عنوان یک نویسنده، محصولات ویژه برای شما داریم!</div>',
                    'business' => '<div class="persona-message">🏢 برای سفارشات عمده با ما تماس بگیرید.</div>',
                    'designer' => '<div class="persona-message">🎨 نمونه‌های طراحی حرفه‌ای ما را ببینید.</div>',
                    'student' => '<div class="persona-message">🎓 تخفیف ویژه دانشجویان!</div>',
                    default => ''
                };
                $content = $message . $content;
            }
        }
        
        return $content;
    });
}

/**
 * Example 9: Cache Management
 * Work with transient cache
 */
function ht_example_cache_management() {
    $user_id = 'user_123';
    
    // Scores are automatically cached in transients
    // Cache key format: ht_persona_{md5($user_id)}
    $cache_key = 'ht_persona_' . md5($user_id);
    
    // Get cached scores
    $cached_scores = get_transient($cache_key);
    if ($cached_scores !== false) {
        echo "Cached Scores Found:\n";
        print_r($cached_scores);
    }
    
    // Clear cache if needed
    delete_transient($cache_key);
    echo "Cache cleared for user: $user_id\n";
}

/**
 * Example 10: Complete Workflow
 * Full example from tracking to AI decision
 */
function ht_example_complete_workflow() {
    $core = \HomayeTabesh\HT_Core::instance();
    
    $user_id = 'guest_' . wp_generate_uuid4();
    
    echo "=== Complete Workflow Example ===\n\n";
    
    // Step 1: User interacts with pricing table
    echo "Step 1: User views pricing table\n";
    $core->memory->add_score(
        $user_id,
        'business',
        5,
        'long_view',
        'et_pb_pricing_table',
        ['text' => 'قیمت چاپ انبوه', 'dwell_time' => 8000]
    );
    
    // Step 2: User views calculator
    echo "Step 2: User uses calculator\n";
    $core->memory->add_score(
        $user_id,
        'author',
        10,
        'click',
        'calculator-module',
        ['text' => 'محاسبه قیمت نهایی - تیراژ 1000']
    );
    
    // Step 3: Check persona
    echo "\nStep 3: Check persona\n";
    $persona = $core->memory->get_dominant_persona($user_id);
    echo "Detected: " . $persona['type'] . " (confidence: " . $persona['confidence'] . "%)\n";
    
    // Step 4: Check WooCommerce context
    echo "\nStep 4: Check WooCommerce context\n";
    $woo_context = $core->woo_context->get_full_context();
    echo "Cart: " . $woo_context['cart']['status'] . "\n";
    echo "Page: " . $woo_context['page_type'] . "\n";
    
    // Step 5: Check if AI should be triggered
    echo "\nStep 5: Check AI trigger\n";
    $trigger = $core->decision_trigger->should_trigger_ai($user_id);
    echo "Trigger: " . ($trigger['trigger'] ? 'YES' : 'NO') . "\n";
    echo "Reason: " . $trigger['reason'] . "\n";
    
    // Step 6: Execute AI if ready
    if ($trigger['trigger']) {
        echo "\nStep 6: Execute AI decision\n";
        $result = $core->decision_trigger->execute_ai_decision(
            $user_id,
            'بهترین نوع چاپ برای کتاب من چیست؟'
        );
        
        if ($result['success']) {
            echo "AI Response: " . $result['response'] . "\n";
        }
    } else {
        echo "\nStep 6: Not enough data for AI yet\n";
        $stats = $core->decision_trigger->get_trigger_stats($user_id);
        echo "Progress: " . round($stats['score_percentage']) . "%\n";
    }
}
