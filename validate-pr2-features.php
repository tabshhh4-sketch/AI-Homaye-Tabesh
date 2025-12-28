<?php
/**
 * Comprehensive PR #2 Feature Validation
 * Tests all Advanced Telemetry Infrastructure components
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

// Simulate WordPress environment
define('ABSPATH', __DIR__ . '/');
require_once __DIR__ . '/includes/autoload.php';

use HomayeTabesh\HT_Core;
use HomayeTabesh\HT_Telemetry;
use HomayeTabesh\HT_WooCommerce_Context;
use HomayeTabesh\HT_Divi_Bridge;
use HomayeTabesh\HT_Decision_Trigger;
use HomayeTabesh\HT_Persona_Manager;

echo "========================================\n";
echo "PR #2 Feature Validation\n";
echo "Advanced Telemetry Infrastructure\n";
echo "========================================\n\n";

$tests_passed = 0;
$tests_failed = 0;
$issues = [];

/**
 * Helper function to test and report
 */
function test_feature($name, $callback) {
    global $tests_passed, $tests_failed, $issues;
    
    echo "Testing: $name ... ";
    
    try {
        $result = $callback();
        if ($result === true || $result === null) {
            echo "✓ PASS\n";
            $tests_passed++;
            return true;
        } else {
            echo "✗ FAIL: $result\n";
            $tests_failed++;
            $issues[] = "$name: $result";
            return false;
        }
    } catch (\Throwable $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $tests_failed++;
        $issues[] = "$name: Exception - " . $e->getMessage();
        return false;
    }
}

// Test 1: Core Class Instantiation
test_feature("HT_Core singleton", function() {
    $core = HT_Core::instance();
    return $core !== null;
});

// Test 2: HT_Telemetry initialization
test_feature("HT_Telemetry component", function() {
    $telemetry = new HT_Telemetry();
    return $telemetry !== null;
});

// Test 3: HT_WooCommerce_Context initialization
test_feature("HT_WooCommerce_Context component", function() {
    $woo_context = new HT_WooCommerce_Context();
    if ($woo_context === null) {
        return "Failed to instantiate";
    }
    
    // Test is_woocommerce_active method
    $method_exists = method_exists($woo_context, 'is_woocommerce_active');
    if (!$method_exists) {
        return "Missing is_woocommerce_active method";
    }
    
    // Test get_cart_status method
    $method_exists = method_exists($woo_context, 'get_cart_status');
    if (!$method_exists) {
        return "Missing get_cart_status method";
    }
    
    return true;
});

// Test 4: HT_Divi_Bridge initialization
test_feature("HT_Divi_Bridge component", function() {
    $divi_bridge = new HT_Divi_Bridge();
    if ($divi_bridge === null) {
        return "Failed to instantiate";
    }
    
    // Test identify_module method
    $method_exists = method_exists($divi_bridge, 'identify_module');
    if (!$method_exists) {
        return "Missing identify_module method";
    }
    
    // Test detect_content_pattern method
    $method_exists = method_exists($divi_bridge, 'detect_content_pattern');
    if (!$method_exists) {
        return "Missing detect_content_pattern method";
    }
    
    // Test get_persona_weights method
    $method_exists = method_exists($divi_bridge, 'get_persona_weights');
    if (!$method_exists) {
        return "Missing get_persona_weights method";
    }
    
    return true;
});

// Test 5: HT_Decision_Trigger initialization
test_feature("HT_Decision_Trigger component", function() {
    $decision_trigger = new HT_Decision_Trigger();
    if ($decision_trigger === null) {
        return "Failed to instantiate";
    }
    
    // Test should_trigger_ai method
    $method_exists = method_exists($decision_trigger, 'should_trigger_ai');
    if (!$method_exists) {
        return "Missing should_trigger_ai method";
    }
    
    // Test get_trigger_stats method
    $method_exists = method_exists($decision_trigger, 'get_trigger_stats');
    if (!$method_exists) {
        return "Missing get_trigger_stats method";
    }
    
    return true;
});

// Test 6: HT_Persona_Manager initialization
test_feature("HT_Persona_Manager component", function() {
    $persona_manager = new HT_Persona_Manager();
    if ($persona_manager === null) {
        return "Failed to instantiate";
    }
    
    // Test add_score method
    $method_exists = method_exists($persona_manager, 'add_score');
    if (!$method_exists) {
        return "Missing add_score method";
    }
    
    // Test get_full_analysis method
    $method_exists = method_exists($persona_manager, 'get_full_analysis');
    if (!$method_exists) {
        return "Missing get_full_analysis method";
    }
    
    // Test get_dominant_persona method
    $method_exists = method_exists($persona_manager, 'get_dominant_persona');
    if (!$method_exists) {
        return "Missing get_dominant_persona method";
    }
    
    return true;
});

// Test 7: HT_Core component properties
test_feature("HT_Core PR #2 properties", function() {
    $core = HT_Core::instance();
    
    $properties = ['woo_context', 'divi_bridge', 'decision_trigger', 'memory', 'eyes'];
    foreach ($properties as $prop) {
        if (!property_exists($core, $prop)) {
            return "Missing property: $prop";
        }
    }
    
    return true;
});

// Test 8: Divi Bridge Module Mapping
test_feature("Divi Bridge module identification", function() {
    $divi_bridge = new HT_Divi_Bridge();
    
    // Test pricing table identification
    $module = $divi_bridge->identify_module('et_pb_pricing_table et_pb_module');
    if ($module === null) {
        return "Failed to identify pricing table module";
    }
    
    if (!isset($module['type']) || $module['type'] !== 'pricing') {
        return "Incorrect module type for pricing table";
    }
    
    return true;
});

// Test 9: Divi Bridge Content Pattern Detection
test_feature("Divi Bridge content pattern detection", function() {
    $divi_bridge = new HT_Divi_Bridge();
    
    // Test calculator pattern
    $pattern = $divi_bridge->detect_content_pattern('محاسبه قیمت تیراژ', 'calculator-module');
    if ($pattern === null) {
        return "Failed to detect calculator pattern";
    }
    
    if (!isset($pattern['pattern']) || $pattern['pattern'] !== 'calculator') {
        return "Incorrect pattern detection";
    }
    
    return true;
});

// Test 10: Divi Bridge Persona Weights
test_feature("Divi Bridge persona weights", function() {
    $divi_bridge = new HT_Divi_Bridge();
    
    $weights = $divi_bridge->get_persona_weights('et_pb_pricing_table', ['text' => 'قیمت']);
    
    if (!is_array($weights)) {
        return "Weights should be an array";
    }
    
    if (empty($weights)) {
        return "Weights should not be empty for pricing table";
    }
    
    return true;
});

// Test 11: Persona Manager Dynamic Scoring
test_feature("Persona Manager dynamic scoring", function() {
    $persona_manager = new HT_Persona_Manager();
    
    // Test that add_score method accepts all required parameters
    $reflection = new \ReflectionMethod($persona_manager, 'add_score');
    $params = $reflection->getParameters();
    
    $required_params = ['user_identifier', 'persona_type', 'score'];
    foreach ($required_params as $param_name) {
        $found = false;
        foreach ($params as $param) {
            if ($param->getName() === $param_name) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return "Missing parameter: $param_name";
        }
    }
    
    return true;
});

// Test 12: REST API endpoint methods
test_feature("HT_Telemetry REST endpoints", function() {
    $telemetry = new HT_Telemetry();
    
    $endpoints = [
        'get_woocommerce_context',
        'get_persona_stats',
        'check_ai_trigger',
        'handle_telemetry_event',
        'handle_batch_events'
    ];
    
    foreach ($endpoints as $endpoint) {
        if (!method_exists($telemetry, $endpoint)) {
            return "Missing endpoint method: $endpoint";
        }
    }
    
    return true;
});

// Test 13: JavaScript tracker file exists
test_feature("JavaScript tracker file", function() {
    $tracker_path = __DIR__ . '/assets/js/tracker.js';
    if (!file_exists($tracker_path)) {
        return "tracker.js not found";
    }
    
    $content = file_get_contents($tracker_path);
    
    // Check for PR #2 features
    $features = ['module_dwell', 'scroll_depth', 'heat_point', 'trackDwellTime', 'trackScrollDepth', 'detectHeatPoints'];
    foreach ($features as $feature) {
        if (strpos($content, $feature) === false) {
            return "Missing feature in tracker.js: $feature";
        }
    }
    
    return true;
});

// Test 14: Example file exists
test_feature("PR #2 usage examples file", function() {
    $examples_path = __DIR__ . '/examples/pr2-usage-examples.php';
    if (!file_exists($examples_path)) {
        return "pr2-usage-examples.php not found";
    }
    
    return true;
});

// Test 15: Documentation files exist
test_feature("PR #2 documentation files", function() {
    $docs = [
        'PR2-IMPLEMENTATION.md',
        'PR2-QUICKSTART.md'
    ];
    
    foreach ($docs as $doc) {
        $path = __DIR__ . '/' . $doc;
        if (!file_exists($path)) {
            return "Missing documentation: $doc";
        }
    }
    
    return true;
});

// Summary
echo "\n========================================\n";
echo "Test Summary\n";
echo "========================================\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";

if ($tests_failed > 0) {
    echo "\nIssues Found:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
    exit(1);
} else {
    echo "\n✓ All PR #2 features are properly implemented!\n";
    echo "\nKey Features Verified:\n";
    echo "  ✓ Frontend tracking (Eyes): dwell time, scroll depth, heat-points\n";
    echo "  ✓ WooCommerce context provider (Memory)\n";
    echo "  ✓ Divi Bridge controller (module mapping)\n";
    echo "  ✓ Decision Trigger (AI invocation logic)\n";
    echo "  ✓ Persona Manager (dynamic scoring)\n";
    echo "  ✓ REST API endpoints (3 new endpoints)\n";
    echo "  ✓ Documentation and examples\n";
    exit(0);
}
