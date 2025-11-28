# Contributing to Laravel SOA Starter

Thank you for your interest in contributing to Laravel SOA Starter! We welcome contributions from the community and are pleased to have you join us.

## 🌟 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

Examples of behavior that contributes to creating a positive environment include:

- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

## 🚀 Getting Started

### Prerequisites

- PHP 8.3 or higher
- Composer
- Laravel 12.x knowledge
- Git
- Basic understanding of SOA patterns and modular architecture

### Development Setup

1. **Fork the repository**
   ```bash
   git clone https://github.com/yourusername/laravel-soa-starter.git
   cd laravel-soa-starter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Run tests to ensure everything works**
   ```bash
   vendor/bin/phpunit
   ```

## 📋 How to Contribute

### Reporting Issues

Before creating an issue, please:

1. **Check existing issues** to avoid duplicates
2. **Use clear, descriptive titles**
3. **Provide detailed steps to reproduce** the issue
4. **Include relevant system information** (PHP version, Laravel version, etc.)
5. **Add error messages and stack traces** if applicable

#### Issue Template
```markdown
**Bug Description:**
A clear description of what the bug is.

**Steps to Reproduce:**
1. Step one
2. Step two
3. Step three

**Expected Behavior:**
What should have happened.

**Actual Behavior:**
What actually happened.

**Environment:**
- PHP Version: 
- Laravel Version: 
- OS: 

**Additional Context:**
Any other relevant information.
```

### Suggesting Features

We welcome feature suggestions! Please:

1. **Check existing feature requests** first
2. **Clearly describe the feature** and its use case
3. **Explain why it would be valuable** to the project
4. **Consider backward compatibility** implications
5. **Provide examples** if possible

#### Feature Request Template
```markdown
**Feature Description:**
A clear description of the proposed feature.

**Use Case:**
Describe the problem this feature would solve.

**Proposed Solution:**
How you envision this feature working.

**Alternative Solutions:**
Any alternative approaches you've considered.

**Additional Context:**
Any other relevant information or examples.
```

### Code Contributions

#### 1. Choose What to Work On

- Check our [Issues](https://github.com/dafalagi/laravel-soa-starter/issues) for bug reports and feature requests
- Look for issues labeled `good first issue` or `help wanted`
- Review our [Roadmap](README.md#roadmap--future-plans) for planned features

#### 2. Development Workflow

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b bugfix/issue-description
   ```

2. **Make your changes**
   - Follow our [Coding Standards](#coding-standards)
   - Write tests for new features
   - Update documentation if needed

3. **Test your changes**
   ```bash
   vendor/bin/phpunit
   vendor/bin/phpunit --coverage-html coverage/
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add new feature description"
   ```

5. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request**
   - Use a clear, descriptive title
   - Reference related issues
   - Provide detailed description of changes
   - Include screenshots for UI changes

## 📏 Coding Standards

### PHP Standards

- **PSR-12**: Follow PSR-12 coding standards
- **Snake Case Variables**: Use `snake_case` for all variable names
- **Type Hints**: Use strict type hints wherever possible
- **DocBlocks**: Document all public methods and classes

```php
<?php

namespace Modules\Example\Services;

use App\Services\BaseService;
use Modules\Example\DTOs\ExampleRequestDTO;

class ExampleService extends BaseService
{
    /**
     * Process the example request.
     * 
     * @param ExampleRequestDTO $dto
     * @return array
     */
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }
    
    protected function process(mixed $dto): void
    {
        // Business logic here
        $example_data = $this->prepare($dto);
        
        // Process the data
        $result = $this->handle_example_logic($example_data);
        
        $this->results['data'] = $result;
        $this->results['message'] = 'Example processed successfully';
    }
    
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:examples'],
        ];
    }
}
```

### Architectural Guidelines

#### Service Layer
- **Single Responsibility**: Each service handles one specific action
- **Extend BaseService**: All services must extend `BaseService`
- **Validation in Services**: Move validation from controllers to services
- **Use DTOs**: Always use Data Transfer Objects for request/response

#### Modular Structure
- **Self-contained Modules**: Each module should be independent
- **Module-specific Database**: Keep migrations, factories, seeders in modules
- **Consistent Naming**: Follow the established naming conventions

#### Testing
- **Comprehensive Coverage**: Aim for high test coverage
- **Unit and Feature Tests**: Write both types of tests
- **Test Module Structure**: Organize tests per module
- **Mock External Services**: Use mocks for external dependencies

### Documentation Standards

- **Update README**: Update relevant sections for new features
- **Code Comments**: Comment complex business logic
- **API Documentation**: Document new API endpoints
- **Architecture Docs**: Update architectural documentation when needed

## 🧪 Testing Guidelines

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit modules/Auth/Tests/

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage/

# Run specific test
vendor/bin/phpunit --filter testMethodName
```

### Writing Tests

#### Test Structure
```php
<?php

namespace Modules\Example\Tests\Unit;

use Tests\TestCase;
use Modules\Example\Services\ExampleService;
use Modules\Example\DTOs\ExampleRequestDTO;

class ExampleServiceTest extends TestCase
{
    private ExampleService $example_service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->example_service = new ExampleService();
    }
    
    public function test_can_process_example_successfully(): void
    {
        // Arrange
        $dto = new ExampleRequestDTO(
            name: 'Test Name',
            email: 'test@example.com'
        );
        
        // Act
        $result = $this->example_service->execute($dto);
        
        // Assert
        $this->assertEquals(200, $result['status_code']);
        $this->assertNotNull($result['data']);
        $this->assertEquals('Example processed successfully', $result['message']);
    }
}
```

#### Test Requirements
- **Arrange-Act-Assert pattern**: Structure tests clearly
- **Descriptive test names**: Use `test_can_do_something_when_condition`
- **Test edge cases**: Include failure scenarios
- **Use factories**: Utilize model factories for test data

## 🔄 Pull Request Process

### PR Requirements

1. **Tests Pass**: All tests must pass
2. **Code Coverage**: Maintain or improve code coverage
3. **Documentation**: Update relevant documentation
4. **No Breaking Changes**: Unless explicitly planned
5. **Follow Standards**: Adhere to coding and architectural standards

### PR Template

```markdown
## Description
Brief description of the changes.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## How Has This Been Tested?
Describe the tests that you ran to verify your changes.

- [ ] Unit tests
- [ ] Feature tests
- [ ] Manual testing

## Checklist
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## Screenshots (if applicable)
Add screenshots to help explain your changes.

## Additional Notes
Any additional information or context about the changes.
```

### Review Process

1. **Automated Checks**: CI pipeline runs tests and checks
2. **Code Review**: Maintainers review code quality and architecture
3. **Discussion**: Address feedback and questions
4. **Approval**: Once approved, the PR will be merged

## 🏷️ Version and Release Process

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

#### Types
- `feat`: New features
- `fix`: Bug fixes
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

#### Examples
```bash
feat(auth): add JWT token refresh functionality
fix(user): resolve password validation issue
docs(readme): update installation instructions
refactor(services): improve error handling in BaseService
```

## 📞 Getting Help

- **Discussions**: Use [GitHub Discussions](https://github.com/dafalagi/laravel-soa-starter/discussions) for questions
- **Issues**: Report bugs and request features via [GitHub Issues](https://github.com/dafalagi/laravel-soa-starter/issues)
- **Email**: Contact maintainers directly for sensitive issues

## 🎉 Recognition

Contributors will be recognized in:

- **README.md**: Contributors section
- **CHANGELOG.md**: Release notes
- **GitHub**: Contributor graphs and statistics

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [SOA Principles](https://en.wikipedia.org/wiki/Service-oriented_architecture)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

Thank you for contributing to Laravel SOA Starter! Your efforts help make this project better for everyone. 🚀