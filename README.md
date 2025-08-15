# WireKit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/apxcde/wirekit.svg?style=flat-square)](https://packagist.org/packages/apxcde/wirekit)
[![Tests](https://github.com/apxcde/wirekit/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/apxcde/wirekit/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/apxcde/wirekit.svg?style=flat-square)](https://packagist.org/packages/apxcde/wirekit)

> [!IMPORTANT]
> This is an opinionated starter kit created by [ApexCode](https://apexcode.dev) using Laravel, 
> Livewire, Folio, Livewire Volt and [FluxUI](https://fluxui.dev/). This was made to fit our needs.

> [!TIP]
> To get up and running quickly, use the new Laravel installer with the using option: 
> `laravel new myproject --using=apxcde/wirekit`

## Installation

// TODO::

## Architecture

### Core Technologies Stack
- **Laravel 12** - Main framework with PHP 8.2+
- **Livewire 3.6** - Frontend framework for reactive components
- **Livewire Volt 1.7** - Functional API for Livewire components
- **Laravel Folio 1.1** - Page-based routing system
- **FluxUI 2.2** - UI component library (proprietary)
- **Laravel Actions 2.9** - Single-purpose action classes
- **Tailwind CSS 4.0** - Utility-first CSS framework
- **Vite 6.2** - Frontend build tool

### Application Structure

**Page-Based Routing (Folio)**
- Pages are defined as Blade files in `resources/views/pages/`
- Routes are automatically generated from file structure
- Pages can contain inline Livewire Volt components
- Configured in `app/Providers/FolioServiceProvider.php`

**Livewire Volt Components**
- Functional components defined inline within Blade files
- Use `@volt('component-name')` directive
- Components extend `Livewire\Volt\Component`
- Mounted via `app/Providers/VoltServiceProvider.php`

**Laravel Actions Pattern**
- Business logic encapsulated in single-purpose Action classes
- Located in `app/Actions/` with subdirectories by domain
- Use `AsAction` trait from `lorisleiva/laravel-actions`
- Can function as commands, controllers, jobs, or listeners

**Authentication System**
- Magic link authentication (passwordless)
- OAuth integration (Google, GitHub)
- Custom auth routes in `routes/auth.php`
- User model in `app/Models/User.php`

## Development Commands

### Quick Start
```bash
# Start development environment (all services)
composer dev
# This runs: server, queue, logs, and vite concurrently
```

### Individual Development Services
```bash
# Start Laravel development server
php artisan serve

# Start queue worker
php artisan queue:listen --tries=1

# Start application logs
php artisan pail --timeout=0

# Start frontend development
npm run dev
```

### Building & Assets
```bash
# Build frontend assets for production
npm run build

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Testing & Quality
```bash
# Run all tests
composer test
# Equivalent to: php artisan config:clear && php artisan test

# Run tests with PHPUnit directly
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with filter
php artisan test --filter=test_example
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Fix code style issues
./vendor/bin/pint --dirty
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Run migrations with grace (no confirmation)
php artisan migrate --graceful

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback
```

### Kit-Specific Commands
```bash
# Activate Flux UI Pro (required for full functionality)
php artisan kit:activate-flux

# Initialize git repository
php artisan kit:initialize-git

# Clean up installation files
php artisan kit:clean-up

# Install the complete kit
php artisan kit:install
```

## Key Architectural Patterns

### Volt Component Structure
Volt components are defined inline within Blade files using this pattern:
```php
<?php
use Livewire\Volt\Component;
use function Laravel\Folio\{name, middleware};

new class extends Component {
    // Component logic here
};
?>

@volt('component-name')
    <!-- Component template here -->
@endvolt
```

### Action Classes
Actions follow this structure:
```php
use Lorisleiva\Actions\Concerns\AsAction;

class ExampleAction
{
    use AsAction;
    
    public function handle($parameters)
    {
        // Business logic here
    }
    
    // Optional: Use as command
    public function asCommand(Command $command) { }
    
    // Optional: Use as controller
    public function asController() { }
}
```

### Page Structure with Folio
Pages in `resources/views/pages/` automatically become routes:
- `pages/index.blade.php` → `/`
- `pages/about.blade.php` → `/about`
- `pages/dashboard/settings.blade.php` → `/dashboard/settings`

Use Folio functions for route configuration:
```php
<?php
use function Laravel\Folio\{name, middleware};

name('page.name');
middleware('auth');
?>
```

## FluxUI Integration

FluxUI is a proprietary component library requiring activation:
1. Run `php artisan kit:activate-flux`
2. Choose installation method (file copy or license key)
3. Components are used with `<flux:component>` syntax
4. Includes form inputs, modals, buttons, and layout components

## Authentication Flow

The application uses a modern authentication approach:
- **Magic Links**: Passwordless email-based login
- **OAuth**: Google and GitHub integration
- **Rate Limiting**: Protection against abuse
- **Auto-registration**: Users are created on first login attempt

## Environment Setup Notes

- Uses SQLite by default (`database/database.sqlite`)
- Requires PHP 8.2+
- Frontend assets require Node.js
- Mail configuration needed for magic links
- OAuth apps need to be configured for social login

## Testing Architecture

- **PHPUnit** configuration in `phpunit.xml`
- Tests use in-memory SQLite database
- **Feature tests** in `tests/Feature/`
- **Unit tests** in `tests/Unit/`
- Tests run with `composer test` script

## Common File Locations

- **Pages**: `resources/views/pages/`
- **Components**: `resources/views/components/`
- **Actions**: `app/Actions/`
- **Models**: `app/Models/`
- **Configurations**: `config/`
- **Routes**: `routes/` (web.php includes auth.php)
- **Migrations**: `database/migrations/`
- **Frontend**: `resources/css/`, `resources/js/`

## License

WireKit is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
