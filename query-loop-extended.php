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


// Backend (editor) query block query, using the REST API

// https://developer.wordpress.org/rest-api/reference/posts/#arguments

add_action('wp_loaded', function() {
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

			if ($request->get_param('restrict')) {
				if ($request->get_param('restrict') === 'sticky') {
					$args['post__in'] = get_option('sticky_posts');
				} else if ($request->get_param('restrict') === 'unsticky') {
					$args['post__not_in'] = get_option('sticky_posts');
				} else if ($request->get_param('restrict') === 'featured') {
					$args['meta_key'] = 'featured';
					$args['meta_value'] = '1';
				} else if ($request->get_param('restrict') === 'unfeatured') {
					$args['meta_key'] = 'featured';
					$args['meta_value'] = '0';
				}
			}

			// Date Range Restrictions

			if ($request->get_param('date_range')) {
				$args['date_query'] = [
					'column' => $request->get_param('date_relative') === 'post' ? 'post_date' : 'post_modified',
				];
				if ($request->get_param('date_relative') === 'current') {
					$args['date_query']['after'] = date('Y-m-d H:i:s', strtotime('-' . $request->get_param('date_range')));
				} else if ($request->get_param('date_relative') === 'past') {
					$args['date_query']['before'] = date('Y-m-d H:i:s', strtotime('-' . $request->get_param('date_range')));
				}
			}

			// Ordering of Posts

			if ($request->get_param('orderBy')) {
				if ($request->get_param('orderBy') === 'tags') {
					// https://wordpress.org/support/topic/using-query-loop-block-to-show-related-posts/
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


// Frontend query block query, using WP_Query

// https://developer.wordpress.org/reference/classes/wp_query/

apply_filters('query_loop_block_query_vars', function($query, $block) {
	if ($block['attrs']['namespace'] === 'telesmart/query') {
		add_filter('query_loop_block_query_vars', function($query) {
			$post = get_post(get_the_ID());
			if ($query->pod_relationship) {
				if (function_exists('pods')) {
					$pod = pods($post->post_type, $post->id, true);
					$related = $pod->field($query->pod_relationship);
					$ids = [];
					foreach ($related as $item) $ids[] = $item['ID'];
					$query->set['post__in'] = $ids;
				}

			}
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
			/* if ($query->current_author) {
				$query->set('author', get_the_author_meta('ID'));
			}
			if ($query->current_category) {
				$query->set('category__in', wp_get_post_categories($post->id));
			}
			if ($query->current_tag) {
				$query->set('tag__in', wp_get_post_tags($post->id));
			} */
			if ($query->exclude_current) {
				$query->set('post__not_in', array_merge([$query->get('post__not_in')], [$post->id]));
			}
			if ($query->restrict) {
				if ($query->restrict === 'sticky') {
					$query->set('post__in', get_option('sticky_posts'));
				} else if ($query->restrict === 'unsticky') {
					$query->set('post__not_in', get_option('sticky_posts'));
				} else if ($query->restrict === 'featured') {
					$query->set('meta_key', 'featured');
					$query->set('meta_value', '1');
				} else if ($query->restrict === 'unfeatured') {
					$query->set('meta_key', 'featured');
					$query->set('meta_value', '0');
				}
			}
			if ($query->date_range) {
				$query->set('date_query', [
					'column' => $query->date_relative === 'post' ? 'post_date' : 'post_modified',
				]);
			}
			return $query;
		}, 10, 2);
	}
	return $query;
}, 10, 2);

wp_enqueue_script('query-loop-extended',
	get_stylesheet_directory_uri() . '/query-loop-extended.js',
	array('wp-blocks', 'wp-editor'),
);

?>
