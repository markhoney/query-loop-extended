<?php

namespace QueryLoopExtended\Tests\Integration;

use WP_UnitTestCase;
use WP_Query;

class QueryLoopIntegrationTest extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test posts
        $this->parent_post = $this->factory->post->create([
            'post_title' => 'Parent Post',
            'post_content' => 'Parent content',
            'post_status' => 'publish'
        ]);

        $this->child_post_1 = $this->factory->post->create([
            'post_title' => 'Child Post 1',
            'post_content' => 'Child 1 content',
            'post_status' => 'publish',
            'post_parent' => $this->parent_post
        ]);

        $this->child_post_2 = $this->factory->post->create([
            'post_title' => 'Child Post 2',
            'post_content' => 'Child 2 content',
            'post_status' => 'publish',
            'post_parent' => $this->parent_post
        ]);

        $this->sibling_post = $this->factory->post->create([
            'post_title' => 'Sibling Post',
            'post_content' => 'Sibling content',
            'post_status' => 'publish',
            'post_parent' => $this->parent_post
        ]);

        // Create test categories
        $this->category_1 = $this->factory->category->create(['name' => 'Test Category 1']);
        $this->category_2 = $this->factory->category->create(['name' => 'Test Category 2']);

        // Create test tags
        $this->tag_1 = $this->factory->tag->create(['name' => 'Test Tag 1']);
        $this->tag_2 = $this->factory->tag->create(['name' => 'Test Tag 2']);

        // Assign categories and tags to posts
        wp_set_post_categories($this->parent_post, [$this->category_1]);
        wp_set_post_terms($this->parent_post, [$this->tag_1], 'post_tag');

        wp_set_post_categories($this->child_post_1, [$this->category_1, $this->category_2]);
        wp_set_post_terms($this->child_post_1, [$this->tag_1, $this->tag_2], 'post_tag');

        wp_set_post_categories($this->child_post_2, [$this->category_1]);
        wp_set_post_terms($this->child_post_2, [$this->tag_1], 'post_tag');
    }

    public function testChildrenRelationshipFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->parent_post));

        // Create a mock query with children relationship
        $query = new WP_Query();
        $query->relationship = 'children';

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter (this would normally be done by WordPress)
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have post_parent set
        $this->assertEquals($this->parent_post, $filtered_query->get('post_parent'));
    }

    public function testSiblingsRelationshipFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->child_post_1));

        // Create a mock query with siblings relationship
        $query = new WP_Query();
        $query->relationship = 'siblings';

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have post_parent set to the parent of the current post
        $this->assertEquals($this->parent_post, $filtered_query->get('post_parent'));
    }

    public function testCategoryMatchFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->parent_post));

        // Create a mock query with category match
        $query = new WP_Query();
        $query->match = 'category';

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have category__in set
        $categories = $filtered_query->get('category__in');
        $this->assertContains($this->category_1, $categories);
    }

    public function testTagMatchFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->parent_post));

        // Create a mock query with tag match
        $query = new WP_Query();
        $query->match = 'tag';

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have tag__in set
        $tags = $filtered_query->get('tag__in');
        $this->assertContains($this->tag_1, $tags);
    }

    public function testExcludeCurrentPostFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->parent_post));

        // Create a mock query with exclude_current
        $query = new WP_Query();
        $query->exclude_current = true;

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have post__not_in set
        $excluded_posts = $filtered_query->get('post__not_in');
        $this->assertContains($this->parent_post, $excluded_posts);
    }

    public function testDateRangeFilter()
    {
        // Set up the current post context
        $this->go_to(get_permalink($this->parent_post));

        // Create a mock query with date range
        $query = new WP_Query();
        $query->date_unit = 'month';
        $query->date_range = 6;
        $query->date_direction = 'within';
        $query->date_relative = 'post';
        $query->date_posts = 'post_date';

        // Create mock block attributes
        $block = ['attrs' => ['namespace' => 'honeychurch/query-loop-extended']];

        // Apply the filter
        $filtered_query = apply_filters('query_loop_block_query_vars', $query, $block);

        // The query should now have date_query set
        $date_query = $filtered_query->get('date_query');
        $this->assertIsArray($date_query);
        $this->assertNotEmpty($date_query);
    }

    public function testViewCountingFunction()
    {
        // Create a test post
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post for Views',
            'post_status' => 'publish'
        ]);

        // Set up the current post context
        $this->go_to(get_permalink($post_id));

        // Mock that we're not logged in
        $this->logout();

        // Trigger the view counting function
        count_post_views();

        // Check that the view count was incremented
        $views = get_post_meta($post_id, 'views_count', true);
        $this->assertEquals('1', $views);

        // Trigger it again
        count_post_views();

        // Check that the view count was incremented again
        $views = get_post_meta($post_id, 'views_count', true);
        $this->assertEquals('2', $views);
    }

    public function testViewCountingFunctionNotLoggedIn()
    {
        // Create a test post
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post for Views',
            'post_status' => 'publish'
        ]);

        // Set up the current post context
        $this->go_to(get_permalink($post_id));

        // Mock that we're logged in
        $this->login_as('admin');

        // Trigger the view counting function
        count_post_views();

        // Check that the view count was NOT incremented for logged-in users
        $views = get_post_meta($post_id, 'views_count', true);
        $this->assertEmpty($views);
    }

    public function testRESTAPIQueryFilter()
    {
        // Create a test post
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post for REST API',
            'post_status' => 'publish'
        ]);

        // Mock the REST API request
        $request = new \WP_REST_Request('GET', '/wp/v2/posts');
        $request->set_param('post_id', $post_id);
        $request->set_param('relationship', 'children');

        // Create initial args
        $args = ['post_type' => 'post'];

        // Apply the REST API filter
        $filtered_args = apply_filters('rest_post_query', $args, $request);

        // The args should now have post__in set with the children
        $this->assertArrayHasKey('post__in', $filtered_args);
    }
}