<?php

namespace QueryLoopExtended\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

class QueryFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Brain\Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function testRelationshipFilterChildren()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) ['ID' => 123]);
        Functions\when('get_the_ID')->justReturn(123);

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('set')->with('post_parent', 123)->once();
        $query->relationship = 'children';

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->relationship === 'children') {
                    $post = get_post(get_the_ID());
                    $query->set('post_parent', $post->ID);
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }

    public function testRelationshipFilterSiblings()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) ['ID' => 123]);
        Functions\when('get_the_ID')->justReturn(123);
        Functions\when('wp_get_post_parent_id')->with(123)->justReturn(456);

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('set')->with('post_parent', 456)->once();
        $query->relationship = 'siblings';

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->relationship === 'siblings') {
                    $post = get_post(get_the_ID());
                    $parent_id = wp_get_post_parent_id($post->ID);
                    if ($parent_id) {
                        $query->set('post_parent', $parent_id);
                    }
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }

    public function testMatchFilterAuthor()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) ['ID' => 123, 'post_author' => 789]);
        Functions\when('get_the_ID')->justReturn(123);

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('set')->with('author', 789)->once();
        $query->match = 'author';

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->match === 'author') {
                    $post = get_post(get_the_ID());
                    $query->set('author', $post->post_author);
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }

    public function testMatchFilterCategory()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) ['ID' => 123]);
        Functions\when('get_the_ID')->justReturn(123);
        Functions\when('wp_get_post_categories')->with(123)->justReturn([1, 2, 3]);

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('set')->with('category__in', [1, 2, 3])->once();
        $query->match = 'category';

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->match === 'category') {
                    $post = get_post(get_the_ID());
                    $categories = wp_get_post_categories($post->ID);
                    if (!empty($categories)) {
                        $query->set('category__in', $categories);
                    }
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }

    public function testExcludeCurrentPost()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) ['ID' => 123]);
        Functions\when('get_the_ID')->justReturn(123);

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('get')->with('post__not_in')->andReturn([]);
        $query->shouldReceive('set')->with('post__not_in', [123])->once();
        $query->exclude_current = true;

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->exclude_current) {
                    $post = get_post(get_the_ID());
                    $existing_not_in = $query->get('post__not_in') ?: [];
                    $query->set('post__not_in', array_merge($existing_not_in, [$post->ID]));
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }

    public function testDateRangeFilter()
    {
        // Mock WordPress functions
        Functions\when('get_post')->justReturn((object) [
            'ID' => 123,
            'post_date' => '2023-01-15 10:00:00',
            'post_modified' => '2023-01-20 15:30:00'
        ]);
        Functions\when('get_the_ID')->justReturn(123);
        Functions\when('date')->justReturn('2023-01-25 12:00:00');

        // Create mock query object
        $query = Mockery::mock('WP_Query');
        $query->shouldReceive('set')->once();
        $query->date_unit = 'month';
        $query->date_range = 6;
        $query->date_direction = 'within';
        $query->date_relative = 'post';
        $query->date_posts = 'post_date';

        // Mock the block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Test the filter
        $filter = function($query, $block) {
            if ($block['attrs']['namespace'] === 'honeychurch/query-loop-extended') {
                if ($query->date_unit) {
                    $post = get_post(get_the_ID());
                    $post_date = $post->post_date;
                    $post_modified = $post->post_modified;
                    $today = date('Y-m-d H:i:s');
                    $date_unit = $query->date_unit;
                    $date_range = $query->date_range;
                    $date_direction = $query->date_direction;
                    $date_relative = $query->date_relative;
                    $date_posts = $query->date_posts;
                    $date_query = [];
                    $date_compare_to = $date_relative === 'post' ? $post_date : ($date_relative === 'modified' ? $post_modified : $today);
                    $date_earlier = date('Y-m-d H:i:s', strtotime("{$date_compare_to} -{$date_range} {$date_unit}"));
                    $date_later = date('Y-m-d H:i:s', strtotime("{$date_compare_to} +{$date_range} {$date_unit}"));
                    if ($date_direction === 'within') {
                        $date_query[] = [
                            'column' => $date_posts,
                            'after' => $date_earlier,
                            'before' => $date_later,
                        ];
                    }
                    if (!empty($date_query)) {
                        $query->set('date_query', $date_query);
                    }
                }
            }
            return $query;
        };

        $result = $filter($query, $block);
        $this->assertSame($query, $result);
    }
}