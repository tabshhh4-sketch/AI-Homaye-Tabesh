<?php
/**
 * PR17 Usage Examples - Authority Manager, Action Orchestrator, Feedback System
 *
 * @package HomayeTabesh
 */

require_once __DIR__ . '/../homaye-tabesh.php';

/**
 * Example 1: Using Authority Manager for Conflict Resolution
 */
function example_authority_manager() {
    echo "<h2>Example 1: Authority Manager - Conflict Resolution</h2>";
    
    $authority_manager = \HomayeTabesh\HT_Core::instance()->authority_manager;
    
    // Set manual override (highest priority)
    $success = $authority_manager->set_manual_override(
        'product_price_101',
        120.00,
        'تصحیح قیمت توسط مدیر - تخفیف ویژه',
        get_current_user_id()
    );
    
    echo "<p>Manual override set: " . ($success ? 'Yes' : 'No') . "</p>";
    
    // Get final fact (will use manual override)
    $final_price = $authority_manager->get_final_fact('product_price_101');
    echo "<p>Final Price (from Level 1 - Manual Override): {$final_price}</p>";
    
    // Get fact from live data (if no override exists)
    $live_price = $authority_manager->get_final_fact('product_price_102');
    echo "<p>Live Price (from Level 3 - WooCommerce): {$live_price}</p>";
    
    // Get all overrides
    $overrides = $authority_manager->get_all_overrides(true);
    echo "<h3>Active Manual Overrides:</h3>";
    echo "<pre>" . print_r($overrides, true) . "</pre>";
}

/**
 * Example 2: Using Action Orchestrator for Multi-Step Operations
 */
function example_action_orchestrator() {
    echo "<h2>Example 2: Action Orchestrator - Multi-Step Operations</h2>";
    
    $orchestrator = \HomayeTabesh\HT_Core::instance()->action_orchestrator;
    
    // Define multi-step action sequence
    $actions = [
        [
            'type' => 'verify_otp',
            'params' => [
                'phone' => '09123456789',
                'code' => '1234',
            ],
        ],
        [
            'type' => 'add_to_cart',
            'params' => [
                'product_id' => 101,
                'quantity' => 1,
            ],
        ],
        [
            'type' => 'create_order',
            'params' => [
                'product_id' => 101,
                'quantity' => 1,
            ],
        ],
        [
            'type' => 'send_sms',
            'params' => [
                'template' => 'order_confirmed',
                'template_params' => [
                    'order_id' => '{order_id}',
                ],
            ],
        ],
    ];
    
    // Execute actions
    $result = $orchestrator->execute_actions($actions);
    
    echo "<h3>Execution Result:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ All actions executed successfully!</p>";
        echo "<p>Final Message: {$result['message']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Action execution failed!</p>";
        echo "<p>Error: {$result['error']}</p>";
        echo "<p>Failed at step: {$result['failed_at']}</p>";
        echo "<p>Rollback performed: " . ($result['rollback_performed'] ? 'Yes' : 'No') . "</p>";
    }
}

/**
 * Example 3: Parsing Gemini Response with Actions
 */
function example_gemini_with_orchestrator() {
    echo "<h2>Example 3: Gemini Response with Orchestrator</h2>";
    
    // Simulated Gemini response with actions
    $gemini_response = [
        'response' => 'شماره شما تایید شد و سفارش ثبت گردید. پیامک تایید برای شما ارسال شد.',
        'actions' => [
            [
                'type' => 'verify_otp',
                'params' => ['phone' => '09123456789', 'code' => '1234'],
            ],
            [
                'type' => 'create_order',
                'params' => ['product_id' => 101, 'quantity' => 1],
            ],
            [
                'type' => 'send_sms',
                'params' => [
                    'template' => 'order_confirmed',
                    'template_params' => ['order_id' => '{order_id}'],
                ],
            ],
        ],
    ];
    
    $orchestrator = \HomayeTabesh\HT_Core::instance()->action_orchestrator;
    
    // Parse and execute actions
    $actions = \HomayeTabesh\HT_Action_Orchestrator::parse_gemini_response($gemini_response);
    $result = $orchestrator->execute_actions($actions);
    
    echo "<h3>Gemini Response:</h3>";
    echo "<p>{$gemini_response['response']}</p>";
    
    echo "<h3>Actions Execution:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
}

/**
 * Example 4: User Feedback System
 */
function example_feedback_system() {
    echo "<h2>Example 4: Feedback System</h2>";
    
    $feedback_system = \HomayeTabesh\HT_Core::instance()->feedback_system;
    
    // Submit positive feedback
    $positive_feedback = [
        'rating' => 'like',
        'response_text' => 'قیمت محصول 120 تومان است',
        'user_prompt' => 'قیمت این محصول چقدر است؟',
        'conversation_id' => 'conv_' . time(),
        'facts_used' => ['product_price_101' => 120],
        'context_data' => ['product_id' => 101],
    ];
    
    $result1 = $feedback_system->submit_feedback($positive_feedback);
    echo "<h3>Positive Feedback:</h3>";
    echo "<p>Success: " . ($result1['success'] ? 'Yes' : 'No') . "</p>";
    echo "<p>Message: {$result1['message']}</p>";
    
    // Submit negative feedback
    $negative_feedback = [
        'rating' => 'dislike',
        'response_text' => 'قیمت محصول 100 تومان است',
        'user_prompt' => 'قیمت این محصول چقدر است؟',
        'error_details' => 'قیمت اشتباه است. باید 120 تومان باشد',
        'conversation_id' => 'conv_' . (time() + 1),
        'facts_used' => ['product_price_101' => 100],
        'context_data' => ['product_id' => 101],
    ];
    
    $result2 = $feedback_system->submit_feedback($negative_feedback);
    echo "<h3>Negative Feedback:</h3>";
    echo "<p>Success: " . ($result2['success'] ? 'Yes' : 'No') . "</p>";
    echo "<p>Message: {$result2['message']}</p>";
    
    // Get review queue (admin only)
    if (current_user_can('manage_options')) {
        $queue = $feedback_system->get_review_queue(['status' => 'pending'], 1, 10);
        echo "<h3>Review Queue:</h3>";
        echo "<pre>" . print_r($queue, true) . "</pre>";
        
        // Get statistics
        $stats = $feedback_system->get_statistics();
        echo "<h3>Feedback Statistics:</h3>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
    }
}

/**
 * Example 5: Authority Levels Demonstration
 */
function example_authority_levels() {
    echo "<h2>Example 5: Authority Levels Priority</h2>";
    
    $authority_manager = \HomayeTabesh\HT_Core::instance()->authority_manager;
    
    // Scenario: Product price conflict
    echo "<h3>Scenario: Product Price Conflict</h3>";
    echo "<ul>";
    echo "<li>Level 1 (Manual Override): 120 تومان</li>";
    echo "<li>Level 2 (Panel Settings): -</li>";
    echo "<li>Level 3 (WooCommerce Live): 100 تومان</li>";
    echo "<li>Level 4 (General Knowledge): 110 تومان</li>";
    echo "</ul>";
    
    // Set manual override
    $authority_manager->set_manual_override(
        'product_price_example',
        120.00,
        'قیمت تصحیح شده توسط مدیر'
    );
    
    // Get final price (should be 120 from Level 1)
    $final_price = $authority_manager->get_final_fact('product_price_example');
    
    echo "<p><strong>Final Price (Winner):</strong> {$final_price} تومان</p>";
    echo "<p><strong>Authority Level:</strong> Level 1 - Manual Override (Highest Priority)</p>";
    
    // Remove override and check again
    $authority_manager->remove_manual_override('product_price_example');
    echo "<h3>After Removing Manual Override:</h3>";
    
    $final_price_after = $authority_manager->get_final_fact('product_price_example');
    echo "<p><strong>Final Price:</strong> " . ($final_price_after ?? 'Not Found') . "</p>";
    echo "<p><strong>Authority Level:</strong> Level 3 - Live Data (WooCommerce)</p>";
}

/**
 * Example 6: Complete Flow - User Request with Orchestration
 */
function example_complete_flow() {
    echo "<h2>Example 6: Complete User Request Flow</h2>";
    
    echo "<h3>User Request:</h3>";
    echo "<p>کاربر: سفارش من را با موبایلم ثبت کن</p>";
    
    // Step 1: Authority Manager checks facts
    echo "<h3>Step 1: Check Facts with Authority Manager</h3>";
    $authority_manager = \HomayeTabesh\HT_Core::instance()->authority_manager;
    $product_price = $authority_manager->get_final_fact('product_price_101', ['user_request' => true]);
    echo "<p>Product Price (from highest authority): {$product_price}</p>";
    
    // Step 2: Orchestrator executes actions
    echo "<h3>Step 2: Execute Multi-Step Actions</h3>";
    $orchestrator = \HomayeTabesh\HT_Core::instance()->action_orchestrator;
    
    $actions = [
        ['type' => 'verify_otp', 'params' => ['phone' => '09123456789', 'code' => '1234']],
        ['type' => 'create_order', 'params' => ['product_id' => 101, 'quantity' => 1]],
        ['type' => 'send_sms', 'params' => ['template' => 'order_confirmed']],
    ];
    
    $result = $orchestrator->execute_actions($actions);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ {$result['message']}</p>";
    } else {
        echo "<p style='color: red;'>❌ {$result['error']}</p>";
    }
    
    // Step 3: User provides feedback
    echo "<h3>Step 3: User Provides Feedback</h3>";
    $feedback_system = \HomayeTabesh\HT_Core::instance()->feedback_system;
    
    $feedback = [
        'rating' => 'like',
        'response_text' => $result['message'],
        'user_prompt' => 'سفارش من را با موبایلم ثبت کن',
        'conversation_id' => 'conv_complete_flow',
        'facts_used' => ['product_price_101' => $product_price],
        'context_data' => $result,
    ];
    
    $feedback_result = $feedback_system->submit_feedback($feedback);
    echo "<p>Feedback submitted: " . ($feedback_result['success'] ? 'Yes' : 'No') . "</p>";
}

// Run examples
if (is_admin() || defined('WP_CLI')) {
    echo "<h1>PR17 Usage Examples</h1>";
    echo "<hr>";
    
    example_authority_manager();
    echo "<hr>";
    
    example_action_orchestrator();
    echo "<hr>";
    
    example_gemini_with_orchestrator();
    echo "<hr>";
    
    example_feedback_system();
    echo "<hr>";
    
    example_authority_levels();
    echo "<hr>";
    
    example_complete_flow();
}
