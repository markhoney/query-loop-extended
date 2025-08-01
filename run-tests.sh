#!/bin/bash

# Query Loop Extended - Test Runner
# This script provides an easy way to run tests for the plugin

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Composer is installed
check_composer() {
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed. Please install Composer first."
        exit 1
    fi
}

# Check if PHP is available
check_php() {
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed or not in PATH."
        exit 1
    fi

    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_status "PHP version: $PHP_VERSION"
}

# Install dependencies
install_dependencies() {
    print_status "Installing Composer dependencies..."
    composer install --prefer-dist --no-progress
    print_success "Dependencies installed successfully"
}

# Run tests
run_tests() {
    local test_type=$1

    case $test_type in
        "all")
            print_status "Running all tests..."
            composer test
            ;;
        "unit")
            print_status "Running unit tests..."
            composer test:unit
            ;;
        "integration")
            print_status "Running integration tests..."
            composer test:integration
            ;;
        "coverage")
            print_status "Running tests with coverage..."
            composer test:coverage
            ;;
        *)
            print_error "Unknown test type: $test_type"
            print_status "Available options: all, unit, integration, coverage"
            exit 1
            ;;
    esac
}

# Run linting
run_lint() {
    print_status "Running PHP linting..."

    # Find all PHP files and check syntax
    find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" | while read -r file; do
        if php -l "$file" > /dev/null 2>&1; then
            print_success "✓ $file"
        else
            print_error "✗ $file"
            php -l "$file"
        fi
    done
}

# Check JavaScript syntax
check_js() {
    print_status "Checking JavaScript syntax..."

    if command -v node &> /dev/null; then
        if node -c query-loop-extended.js; then
            print_success "JavaScript syntax is valid"
        else
            print_error "JavaScript syntax errors found"
            exit 1
        fi
    else
        print_warning "Node.js not available, skipping JavaScript syntax check"
    fi
}

# Show help
show_help() {
    echo "Query Loop Extended - Test Runner"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  install     Install Composer dependencies"
    echo "  test        Run all tests"
    echo "  unit        Run unit tests only"
    echo "  integration Run integration tests only"
    echo "  coverage    Run tests with coverage report"
    echo "  lint        Run PHP linting"
    echo "  js          Check JavaScript syntax"
    echo "  all         Run all checks (install, lint, js, test)"
    echo "  help        Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 install"
    echo "  $0 test"
    echo "  $0 unit"
    echo "  $0 all"
}

# Main script logic
main() {
    local command=$1

    if [ -z "$command" ]; then
        show_help
        exit 1
    fi

    case $command in
        "install")
            check_composer
            check_php
            install_dependencies
            ;;
        "test"|"unit"|"integration"|"coverage")
            check_composer
            check_php
            run_tests "$command"
            ;;
        "lint")
            run_lint
            ;;
        "js")
            check_js
            ;;
        "all")
            check_composer
            check_php
            install_dependencies
            run_lint
            check_js
            run_tests "all"
            print_success "All checks completed successfully!"
            ;;
        "help"|"-h"|"--help")
            show_help
            ;;
        *)
            print_error "Unknown command: $command"
            show_help
            exit 1
            ;;
    esac
}

# Run main function with all arguments
main "$@"