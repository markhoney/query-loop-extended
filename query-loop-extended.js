(() => {

	const namespace = 'honeychurch/query-loop-extended';

	const controls = {
		Filter: [
			{
				type: 'SelectControl',
				name: 'relationship',
				label: 'Relationship', // 'Get Posts related to Current'
				options: [
					{label: 'None', value: ''},
					{label: 'Siblings', value: 'siblings'},
					{label: 'Children', value: 'children'},
					{label: 'Parent', value: 'parent'},
				],
			},
			{
				type: 'SelectControl',
				name: 'match',
				label: 'Match Current Post Attribute', // 'Match Current Post Attribute'
				options: [
					{label: 'None', value: ''},
					{label: 'Author', value: 'author'},
					{label: 'Categories', value: 'categories'},
					{label: 'Tags', value: 'tags'},
				],
			},
			{
				type: 'TextControl',
				name: 'podRelationshipName',
				label: 'Get Posts from Pod Relationship',
			},
		],
		Exclude: [
			{
				type: 'ToggleControl',
				name: 'excludeCurrent',
				label: 'Current Post',
			},
			{
				type: 'SelectControl',
				name: 'exclude',
				label: 'Exclude Posts',
				options: [
					{label: 'None', value: ''},
					{label: 'Sticky', value: 'sticky'},
					{label: 'Not sticky', value: 'unsticky'},
					{label: 'Featured', value: 'featured'},
					{label: 'Not featured', value: 'unfeatured'},
				],
			},
		],
		Dates: [
			{
				type: 'RangeControl',
				name: 'dateRange',
				label: 'Date Range',
				min: 1,
				max: 12,
			},
			{
				type: 'SelectControl',
				name: 'dateUnit',
				label: 'Date Unit',
				options: [
					{label: 'Days', value: 'day'},
					{label: 'Weeks', value: 'week'},
					{label: 'Months', value: 'month'},
					{label: 'Years', value: 'year'},
				],
			},
			{
				type: 'SelectControl',
				name: 'dateDirection',
				label: 'Date Direction',
				options: [
					{label: 'Within', value: 'within'},
					{label: 'Before', value: 'before'},
					{label: 'After', value: 'after'},
				],
			},
			{
				type: 'SelectControl',
				name: 'dateRelativeTo',
				label: 'Relative to',
				options: [
					{label: 'Current Date', value: 'current'},
					{label: 'Post Date', value: 'post'},
					{label: 'Modified Date', value: 'modified'},
				],
			},
		],
		Ordering: [
			{
				type: 'SelectControl',
				name: 'sort',
				label: 'Sort By',
				options: [
					{label: 'Menu Order', value: 'menu_order'},
					{label: 'Title', value: 'title'},
					{label: 'Date', value: 'date'},
					{label: 'Modified', value: 'modified'},
					{label: 'Author', value: 'author'},
					{label: 'Relevance', value: 'relevance'},
					{label: 'Random', value: 'rand'},
					{label: 'Comment Count', value: 'comment_count'},
					{label: 'Matching Tags', value: 'tags'},
				],
			},
			{
				type: 'SelectControl',
				name: 'order',
				label: 'Sort Order',
				options: [
					{label: 'Ascending', value: 'asc'},
					{label: 'Descending', value: 'desc'},
				],
			},
		],
	};

	const getControl = (category, name) => {
		const control = controls[category].find((control) => control.name === name);
		return ({value, onChange}) => wp.element.createElement(
			control.type,
			{
				...control,
				value,
				onChange,
			},
		);
	};

	wp.blocks.registerBlockVariation('core/query', {
		name: namespace,
		title: 'Query Loop Extended',
		description: 'A Query Loop block with extended features for filtering and sorting posts.',
		// category: 'widgets',
		isActive: ['namespace'],
		icon: 'share-alt',
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
		scope: ['inserter'],
		innerBlocks: [
			[
				'core/post-template',
				// {layout: {type: 'grid', columns: 3}},
				{},
				[['core/group', {}, [['core/post-title'], ['core/post-excerpt']]]],
			],
			// ['core/query-pagination'],
			// ['core/query-no-results'],
		],
	});

	// https://codex.wordpress.org/WordPress_Query_Vars

	const customQueryControls = (BlockEdit) => (props) => {
		if (props.attributes.namespace !== namespace) return wp.element.createElement(BlockEdit, {key: 'edit', ...props});
		return wp.element.createElement(
			wp.element.Fragment,
			null,
			[
				wp.element.createElement(BlockEdit, {key: 'edit', ...props}),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					null,
					[
						wp.element.createElement(getControl('', 'sort'), {
							value: props.attributes.query.orderBy,
							onChange: (value) => props.setAttributes({query: {...props.attributes.query, orderBy: value}}),
						}),
						wp.element.createElement(getControl('order'), {
							value: props.attributes.query.order,
							onChange: (value) => props.setAttributes({query: {...props.attributes.query, order: value}}),
						}),
					],
				),
			],
		);
	};

/*
	const customQueryControls = (BlockEdit) => (props) => {
		if (props.attributes.namespace !== namespace) return wp.element.createElement(BlockEdit, {key: "edit", ...props});
		props.attributes.query.post_id = wp.media.view.settings.post.id;
		return wp.element.createElement(wp.element.Fragment, {}, [
			wp.element.createElement(BlockEdit, {key: "edit", ...props}),
			wp.element.createElement(wp.blockEditor.InspectorControls, {}, [

				// Filters to select which posts to show

				wp.element.createElement(wp.components.PanelBody, {title: 'Filter', initialOpen: false}, [
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Get Posts related to Current',
						value: props.attributes.query.relationship,
						options: [
							{label: 'None', value: ''},
							{label: 'Siblings', value: 'siblings'},
							{label: 'Children', value: 'children'},
							{label: 'Parent', value: 'parent'},
						],
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Match Current Post Attribute',
						value: props.attributes.query.match,
						options: [
							{label: 'None', value: ''},
							{label: 'Author', value: 'author'},
							{label: 'Categories', value: 'categories'},
							{label: 'Tags', value: 'tags'},
						],
					}),
					wp.element.createElement(wp.components.TextControl, {
						label: 'Get Posts from Pod Relationship',
						value: props.attributes.query.pod_relationship,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, pod_relationship: value}}),
					}),
				]),

				// Inclusions/Exclusions for certain posts

				wp.element.createElement(wp.components.PanelBody, {title: 'Include/Exclude', initialOpen: false}, [
					wp.element.createElement(wp.components.ToggleControl, {
						label: 'Exclude Current Post',
						checked: props.attributes.query.exclude_current,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, exclude_current: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Restrict To',
						value: props.attributes.query.restrict,
						options: [
							{label: 'None', value: ''},
							{label: 'Sticky only', value: 'sticky'},
							{label: 'Not sticky', value: 'unsticky'},
							{label: 'Featured only', value: 'featured'},
							{label: 'Not featured', value: 'unfeatured'},
						],
						onchange: (value) => props.setAttributes({query: {...props.attributes.query, restrict: value}}),
					}),
				]),

				// Date Range Restrictions

				wp.element.createElement(wp.components.PanelBody, {title: 'Date Range', initialOpen: false}, [
					wp.element.createElement(wp.components.RangeControl, {
						label: 'Date Range',
						value: props.attributes.query.date_range || 6,
						min: 1,
						max: 12,
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_range: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Date Unit',
						value: props.attributes.query.date_unit || 'month',
						options: [
							{label: 'Days', value: 'day'},
							{label: 'Weeks', value: 'week'},
							{label: 'Months', value: 'month'},
							{label: 'Years', value: 'year'},
						],
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_unit: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Date Direction',
						value: props.attributes.query.date_direction || 'within',
						options: [
							{label: 'Within', value: 'within'},
							{label: 'Before', value: 'before'},
							{label: 'After', value: 'after'},
						],
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_direction: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Relative to',
						value: props.attributes.query.date_relative || 'post',
						options: [
							{label: 'Current Date', value: 'current'},
							{label: 'Post Date', value: 'post'},
							{label: 'Modified Date', value: 'modified'},
						],
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, date_relative: value}}),
					}),
				]),

				// Ordering of Posts

				wp.element.createElement(wp.components.PanelBody, {title: 'Order', initialOpen: false}, [
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Order Posts By',
						value: props.attributes.query.orderBy || 'date',
						// default: 'date',
						options: [
							{label: 'Menu Order', value: 'menu_order'},
							{label: 'Title', value: 'title'},
							{label: 'Date', value: 'date'},
							{label: 'Modified', value: 'modified'},
							{label: 'Author', value: 'author'},
							{label: 'Relevance', value: 'relevance'},
							{label: 'Random', value: 'rand'},
							{label: 'Comment Count', value: 'comment_count'},
							{label: 'Matching Tags', value: 'tags'},
						],
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, orderBy: value}}),
					}),
					wp.element.createElement(wp.components.SelectControl, {
						label: 'Order Direction',
						value: props.attributes.query.order || 'ASC',
						options: [
							{label: 'Ascending', value: 'ASC'},
							{label: 'Descending', value: 'DESC'},
						],
						onChange: (value) => props.setAttributes({query: {...props.attributes.query, order: value}}),
					}),
				]),
			]),
		]);
	};
	*/

	// Export the customQueryControls function
	// module.exports = customQueryControls;
	// export default customQueryControls;

	wp.hooks.addFilter('editor.BlockEdit', 'core/query', customQueryControls);

})();
