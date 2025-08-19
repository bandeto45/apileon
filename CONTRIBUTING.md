# Contributing to Apileon

Thank you for your interest in contributing to Apileon! We welcome contributions from the community and are grateful for your support.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Documentation](#documentation)
- [Submitting Changes](#submitting-changes)
- [Release Process](#release-process)

## Code of Conduct

By participating in this project, you agree to abide by our Code of Conduct:

- **Be respectful** - Treat everyone with respect and kindness
- **Be inclusive** - Welcome newcomers and encourage diverse perspectives
- **Be constructive** - Provide helpful feedback and suggestions
- **Be patient** - Remember that everyone has different skill levels
- **Be professional** - Keep discussions focused on technical matters

## Getting Started

### Types of Contributions

We welcome several types of contributions:

1. **Bug Reports** - Help us identify and fix issues
2. **Feature Requests** - Suggest new features or improvements
3. **Code Contributions** - Submit bug fixes or new features
4. **Documentation** - Improve or expand documentation
5. **Testing** - Add or improve test coverage
6. **Examples** - Create example applications or tutorials

### Finding What to Work On

- Check the [Issues](https://github.com/username/apileon/issues) page for open issues
- Look for issues labeled `good first issue` for beginners
- Look for issues labeled `help wanted` for community contributions
- Review the [Roadmap](#roadmap) for planned features

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Git
- Composer (optional, for full development experience)

### Setup Instructions

1. **Fork the repository**
```bash
# Click the "Fork" button on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/apileon.git
cd apileon
```

2. **Set up the development environment**
```bash
# With Composer (recommended for development)
composer install

# Or without Composer
chmod +x setup-no-composer.sh
./setup-no-composer.sh
```

3. **Create environment configuration**
```bash
cp .env.example .env
# Edit .env with your development settings
```

4. **Run tests to verify setup**
```bash
# With Composer
composer test

# Without Composer
php test-no-composer.php
```

5. **Start the development server**
```bash
php -S localhost:8000 -t public
```

### Development Workflow

1. Create a feature branch from `main`:
```bash
git checkout -b feature/your-feature-name
```

2. Make your changes
3. Run tests frequently
4. Commit your changes with clear messages
5. Push to your fork
6. Submit a Pull Request

## How to Contribute

### Reporting Bugs

When reporting bugs, please include:

1. **Clear description** of the issue
2. **Steps to reproduce** the problem
3. **Expected behavior** vs actual behavior
4. **Environment details** (PHP version, OS, etc.)
5. **Code examples** if applicable

**Bug Report Template:**
```markdown
## Bug Description
A clear description of what the bug is.

## Steps to Reproduce
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior
A clear description of what you expected to happen.

## Actual Behavior
A clear description of what actually happened.

## Environment
- PHP Version: [e.g. 8.1.0]
- OS: [e.g. Ubuntu 20.04]
- Server: [e.g. Apache 2.4]

## Additional Context
Add any other context about the problem here.
```

### Requesting Features

When requesting features, please include:

1. **Clear description** of the feature
2. **Use case** - why is this needed?
3. **Proposed solution** (if you have ideas)
4. **Alternative solutions** considered
5. **Impact assessment** - who would benefit?

## Coding Standards

### PHP Standards

We follow PSR standards where applicable:

- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloading Standard
- **PSR-12**: Extended Coding Style

### Code Style

1. **Indentation**: 4 spaces (no tabs)
2. **Line endings**: Unix (LF)
3. **Line length**: 120 characters max
4. **Encoding**: UTF-8

### Naming Conventions

```php
// Classes: PascalCase
class UserController
{
    // Methods: camelCase
    public function getUserById(int $id): Response
    {
        // Variables: camelCase
        $userName = 'example';
        
        // Constants: UPPER_SNAKE_CASE
        const MAX_USERS = 100;
    }
}

// Interfaces: PascalCase with Interface suffix
interface PaymentInterface
{
}

// Traits: PascalCase with Trait suffix
trait ValidationTrait
{
}
```

### Type Declarations

Use strict typing:

```php
<?php
declare(strict_types=1);

namespace Apileon\Example;

class Example
{
    public function process(string $input, int $count = 1): array
    {
        // Implementation
    }
}
```

## Testing Guidelines

### Test Requirements

- All new features must include tests
- Bug fixes should include tests that verify the fix
- Aim for 80%+ code coverage
- Tests should be fast and reliable

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/UserControllerTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Run without Composer
php test-no-composer.php
```

## Documentation

### Documentation Standards

- Use clear, concise language
- Include code examples
- Follow the existing documentation structure
- Update relevant documentation when making changes

## Submitting Changes

### Pull Request Process

1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Add** tests for new functionality
5. **Update** documentation
6. **Run** tests and ensure they pass
7. **Commit** with clear messages
8. **Push** to your fork
9. **Submit** a Pull Request

### Commit Message Format

Use conventional commit format:

```
type(scope): subject

body (optional)

footer (optional)
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(routing): add support for route groups

Add middleware support to route groups allowing for easier
application of middleware to multiple routes.

Closes #123
```

### Pull Request Guidelines

- **Clear title** - Summarize the change in 50 characters or less
- **Detailed description** - Explain what changed and why
- **Reference issues** - Link to related issues
- **Include tests** - Ensure adequate test coverage
- **Update docs** - Include relevant documentation updates

## Roadmap

### Current Version (1.0.x)
- [x] Core framework functionality
- [x] Basic middleware support
- [x] Testing infrastructure
- [x] Documentation

### Next Version (1.1.x)
- [ ] Enhanced middleware features
- [ ] Built-in caching support
- [ ] Performance optimizations
- [ ] Additional authentication methods

### Future Versions (2.0.x)
- [ ] Database abstraction layer
- [ ] Advanced routing features
- [ ] Plugin system
- [ ] CLI tools

## Questions?

If you have questions about contributing:

1. Check the [FAQ](docs/FAQ.md)
2. Search existing [Issues](https://github.com/username/apileon/issues)
3. Create a new issue with the `question` label
4. Join our community discussions

Thank you for contributing to Apileon! 🚀
