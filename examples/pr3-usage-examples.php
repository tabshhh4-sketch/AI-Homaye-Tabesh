<?php
/**
 * Example Usage - Homaye Tabesh PR3: Inference Engine
 * 
 * This file demonstrates how to use the new inference engine and AI features from PR3
 * 
 * @package HomayeTabesh
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example 1: Simple AI Query
 * 
 * Send a simple question to Homa and get a response
 */
function pr3_example_simple_query() {
    $inference_engine = \HomayeTabesh\HT_Core::instance()->inference_engine;
    
    $result = $inference_engine->generate_decision([
        'user_identifier' => 'user_123',
        'message' => 'می‌خواهم یک کتاب چاپ کنم. از کجا شروع کنم؟',
        'current_page' => '/products/book-printing',
    ]);
    
    if ($result['success']) {
        echo "Homa: " . $result['message'] . "\n";
        
        if (isset($result['action'])) {
            echo "Action: " . $result['action']['type'] . "\n";
        }
    }
}

/**
 * Example 2: Get Context-Aware Suggestion
 * 
 * Get a suggestion based on user's persona and behavior
 */
function pr3_example_context_suggestion() {
    $inference_engine = \HomayeTabesh\HT_Core::instance()->inference_engine;
    
    $result = $inference_engine->get_context_suggestion('user_123', []);
    
    if ($result['success']) {
        echo "Suggestion: " . $result['message'] . "\n";
        echo "Based on persona: " . $result['persona'] . "\n";
    }
}

/**
 * Example 3: Analyze User Intent
 * 
 * Analyze what the user is trying to do
 */
function pr3_example_analyze_intent() {
    $inference_engine = \HomayeTabesh\HT_Core::instance()->inference_engine;
    
    $result = $inference_engine->analyze_user_intent('user_123');
    
    echo "Detected Intent: " . $result['intent'] . "\n";
    echo "Confidence: " . $result['confidence'] . "%\n";
    echo "Persona: " . $result['persona'] . "\n";
}

/**
 * Example 4: Build Custom Prompt
 * 
 * Build a custom prompt with business knowledge
 */
function pr3_example_custom_prompt() {
    $prompt_builder = new \HomayeTabesh\HT_Prompt_Builder_Service();
    
    $system_instruction = $prompt_builder->build_system_instruction(
        'user_123',
        ['knowledge_types' => ['pricing', 'products']]
    );
    
    $user_prompt = $prompt_builder->build_user_prompt(
        'چاپ 100 نسخه کاتالوگ 24 صفحه چقدر هزینه دارد؟',
        'user_123',
        ['current_page' => '/pricing-calculator']
    );
    
    echo "System Instruction Length: " . strlen($system_instruction) . " chars\n";
    echo "User Prompt: " . $user_prompt . "\n";
}

/**
 * Example 5: WordPress Shortcode for AI Chat
 * 
 * Create a shortcode that users can use to chat with Homa
 * Usage: [homa_chat placeholder="سوال خود را بپرسید..."]
 */
function pr3_homa_chat_shortcode($atts) {
    $atts = shortcode_atts([
        'placeholder' => 'سوال خود را بپرسید...',
    ], $atts);
    
    ob_start();
    ?>
    <div class="homa-chat-widget">
        <div id="homa-chat-messages"></div>
        <form id="homa-chat-form">
            <input 
                type="text" 
                id="homa-chat-input" 
                placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                required
            />
            <button type="submit">ارسال</button>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#homa-chat-form').on('submit', function(e) {
            e.preventDefault();
            
            var message = $('#homa-chat-input').val();
            var userId = 'guest_' + Date.now();
            
            // Add user message to chat
            $('#homa-chat-messages').append(
                '<div class="user-message">' + message + '</div>'
            );
            
            // Clear input
            $('#homa-chat-input').val('');
            
            // Send to API
            $.ajax({
                url: '/wp-json/homaye/v1/ai/query',
                method: 'POST',
                data: {
                    user_id: userId,
                    message: message,
                    context: {
                        page: window.location.pathname
                    }
                },
                success: function(response) {
                    // Add Homa's response
                    $('#homa-chat-messages').append(
                        '<div class="homa-message">' + response.message + '</div>'
                    );
                    
                    // Execute action if present
                    if (response.action && window.HomaUIExecutor) {
                        window.HomaUIExecutor.executeAction(response.action);
                    }
                    
                    // Scroll to bottom
                    $('#homa-chat-messages').scrollTop(
                        $('#homa-chat-messages')[0].scrollHeight
                    );
                }
            });
        });
    });
    </script>
    
    <style>
    .homa-chat-widget {
        max-width: 600px;
        margin: 20px auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }
    
    #homa-chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 20px;
        background: #f9f9f9;
    }
    
    .user-message, .homa-message {
        padding: 10px 15px;
        margin: 10px 0;
        border-radius: 8px;
        max-width: 80%;
    }
    
    .user-message {
        background: #4a90e2;
        color: white;
        margin-left: auto;
        text-align: right;
    }
    
    .homa-message {
        background: white;
        border: 1px solid #ddd;
    }
    
    #homa-chat-form {
        display: flex;
        padding: 15px;
        background: white;
    }
    
    #homa-chat-input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-right: 10px;
    }
    
    #homa-chat-form button {
        padding: 10px 20px;
        background: #4a90e2;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    #homa-chat-form button:hover {
        background: #357abd;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('homa_chat', 'pr3_homa_chat_shortcode');

/**
 * Example 6: REST API Usage from JavaScript
 * 
 * JavaScript code to interact with Homa from frontend
 */
function pr3_example_javascript_integration() {
    ?>
    <script>
    /**
     * Send a query to Homa
     */
    async function askHoma(message, userId = null) {
        if (!userId) {
            userId = 'guest_' + Date.now();
        }
        
        try {
            const response = await fetch('/wp-json/homaye/v1/ai/query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    message: message,
                    context: {
                        page: window.location.pathname,
                        element: document.activeElement.className
                    }
                })
            });
            
            const data = await response.json();
            
            console.log('Homa Response:', data.message);
            
            // Execute action if present
            if (data.action && window.HomaUIExecutor) {
                window.HomaUIExecutor.executeAction(data.action);
            }
            
            return data;
        } catch (error) {
            console.error('Error asking Homa:', error);
            return null;
        }
    }
    
    /**
     * Get suggestion based on current context
     */
    async function getHomaSuggestion(userId) {
        try {
            const response = await fetch('/wp-json/homaye/v1/ai/suggestion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('Suggestion:', data.message);
                console.log('Based on persona:', data.persona);
            }
            
            return data;
        } catch (error) {
            console.error('Error getting suggestion:', error);
            return null;
        }
    }
    
    /**
     * Check Homa's health status
     */
    async function checkHomaHealth() {
        try {
            const response = await fetch('/wp-json/homaye/v1/ai/health');
            const data = await response.json();
            
            console.log('Homa Status:', data.status);
            console.log('API Configured:', data.api_configured);
            console.log('Components:', data.components);
            
            return data;
        } catch (error) {
            console.error('Error checking health:', error);
            return null;
        }
    }
    </script>
    <?php
}
add_action('wp_footer', 'pr3_example_javascript_integration');

/**
 * Example 7: Add custom knowledge to knowledge base
 */
function pr3_example_add_custom_knowledge() {
    $knowledge = \HomayeTabesh\HT_Core::instance()->knowledge;
    
    // Add custom pricing rules
    $custom_pricing = [
        'special_offers' => [
            'new_year_discount' => [
                'description' => 'تخفیف نوروزی 20%',
                'valid_until' => '1403-01-15',
                'applicable_to' => ['کتاب', 'کاتالوگ']
            ]
        ]
    ];
    
    $knowledge->save_rules('special_pricing', $custom_pricing);
}

// Uncomment to run examples
// pr3_example_simple_query();
// pr3_example_context_suggestion();
// pr3_example_analyze_intent();
