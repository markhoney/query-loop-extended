(() => { // https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/extending-the-query-loop-block/

	const namespace = 'honeychurch/query-loop-extended';

	wp.blocks.registerBlockVariation('core/query', {
		name: namespace,
		title: 'Query Loop Extended',
		description: 'A Query Loop block with extended features for filtering and sorting posts.',
		// category: 'widgets',
		isActive: ['namespace'],
		scope: ['inserter'],
		icon: 'share-alt',
		innerBlocks: [
			[
				'core/post-template',
				// {layout: {type: 'grid', columns: 3}},
				{},
				[['core/group', {}, [['core/post-title'], ['core/post-excerpt']]]],
			],
		],
		attributes: {
			namespace,
			query: {
				perPage: 3,
				pages: 0,
				offset: 0,
				// postType: 'book',
				order: 'desc',
				orderBy: 'date',
				author: '',
				search: '',
				exclude: [],
				sticky: '',
				inherit: false,
				relationship: '',
				match: '',
				pod_relationship: '',
				exclude_current: true,
				date_unit: 'month',
				date_range: 6,
				date_direction: 'within',
				date_relative: 'post',
				date_posts: 'post_date',
			},
		},
		supports: { // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
			anchor: true,
			align: true,
			background: {
				backgroundImage: true,
				backgroundSize: true,
			},
			color: {
				gradients: true,
				heading: true,
				link: true,
			},
			dimensions: {
				aspectRatio: true,
				minHeight: true,
			},
			layout: {
				// allowSwitching: true,
				allowSizingOnChildren: false,
			},
			position: {
				sticky: true,
			},
			shadow: true,
			spacing: {
				margin: true,
				padding: true,
				blockGap: true,
			},
			typography: {
				fontSize: true,
				lineHeight: true,
				textAlign: true,
			},
		},
		allowedControls: [
			'inherit',
			'postType',
			// 'order',
			'sticky',
			'taxQuery',
			'author',
			'search',
			'exclude',
			'perPage',
			'pages',
			'offset',
			'sort',
		],
	});

	const customQueryControls = (BlockEdit) => (props) => { // https://codex.wordpress.org/WordPress_Query_Vars
		if (props.attributes.namespace !== namespace) return wp.element.createElement(BlockEdit, {key: "edit", ...props});
		props.attributes.query.post_id = wp.media.view.settings.post.id;
		return wp.element.createElement(wp.element.Fragment, {}, [
			wp.element.createElement(BlockEdit, {key: "edit", ...props}),
			wp.element.createElement(wp.blockEditor.InspectorControls, {}, [

				// Filters to select which posts to show

				wp.element.createElement(wp.components.PanelBody, {title: 'Restrict to', initialOpen: false}, [
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Posts that Are',
						options: [
							{label: 'None', value: ''},
							{label: 'Siblings', value: 'siblings'},
							{label: 'Children', value: 'children'},
							{label: 'Parent', value: 'parent'},
						],
						value: props.attributes.query.relationship || '',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, relationship: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Posts with the Same',
						options: [
							{label: 'None', value: ''},
							{label: 'Author', value: 'author'},
							{label: 'Categories', value: 'categories'},
							{label: 'Tags', value: 'tags'},
						],
						value: props.attributes.query.match || '',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, match: value}}),
					}),
					wp.element.createElement(wp.components.TextControl, {
						label: 'Posts from Pod Relationship Field',
						value: props.attributes.query.pod_relationship,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, pod_relationship: value}}),
					}),
				]),

				// Exclusions for certain posts

				wp.element.createElement(wp.components.PanelBody, {title: 'Exclude', initialOpen: false}, [
					wp.element.createElement(wp.components.ToggleControl, {
						label: 'Current Post',
						checked: props.attributes.query.exclude_current || true,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, exclude_current: value}}),
					}),
				]),

				// Date Range Restrictions

				wp.element.createElement(wp.components.PanelBody, {title: 'Date Range', initialOpen: false}, [
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Date Unit',
						options: [
							{label: 'None', value: ''},
							{label: 'Days', value: 'day'},
							{label: 'Weeks', value: 'week'},
							{label: 'Months', value: 'month'},
							{label: 'Years', value: 'year'},
						],
						value: props.attributes.query.date_unit || 'month',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_unit: value}}),
					}),
					wp.element.createElement(wp.components.RangeControl, {
						label: 'Date Range',
						min: 1,
						max: 12,
						value: props.attributes.query.date_range || 6,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_range: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Date Direction',
						options: [
							{label: 'Within', value: 'within'},
							{label: 'Before', value: 'before'},
							{label: 'After', value: 'after'},
						],
						value: props.attributes.query.date_direction || 'within',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_direction: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Reference Date',
						options: [
							{label: 'Current Date', value: 'current'},
							{label: "This Post's Published Date", value: 'post'},
							{label: "This Post's Modified Date", value: 'modified'},
						],
						value: props.attributes.query.date_relative || 'post',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_relative: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Posts Date to Compare with',
						options: [
							{label: 'Published Date', value: 'post_date'},
							{label: 'Modified Date', value: 'post_modified'},
						],
						value: props.attributes.query.date_posts || 'post_date',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_posts: value}}),
					}),
				]),

				// Ordering of Posts

				wp.element.createElement(wp.components.PanelBody, {title: 'Order', initialOpen: false}, [
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Order Posts By',
						// default: 'date',
						options: [
							{label: 'Menu Order', value: 'menu_order'},
							{label: 'Title', value: 'title'},
							{label: 'Date', value: 'date'},
							{label: 'Modified', value: 'modified'},
							{label: 'Author', value: 'author'},
							// {label: 'Relevance', value: 'relevance'},
							{label: 'Random', value: 'rand'},
							{label: 'Comment Count', value: 'comment_count'},
							{label: 'Matching Tags', value: 'tags'},
							{label: 'Views', value: 'views'},
						],
						value: props.attributes.query.orderBy || 'date',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, orderBy: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Order Direction',
						options: [
							{label: 'Ascending', value: 'asc'},
							{label: 'Descending', value: 'desc'},
						],
						value: props.attributes.query.order || 'desc',
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, order: value}}),
					}),
				]),
			]),
		]);
	};


	// Export the customQueryControls function
	// module.exports = customQueryControls;
	// export default customQueryControls;

	wp.hooks.addFilter('editor.BlockEdit', 'core/query', customQueryControls);

})();
