<?php

namespace QueryLoopExtended\Tests\Unit;

use PHPUnit\Framework\TestCase;

class JavaScriptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testJavaScriptFileExists()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $this->assertFileExists($js_file, 'JavaScript file should exist');
    }

    public function testJavaScriptFileIsReadable()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $this->assertIsReadable($js_file, 'JavaScript file should be readable');
    }

    public function testJavaScriptFileContainsNamespace()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'honeychurch/query-loop-extended',
            $content,
            'JavaScript file should contain the correct namespace'
        );
    }

    public function testJavaScriptFileContainsBlockVariation()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'registerBlockVariation',
            $content,
            'JavaScript file should contain block variation registration'
        );
    }

    public function testJavaScriptFileContainsCustomControls()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'customQueryControls',
            $content,
            'JavaScript file should contain custom query controls'
        );
    }

    public function testJavaScriptFileContainsInspectorControls()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'InspectorControls',
            $content,
            'JavaScript file should contain inspector controls'
        );
    }

    public function testJavaScriptFileContainsRelationshipOptions()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'siblings',
            $content,
            'JavaScript file should contain siblings relationship option'
        );

        $this->assertStringContainsString(
            'children',
            $content,
            'JavaScript file should contain children relationship option'
        );

        $this->assertStringContainsString(
            'parent',
            $content,
            'JavaScript file should contain parent relationship option'
        );
    }

    public function testJavaScriptFileContainsMatchOptions()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'author',
            $content,
            'JavaScript file should contain author match option'
        );

        $this->assertStringContainsString(
            'category',
            $content,
            'JavaScript file should contain category match option'
        );

        $this->assertStringContainsString(
            'tags',
            $content,
            'JavaScript file should contain tags match option'
        );
    }

    public function testJavaScriptFileContainsDateRangeOptions()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'Date Range',
            $content,
            'JavaScript file should contain date range panel'
        );

        $this->assertStringContainsString(
            'within',
            $content,
            'JavaScript file should contain within date direction'
        );

        $this->assertStringContainsString(
            'before',
            $content,
            'JavaScript file should contain before date direction'
        );

        $this->assertStringContainsString(
            'after',
            $content,
            'JavaScript file should contain after date direction'
        );
    }

    public function testJavaScriptFileContainsOrderingOptions()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'Order Posts By',
            $content,
            'JavaScript file should contain order posts by control'
        );

        $this->assertStringContainsString(
            'views',
            $content,
            'JavaScript file should contain views ordering option'
        );

        $this->assertStringContainsString(
            'tags',
            $content,
            'JavaScript file should contain tags ordering option'
        );
    }

    public function testJavaScriptFileContainsHookFilter()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        $this->assertStringContainsString(
            'addFilter',
            $content,
            'JavaScript file should contain addFilter hook'
        );

        $this->assertStringContainsString(
            'editor.BlockEdit',
            $content,
            'JavaScript file should contain editor.BlockEdit filter'
        );
    }

    public function testJavaScriptFileIsValidSyntax()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        // Basic syntax checks
        $this->assertStringContainsString(
            '(() => {',
            $content,
            'JavaScript file should start with IIFE'
        );

        $this->assertStringContainsString(
            '})();',
            $content,
            'JavaScript file should end with IIFE closure'
        );

        // Check for balanced parentheses and braces
        $open_parens = substr_count($content, '(');
        $close_parens = substr_count($content, ')');
        $this->assertEquals($open_parens, $close_parens, 'Parentheses should be balanced');

        $open_braces = substr_count($content, '{');
        $close_braces = substr_count($content, '}');
        $this->assertEquals($open_braces, $close_braces, 'Braces should be balanced');
    }

    public function testJavaScriptFileHasNoConsoleErrors()
    {
        $js_file = dirname(dirname(__DIR__)) . '/query-loop-extended.js';
        $content = file_get_contents($js_file);

        // Check for common JavaScript errors
        $this->assertStringNotContainsString(
            'console.error',
            $content,
            'JavaScript file should not contain console.error statements'
        );

        $this->assertStringNotContainsString(
            'console.warn',
            $content,
            'JavaScript file should not contain console.warn statements'
        );
    }
}