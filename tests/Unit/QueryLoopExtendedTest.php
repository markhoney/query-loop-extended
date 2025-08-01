<?php

namespace QueryLoopExtended\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

class QueryLoopExtendedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function testPluginConstants()
    {
        $this->assertTrue(defined('ABSPATH'));
    }

    public function testNamespaceConstant()
    {
        $this->assertEquals('honeychurch/query-loop-extended', $GLOBALS['namespace']);
    }

    public function testFrontendQueryFilterIsAdded()
    {
        // Mock the apply_filters function
        Functions\when('apply_filters')->justReturn(true);

        // Trigger the plugin loading
        require_once dirname(dirname(__DIR__)) . '/query-loop-extended.php';

        // Verify that the filter was added
        $this->assertTrue(Functions\applied('query_loop_block_query_vars'));
    }

    public function testBackendQueryFilterIsAdded()
    {
        // Mock WordPress functions
        Functions\when('add_action')->justReturn(true);
        Functions\when('get_post_types')->justReturn([]);

        // Trigger the plugin loading
        require_once dirname(dirname(__DIR__)) . '/query-loop-extended.php';

        // Verify that the action was added
        $this->assertTrue(Actions\did('wp_loaded'));
    }

    public function testScriptEnqueueIsAdded()
    {
        // Mock WordPress functions
        Functions\when('add_action')->justReturn(true);
        Functions\when('wp_enqueue_script')->justReturn(true);
        Functions\when('plugins_url')->justReturn('http://example.com/plugin.js');

        // Trigger the plugin loading
        require_once dirname(dirname(__DIR__)) . '/query-loop-extended.php';

        // Verify that the script enqueue action was added
        $this->assertTrue(Actions\did('enqueue_block_editor_assets'));
    }

    public function testViewCountingFunctionIsAdded()
    {
        // Mock WordPress functions
        Functions\when('add_action')->justReturn(true);
        Functions\when('is_single')->justReturn(true);
        Functions\when('is_user_logged_in')->justReturn(false);
        Functions\when('get_post_meta')->justReturn('1');
        Functions\when('update_post_meta')->justReturn(true);

        // Trigger the plugin loading
        require_once dirname(dirname(__DIR__)) . '/query-loop-extended.php';

        // Verify that the view counting action was added
        $this->assertTrue(Actions\did('wp'));
    }
}