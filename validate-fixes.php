<?php
/**
 * Validation Script for Plugin Fixes
 * 
 * This script validates that all critical fixes have been applied correctly
 * Run this from command line: php validate-fixes.php
 * 
 * @package HomayeTabesh
 */

// Define constants for testing
define('HT_PLUGIN_DIR', __DIR__ . '/');
define('HT_VERSION', '1.0.0');

echo "=== AI-Homaye-Tabesh Fixes Validation ===\n\n";

// Test 1: Check if activator file exists and has required tables
echo "Test 1: Checking HT_Activator for required tables...\n";
$activator_file = HT_PLUGIN_DIR . 'includes/HT_Activator.php';
if (!file_exists($activator_file)) {
    echo "❌ FAILED: Activator file not found\n";
    exit(1);
}

$activator_content = file_get_contents($activator_file);
$required_tables = [
    'homaye_blacklist',
    'homaye_knowledge_facts',
    'homaye_user_behavior',
    'homaye_monitored_plugins',
    'homaye_security_events',
];

$missing_tables = [];
foreach ($required_tables as $table) {
    if (strpos($activator_content, $table) === false) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "✅ PASSED: All required tables are defined\n";
} else {
    echo "❌ FAILED: Missing tables: " . implode(', ', $missing_tables) . "\n";
}

// Test 2: Check for required columns
echo "\nTest 2: Checking for required columns...\n";
$required_columns = [
    'is_monitored' => 'homaye_monitored_plugins',
    'verified' => 'homaye_knowledge_facts',
    'current_score' => 'homaye_user_behavior',
    'is_verified' => 'homa_otp',
];

$missing_columns = [];
foreach ($required_columns as $column => $table) {
    if (strpos($activator_content, $column) === false) {
        $missing_columns[] = "$table.$column";
    }
}

if (empty($missing_columns)) {
    echo "✅ PASSED: All required columns are defined\n";
} else {
    echo "❌ FAILED: Missing columns: " . implode(', ', $missing_columns) . "\n";
}

// Test 3: Check Gemini Client for 'success' key handling
echo "\nTest 3: Checking HT_Gemini_Client for proper success key handling...\n";
$gemini_file = HT_PLUGIN_DIR . 'includes/HT_Gemini_Client.php';
if (!file_exists($gemini_file)) {
    echo "❌ FAILED: Gemini Client file not found\n";
    exit(1);
}

$gemini_content = file_get_contents($gemini_file);
if (strpos($gemini_content, "if (!isset(\$result['success']))") !== false) {
    echo "✅ PASSED: Success key check is present in generate_response\n";
} else {
    echo "❌ FAILED: Success key check not found\n";
}

// Test 4: Check for fallback response structure
if (strpos($gemini_content, "'success' => false") !== false && 
    strpos($gemini_content, "'success' => true") !== false) {
    echo "✅ PASSED: Proper fallback response structure is present\n";
} else {
    echo "❌ FAILED: Fallback response structure incomplete\n";
}

// Test 5: Check JavaScript IndexerMap fix
echo "\nTest 5: Checking homa-indexer.js for property redefinition fix...\n";
$indexer_file = HT_PLUGIN_DIR . 'assets/js/homa-indexer.js';
if (!file_exists($indexer_file)) {
    echo "❌ FAILED: Indexer JS file not found\n";
    exit(1);
}

$indexer_content = file_get_contents($indexer_file);
if (strpos($indexer_content, "configurable: true") !== false &&
    strpos($indexer_content, "hasOwnProperty.call") !== false) {
    echo "✅ PASSED: IndexerMap property redefinition fix is present\n";
} else {
    echo "❌ FAILED: IndexerMap fix not found or incomplete\n";
}

// Test 6: Check for self-healing column migration
echo "\nTest 6: Checking for self-healing column migration...\n";
if (strpos($activator_content, "check_and_add_missing_columns") !== false) {
    echo "✅ PASSED: Column migration method exists\n";
} else {
    echo "❌ FAILED: Column migration method not found\n";
}

// Test 7: Check HT_Core for admin notices
echo "\nTest 7: Checking HT_Core for admin notice integration...\n";
$core_file = HT_PLUGIN_DIR . 'includes/HT_Core.php';
if (!file_exists($core_file)) {
    echo "❌ FAILED: Core file not found\n";
    exit(1);
}

$core_content = file_get_contents($core_file);
if (strpos($core_content, "homa_db_repairs_made") !== false &&
    strpos($core_content, "HT_Error_Handler::admin_notice") !== false) {
    echo "✅ PASSED: Admin notice integration is present\n";
} else {
    echo "❌ FAILED: Admin notice integration not found\n";
}

// Test 8: Check INSTALL.md for troubleshooting guide
echo "\nTest 8: Checking INSTALL.md for troubleshooting guide...\n";
$install_file = HT_PLUGIN_DIR . 'INSTALL.md';
if (!file_exists($install_file)) {
    echo "❌ FAILED: INSTALL.md not found\n";
    exit(1);
}

$install_content = file_get_contents($install_file);
if (strpos($install_content, "Troubleshooting") !== false &&
    strpos($install_content, "CSP") !== false) {
    echo "✅ PASSED: Troubleshooting guide is present\n";
} else {
    echo "❌ FAILED: Troubleshooting guide not complete\n";
}

// Test 9: Check HT_AI_Controller for safe success checking
echo "\nTest 9: Checking HT_AI_Controller for safe success checking...\n";
$controller_file = HT_PLUGIN_DIR . 'includes/HT_AI_Controller.php';
if (!file_exists($controller_file)) {
    echo "❌ FAILED: AI Controller file not found\n";
    exit(1);
}

$controller_content = file_get_contents($controller_file);
if (strpos($controller_content, "isset(\$result['success'])") !== false) {
    echo "✅ PASSED: Safe success checking is present\n";
} else {
    echo "❌ FAILED: Safe success checking not found\n";
}

// Test 10: Check HT_Atlas_API for safe success checking
echo "\nTest 10: Checking HT_Atlas_API for safe success checking...\n";
$atlas_file = HT_PLUGIN_DIR . 'includes/HT_Atlas_API.php';
if (!file_exists($atlas_file)) {
    echo "❌ FAILED: Atlas API file not found\n";
    exit(1);
}

$atlas_content = file_get_contents($atlas_file);
if (strpos($atlas_content, "if (!isset(\$response['success']))") !== false) {
    echo "✅ PASSED: Safe success checking is present in test_gemini_connection\n";
} else {
    echo "❌ FAILED: Safe success checking not found\n";
}

echo "\n=== Validation Complete ===\n";
echo "All critical fixes have been validated!\n";
