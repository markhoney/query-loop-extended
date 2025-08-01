<?php
/**
 * PHPUnit bootstrap file
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// WordPress test environment
if (!defined('WP_TESTS_DIR')) {
    define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');
}

if (!defined('WP_CORE_DIR')) {
    define('WP_CORE_DIR', getenv('WP_CORE_DIR') ?: '/tmp/wordpress/');
}

// Load WordPress test environment
require_once WP_TESTS_DIR . '/includes/functions.php';
require_once WP_TESTS_DIR . '/includes/bootstrap.php';

// Load the plugin
require_once dirname(__DIR__) . '/query-loop-extended.php';

// Set up test environment
if (!function_exists('wp_install')) {
    require_once WP_CORE_DIR . '/wp-admin/includes/upgrade.php';
}

// Create test database tables
if (!function_exists('_delete_all_data')) {
    require_once WP_TESTS_DIR . '/includes/testcase.php';
}

// Load Brain Monkey for mocking
Brain\Monkey\setUp();