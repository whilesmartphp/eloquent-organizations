# Whilesmart Eloquent Organizations

Organization management package for Laravel applications with role-based access control and workspace integration.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/whilesmart/eloquent-organizations.svg?style=flat-square)](https://packagist.org/packages/whilesmart/eloquent-organizations)

## Features

- **Organization Management**: Complete CRUD operations for organizations
- **Role Integration**: Built-in support for role-based access control
- **Workspace Scoping**: Optional workspace-level organization isolation
- **Polymorphic Ownership**: Organizations can be owned by any model (User, Team, etc.)
- **API Ready**: Pre-built RESTful API endpoints
- **Laravel Auto-Discovery**: Automatically registers service provider
- **Flexible Configuration**: Customizable settings and behavior

## Installation

Install the package via Composer:

```bash
composer require whilesmart/eloquent-organizations
```

The package will automatically register its service provider.

### Publish Configuration

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag="organizations-config"
```

### Publish Routes

To customize the API routes:

```bash
php artisan vendor:publish --tag="organizations-routes"
```

## Usage

### Organization Model

The package provides a complete `Organization` model with the following attributes:

- **name**: Organization name
- **slug**: URL-friendly identifier (auto-generated)
- **type**: Organization type (company, nonprofit, etc.)
- **email**: Contact email
- **phone**: Contact phone number
- **address**: Physical address
- **website**: Website URL
- **description**: Organization description
- **owner**: Polymorphic relationship to owning model
- **is_active**: Active status

### API Endpoints

The package automatically registers these routes under `/api/organizations`:

```
GET    /api/organizations              # List organizations
POST   /api/organizations              # Create organization
GET    /api/organizations/{id}         # Show organization
PUT    /api/organizations/{id}         # Update organization
DELETE /api/organizations/{id}         # Delete organization
```

### Workspace Integration

When workspace scoping is enabled, organizations are automatically filtered by workspace:

```
GET    /api/workspaces/{id}/organizations    # Workspace-scoped organizations
POST   /api/workspaces/{id}/organizations    # Create in workspace
```

### Using in Your Models

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Whilesmart\Organizations\Models\Organization;

class Project extends Model
{
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
```

### Creating Organizations

```php
use Whilesmart\Organizations\Models\Organization;

$organization = Organization::create([
    'name' => 'Acme Corporation',
    'type' => 'company',
    'email' => 'contact@acme.com',
    'website' => 'https://acme.com',
    'owner_type' => 'App\Models\User',
    'owner_id' => $user->id,
    'is_active' => true,
]);
```

### Role-Based Access Control

The package integrates with `whilesmart/eloquent-roles` for permission management:

```php
// Check if user can manage organizations
$user->hasPermission('manage-organizations');

// Check within specific workspace context
$user->hasPermission('manage-organizations', 'workspace', $workspaceId);
```

## Configuration

The default configuration includes:

```php
return [
    'workspace_scoped' => true,  // Enable workspace-level scoping
    'require_owner' => true,     // Organizations must have an owner
    'auto_slug' => true,         // Auto-generate slugs from names
];
```

## Requirements

- PHP ^8.2
- Laravel ^11.0|^12.0
- whilesmart/eloquent-roles ^1.0

## Suggested Packages

- **whilesmart/eloquent-workspaces**: For workspace integration support

## License

The MIT License (MIT).

