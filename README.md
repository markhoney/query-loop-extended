# Query Loop Extended

A WordPress plugin that extends the Query Loop block to allow for much more extensive filtering and ordering of posts.

## Features

- **Relationship Filtering**: Show children, siblings, or parent posts
- **Content Matching**: Filter by same author, category, or tags
- **Pod Relationships**: Support for Pods plugin relationships
- **Date Range Filtering**: Filter posts by date ranges relative to current post or date
- **Advanced Ordering**: Order by views, matching tags, and more
- **Current Post Exclusion**: Automatically exclude the current post from results

## Installation

1. Upload the plugin files to `/wp-content/plugins/query-loop-extended/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the 'Query Loop Extended' block in the block editor

## Development

### Prerequisites

- PHP 7.4 or higher
- Composer
- WordPress development environment

### Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

### Testing

The plugin includes comprehensive testing using PHPUnit and WordPress testing framework.

#### Running Tests

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration

# Run tests with coverage report
composer test:coverage
```

#### Test Structure

- **Unit Tests** (`tests/Unit/`): Test individual functions and components in isolation
- **Integration Tests** (`tests/Integration/`): Test the plugin with actual WordPress functionality
- **JavaScript Tests** (`tests/Unit/JavaScriptTest.php`): Test JavaScript file integrity and syntax

#### Test Coverage

The test suite covers:
- Query filtering functionality
- Relationship filtering (children, siblings, parent)
- Content matching (author, category, tags)
- Date range filtering
- Post exclusion
- View counting
- REST API integration
- JavaScript block editor integration

### Continuous Integration

The plugin uses GitHub Actions for automated testing:
- Runs tests on multiple PHP versions (7.4, 8.0, 8.1, 8.2)
- Tests against different WordPress versions
- Includes linting and security audits
- Generates coverage reports

## Usage

### Block Editor

1. Add a 'Query Loop Extended' block to your page/post
2. Configure the filtering options in the block sidebar:
   - **Restrict to**: Choose relationship filtering
   - **Posts with the Same**: Filter by matching content
   - **Exclude**: Exclude current post
   - **Date Range**: Filter by date ranges
   - **Order**: Choose ordering method and direction

### Programmatic Usage

The plugin adds filters to WordPress queries that can be used programmatically:

```php
// Example: Get children posts
$args = array(
    'relationship' => 'children',
    'namespace' => 'honeychurch/query-loop-extended'
);
$query = new WP_Query($args);
```

## Configuration

### Date Range Options

- **Date Unit**: day, week, month, year
- **Date Range**: 1-12 units
- **Date Direction**: before, after, within
- **Relative to**: current date, post date, modified date
- **Date Column**: post_date, post_modified

### Ordering Options

- Menu Order
- Title
- Date
- Modified
- Author
- Random
- Comment Count
- Matching Tags
- Views

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Block editor (Gutenberg)

## Changelog

### 1.0
- Initial release
- Basic query filtering functionality
- Block editor integration
- View counting feature

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please visit the [plugin page](https://mark.honeychurch.org/query-loop-extended) or create an issue on GitHub.
