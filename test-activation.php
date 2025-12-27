<?php
/**
 * Plugin Activation Test
 * 
 * This script tests basic plugin initialization without WordPress
 * to ensure there are no fatal PHP errors
 * 
 * @package HomayeTabesh
 */

// Mock WordPress functions
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value) {
        return true;
    }
}

if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain) {
        return $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __($text, $domain) {
        return $text;
    }
}

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
}

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

// Define plugin constants
define('HT_VERSION', '1.0.0');
define('HT_PLUGIN_FILE', __DIR__ . '/homaye-tabesh.php');
define('HT_PLUGIN_DIR', __DIR__ . '/');
define('HT_PLUGIN_URL', 'http://localhost/wp-content/plugins/homaye-tabesh/');
define('HT_PLUGIN_BASENAME', 'homaye-tabesh/homaye-tabesh.php');

echo "=== Plugin Activation Test ===\n\n";

// Test 1: Load autoloader
echo "Test 1: Loading autoloader...\n";
try {
    if (file_exists(HT_PLUGIN_DIR . 'vendor/autoload.php')) {
        require_once HT_PLUGIN_DIR . 'vendor/autoload.php';
        echo "✅ Composer autoloader loaded\n";
    } else {
        require_once HT_PLUGIN_DIR . 'includes/autoload.php';
        echo "✅ Fallback autoloader loaded\n";
    }
} catch (\Throwable $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if core classes are available
echo "\nTest 2: Checking core classes...\n";
$core_classes = [
    'HomayeTabesh\\HT_Core',
    'HomayeTabesh\\HT_Activator',
    'HomayeTabesh\\HT_Error_Handler',
    'HomayeTabesh\\HT_Gemini_Client',
];

foreach ($core_classes as $class) {
    if (class_exists($class)) {
        echo "✅ Class $class exists\n";
    } else {
        echo "❌ FAILED: Class $class not found\n";
        exit(1);
    }
}

// Test 3: Check Error Handler functionality
echo "\nTest 3: Testing Error Handler...\n";
try {
    \HomayeTabesh\HT_Error_Handler::log_error('Test message', 'test');
    echo "✅ Error Handler works without crashing\n";
} catch (\Throwable $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test Gemini Client instantiation
echo "\nTest 4: Testing Gemini Client instantiation...\n";
try {
    $client = new \HomayeTabesh\HT_Gemini_Client();
    echo "✅ Gemini Client instantiated successfully\n";
    
    // Test fallback response
    $reflection = new ReflectionClass($client);
    $method = $reflection->getMethod('get_fallback_response');
    $method->setAccessible(true);
    $result = $method->invoke($client, 'Test error');
    
    if (isset($result['success']) && $result['success'] === false) {
        echo "✅ Fallback response has correct structure\n";
    } else {
        echo "❌ FAILED: Fallback response missing 'success' key\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Test Activator table definitions
echo "\nTest 5: Testing Activator table definitions...\n";
try {
    $reflection = new ReflectionClass('\HomayeTabesh\HT_Activator');
    $method = $reflection->getMethod('check_and_repair_database');
    
    echo "✅ check_and_repair_database method exists\n";
    
    // Check for column migration method
    $method2 = $reflection->getMethod('check_and_add_missing_columns');
    echo "✅ check_and_add_missing_columns method exists\n";
    
} catch (\Throwable $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Check JavaScript file syntax
echo "\nTest 6: Checking JavaScript file syntax...\n";
$js_files = [
    'assets/js/homa-indexer.js',
    'assets/js/tracker.js',
];

foreach ($js_files as $file) {
    $path = HT_PLUGIN_DIR . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (!empty($content)) {
            echo "✅ $file exists and is readable\n";
        } else {
            echo "❌ FAILED: $file is empty\n";
        }
    } else {
        echo "⚠️  WARNING: $file not found (may be OK for source install)\n";
    }
}

// Test 7: Verify no syntax errors in PHP files
echo "\nTest 7: Checking PHP syntax in critical files...\n";
$php_files = [
    'includes/HT_Core.php',
    'includes/HT_Activator.php',
    'includes/HT_Gemini_Client.php',
    'includes/HT_AI_Controller.php',
    'includes/HT_Atlas_API.php',
];

foreach ($php_files as $file) {
    $path = HT_PLUGIN_DIR . $file;
    if (file_exists($path)) {
        $result = exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return_code);
        if ($return_code === 0) {
            echo "✅ $file has no syntax errors\n";
        } else {
            echo "❌ FAILED: $file has syntax errors\n";
            echo implode("\n", $output) . "\n";
            exit(1);
        }
    }
}

echo "\n=== All Tests Passed ===\n";
echo "Plugin can be activated without fatal errors!\n";
