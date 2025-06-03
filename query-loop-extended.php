<?php

/*
 * Plugin Name: Query Loop Extended
 * Plugin URI: https://mark.honeychurch.org/query-loop-extended
 * Description: Extends the Query Loop block to allow for a much more extensive filtering and ordering.
 * Version: 1.0
 * Author: Mark Honeychurch
 * Author URI: https://mark.honeychurch.org
 * Text Domain: query-loop-extended
 * Domain Path: /languages
 * Requires at least: 5.8
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

$namespace = 'honeychurch/query-loop-extended';



// Frontend query block query, using WP_Query

apply_filters('query_loop_block_query_vars', function($query, $block) { // https://developer.wordpress.org/reference/classes/wp_query/
	if ($block['attrs']['namespace'] === $namespace) {
		add_filter('query_loop_block_query_vars', function($query) {

			$post = get_post(get_the_ID());

			// Filters to select which posts to show

			if ($query->relationship) {
				switch ($query->relationship) {
					case 'children':
						$query->set('post_parent', $post->id);
						break;
					case 'siblings':
						$query->set('post_parent', wp_get_post_parent_id($post->id));
						break;
					case 'parent':
						$query->set('post__in', [wp_get_post_parent_id($post->id)]);
						break;
				}
			}
			if ($query->match) {
				switch ($query->match) {
					case 'author':
						$query->set('author', $post->post_author);
						break;
					case 'category':
						$query->set('category__in', wp_get_post_categories($post->id));
						break;
					case 'tag':
						$query->set('tag__in', wp_get_post_tags($post->id));
						break;
				}
			}

			if ($query->pod_relationship) {
				if (function_exists('pods')) {
					$pod = pods($post->post_type, $post->id, true);
					$related = $pod->field($query->pod_relationship);
					$ids = [];
					foreach ($related as $item) $ids[] = $item['ID'];
					$query->set['post__in'] = $ids;
				}
			}

			// Inclusions/Exclusions for certain posts

			if ($query->exclude_current) {
				$query->set('post__not_in', array_merge([$query->get('post__not_in')], [$post->id]));
			}

			// Date Range Restrictions

			if ($query->date_unit) {
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
				if ($date_direction === 'before') {
					$date_query[] = [
						'column' => $date_posts,
						'after' => $date_earlier,
						'before' => $date_compare_to,
					];
				} elseif ($date_direction === 'after') {
					$date_query[] = [
						'column' => $date_posts,
						'after' => $date_compare_to,
						'before' => $date_later,
					];
				} elseif ($date_direction === 'within') {
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

			// Ordering of Posts

			if ($query->orderBy) {
				if ($query->orderBy === 'tags') {
					// Add a filter to order by the number of tags that match the current post's tags
					add_filter('posts_orderby', function($orderby, $query) {
						global $wpdb;
						if ($query->get('tag__in')) {
							$orderby = "COUNT({$wpdb->term_relationships}.object_id) DESC";
						}
						return $orderby;
					}, 10, 2);
				} else if ($query->orderBy === 'views') {
					// Add a filter to order by the number of views
					add_filter('posts_orderby', function($orderby, $query) {
						global $wpdb;
						$orderby = "CAST({$wpdb->postmeta}.meta_value AS UNSIGNED) DESC";
						$query->set('meta_key', 'post_views_count');
						return $orderby;
					}, 10, 2);
				}
			}

			return $query;
		}, 10, 2);
	}
	return $query;
}, 10, 2);





// Backend (editor) query block query, using the REST API

add_action('wp_loaded', function() { // https://developer.wordpress.org/rest-api/reference/posts/#arguments
	// post__in, post__not_in, search_columns, post_status (publish, etc), date_query
	foreach (get_post_types(['public' => true], 'objects') as $post_type) {
		add_filter("rest_" . $post_type->name . "_query", function($args, $request) {
			$post = get_post($request->get_param('post_id'));

			// error_log(print_r($request->get_param('post_id'), true));
			// error_log(print_r($post->ID, true));

			// Filters to select which posts to show

			if ($request->get_param('relationship')) {
				switch ($request->get_param('relationship')) {
					case 'children':
						$args['post__in'] = get_posts(['post_parent' => $post->id, 'fields' => 'ids']);
						break;
					case 'siblings':
						$args['post_in'] = get_posts(['post_parent' => wp_get_post_parent_id($post->id), 'fields' => 'ids']);
						break;
					case 'parent':
						$args['post__in'] = [wp_get_post_parent_id($post->id)];
						break;
				}
			}

			if ($request->get_param('match')) {
				switch ($request->get_param('match')) {
					case 'author':
						$args['author'] = $post->post_author;
						break;
					case 'category':
						$args['category__in'] = wp_get_post_categories($post->id);
						break;
					case 'tag':
						$args['tag__in'] = wp_get_post_tags($post->id);
						break;
				}
			}

			if ($request->get_param('pod_relationship')) {
				if (function_exists('pods')) {
					$pod = pods($post->post_type, $post->ID, true);
					// error_log($request->get_param('pod_relationship'));
					$related = $pod->field($request->get_param('pod_relationship'));
					$ids = [];
					foreach ($related as $item) $ids[] = $item['ID'];
					// error_log(print_r($ids, true));
					$args['post__in'] = $ids;
				}
			}

			// Inclusions/Exclusions for certain posts

			if ($request->get_param('exclude_current')) {
				$args['post__not_in'] = array_merge([$args['post__not_in']], [$post->id]);
			}

			// Date Range Restrictions

			if ($request->get_param('date_unit')) {
				// if there's a date_unit, check both the unit (day, week, month, year) and date_range (1-12) and use those with the date_direction (before, after or within) and the date_relative (post_date, post_modified or today) to create a date query against one of the post dates, $date_posts (post_date or post_modified).
				$post_date = $post->post_date;
				$post_modified = $post->post_modified;
				$today = date('Y-m-d H:i:s');
				$date_unit = $request->get_param('date_unit');
				$date_range = $request->get_param('date_range');
				$date_direction = $request->get_param('date_direction');
				$date_relative = $request->get_param('date_relative');
				$date_posts = $request->get_param('date_posts');
				$date_query = [];
				$date_compare_to = $date_relative === 'post' ? $post_date : ($date_relative === 'modified' ? $post_modified : $today);
				$date_earlier = date('Y-m-d H:i:s', strtotime("{$date_compare_to} -{$date_range} {$date_unit}"));
				$date_later = date('Y-m-d H:i:s', strtotime("{$date_compare_to} +{$date_range} {$date_unit}"));
				if ($date_direction === 'before') {
					$date_query[] = [
						'column' => $date_posts,
						'after' => $date_earlier,
						'before' => $date_compare_to,
					];
				} elseif ($date_direction === 'after') {
					$date_query[] = [
						'column' => $date_posts,
						'after' => $date_compare_to,
						'before' => $date_later,
					];
				} elseif ($date_direction === 'within') {
					$date_query[] = [
						'column' => $date_posts,
						'after' => $date_earlier,
						'before' => $date_later,
					];
				}
				if (!empty($date_query)) {
					$args['date_query'] = $date_query;
				}
			}

			// Ordering of Posts

			if ($request->get_param('orderBy')) {
				if ($request->get_param('orderBy') === 'tags') { // https://wordpress.org/support/topic/using-query-loop-block-to-show-related-posts/
					// Add a filter to order by the number of tags that match the current post
					add_filter('posts_orderby', function($orderby, $query) use ($request) {
						global $wpdb;
						if ($query->get('tag__in')) {
							$orderby = "COUNT({$wpdb->term_relationships}.object_id) DESC";
						}
						return $orderby;
					}, 10, 2);

				}
			}

			return $args;
		}, 10, 2);
	}
}, 20);


// Enqueue the block editor script
wp_enqueue_script('query',
	plugins_url('query-loop-extended.js', __FILE__),
	array('wp-blocks', 'wp-editor'),
);

// Function to count post views
function count_post_views() {
	if (is_single()) {
		global $post;
		$views = get_post_meta($post->ID, 'post_views_count', true);
		if ($views == '') {
			update_post_meta($post->ID, 'post_views_count', 1);
		} else {
			$views++;
			update_post_meta($post->ID, 'post_views_count', $views);
		}
	}
}
add_action('wp', 'count_post_views');

/*
function create_query_loop_extended_block_init() {
	if (function_exists('wp_register_block_types_from_metadata_collection')) wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
	elseif (function_exists('wp_register_block_metadata_collection')) wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
	else {
		$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( __DIR__ . "/build/{$block_type}" );
		}
	}
}
add_action('init', 'create_query_loop_extended_block_init');
*/

?>
