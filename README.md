# 🚀 Base Laravel - Professional Template

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

> **Laravel 13 template** for rapid development of robust administrative systems with focus on security, scalability and user experience.

## 📋 Table of Contents

- [About the Project](#-about-the-project)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API](#-api)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

## 🎯 About the Project

This is a **Laravel 13 template** developed to accelerate the development of robust administrative systems. The project incorporates best practices in development, security and architecture, being ideal for projects that require:

- **Advanced authentication system** with 2FA
- **Granular access control** (ACL)
- **Modern administrative interface**
- **Secure RESTful APIs**
- **Monitoring and analytics**
- **PWA (Progressive Web App)**

## ✨ Features

### 🔐 Security
- **Multi-factor Authentication** (Google 2FA)
- **Permission System** (Spatie Laravel Permission)
- **JWT Authentication** for APIs
- **Laravel Sanctum** for SPA
- **Azure AD SSO** - Optional Microsoft Entra ID single sign-on
- **Soft Deletes** in all models
- **Robust data validation**

### 🎨 Interface
- **AdminLTE 3** - Modern administrative interface
- **Bootstrap 5** - Responsive CSS framework
- **DataTables Server-side** - Optimized tables
- **PWA Ready** - Progressive Web App

### 📊 Monitoring
- **Laravel Debugbar** - Development debugging
- **Visitor Tracking** - Visitor analytics
- **Changelog System** - Change logging
- **Error Tracking** - Error monitoring

### 🚀 Performance
- **Laravel Vite** - Optimized build tool
- **Asset Minification** - Resource optimization
- **Redis Cache** - High-performance cache
- **Meilisearch** - Fast search

## 🛠️ Tech Stack

### Backend
- **Laravel 13** - PHP framework
- **PHP 8.3+** - Programming language
- **MySQL 8** - Database
- **Redis** - Cache and sessions
- **Docker** - Containerization

### Frontend
- **Bootstrap 5** - CSS framework
- **AdminLTE 3** - Administrative template
- **Vite** - Build tool
- **SASS** - CSS preprocessor

### Development Tools
- **Laravel Sail** - Docker environment
- **Laravel Pint** - Code style fixer
- **PEST** - Testing framework
- **Laravel Debugbar** - Debug toolbar

## 🚀 Installation

### Prerequisites
- **Docker** and **Docker Compose**
- **Node.js** 18+ (for local development)
- **Composer** (for local development)

### Step by Step

1. **Clone the repository**
```bash
git clone <repository-url>
cd base-laravel
```

2. **Prepare the environment**
```bash
cp .env.example .env
```

3. **Configure environment variables**
```bash
# Edit the .env file with your settings
nano .env
```

4. **Install dependencies**
```bash
composer install
npm install
```

5. **Configure Laravel**
```bash
php artisan key:generate
php artisan jwt:secret
```

6. **Configure Docker (optional)**
```bash
# Alias for Laravel Sail
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

7. **Run migrations**
```bash
# With Docker
sail artisan migrate --seed

# Without Docker
php artisan migrate --seed
```

8. **Configure storage**
```bash
# With Docker
sail artisan storage:link

# Without Docker
php artisan storage:link
```

9. **Compile assets**
```bash
# Development
npm run dev

# Production
npm run build
```

## ⚙️ Configuration

### Important Environment Variables

```env
# Application
APP_NAME="Base Laravel"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=base_laravel
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your-jwt-secret
JWT_TTL=60

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Docker Compose

The project includes complete Docker configuration with:

- **Laravel Sail** - Main container
- **MySQL 8** - Database
- **Redis** - Cache and sessions
- **Meilisearch** - Search
- **Mailpit** - Email testing
- **Selenium** - Automated tests

## 🎮 Usage

### System Access

**Default credentials:**
- **Email:** programador@base.com
- **Password:** 12345678

### Useful Commands

```bash
# Development
sail up -d                    # Start containers
sail artisan serve            # Development server
npm run dev                   # Compile assets (dev)

# Production
npm run build                 # Compile assets (prod)
php artisan config:cache      # Configuration cache
php artisan route:cache       # Route cache

# Maintenance
sail artisan migrate          # Run migrations
sail artisan db:seed          # Run seeders
sail artisan storage:link     # Storage symbolic link
```

## 🔌 API

### JWT Authentication

```bash
# Login
POST /api/v1/login
{
    "email": "user@example.com",
    "password": "password"
}

# Register
POST /api/v1/register
{
    "name": "User Name",
    "email": "user@example.com",
    "password": "password"
}

# Refresh Token
POST /api/v1/refresh
Authorization: Bearer {token}
```

### Available Endpoints

- `GET /api/v1/admin/users` - List users
- `GET /api/v1/admin/profile` - User profile
- `POST /api/v1/logout` - Logout

## 🔑 Azure AD SSO (Optional)

The template supports optional Single Sign-On via **Microsoft Entra ID (Azure AD)**. When configured, a "Sign in with Microsoft" button appears on the login page alongside the standard form. The local user base remains the authority for authorization — SSO is used for authentication only.

### How it works

1. User clicks "Entrar com Microsoft (SSO)"
2. Azure AD authenticates the user and returns to the callback
3. The application looks up the local user by e-mail — **no new users are created**
4. Roles and permissions continue to be managed locally via Spatie Permission

### Configuration

Add the following variables to your `.env`:

```env
# Azure AD SSO (leave blank to disable the SSO button)
AZURE_CLIENT_ID=your-application-client-id
AZURE_CLIENT_SECRET=your-client-secret-value
AZURE_TENANT_ID=common                          # or the specific tenant ID to restrict to one organization
AZURE_REDIRECT_URI=https://yourdomain.com/auth/azure/callback
```

| `AZURE_TENANT_ID` value | Who can authenticate |
|---|---|
| `common` | Any Microsoft account (personal + organizational) |
| `consumers` | Personal accounts only (hotmail, outlook, live) |
| `organizations` | Organizational accounts only |
| `<tenant-id>` | Only users from that specific Azure AD directory |

### Azure Portal setup

1. Go to **Microsoft Entra ID → App registrations → New registration**
2. Set the redirect URI (**Web**): `https://yourdomain.com/auth/azure/callback`
3. Under **Supported account types**, choose the appropriate option for your use case
4. Go to **Certificates & secrets → New client secret** and copy the generated **Value**
5. Collect the **Application (client) ID** and **Directory (tenant) ID** from the Overview page

> **Note:** The SSO button is only rendered when `AZURE_CLIENT_ID` is set. Leaving it blank disables SSO without any code changes.

## 📁 Project Structure

### Main Directories

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Administrative controllers
│   │   │   └── ACL/         # Access control
│   │   ├── Api/             # REST APIs
│   │   └── Auth/            # Authentication
│   ├── Models/              # Eloquent models
│   └── Providers/           # Service providers
├── resources/
│   ├── views/
│   │   ├── admin/           # Administrative views
│   │   ├── auth/            # Authentication views
│   │   └── layouts/         # Base layouts
│   ├── js/                  # JavaScript
│   └── sass/                # SASS styles
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
└── database/
    ├── migrations/           # Migrations
    └── seeders/             # Seeders
```

## 🤝 Contributing

### Code Standards

- **PSR-12** - PHP coding standard
- **Laravel Pint** - Code style fixer
- **PEST** - Testing framework
- **Conventional Commits** - Commit standard

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - PHP framework
- [AdminLTE](https://adminlte.io) - Administrative template
- [Spatie](https://spatie.be) - Laravel packages

---

**Developed with ❤️ to accelerate the development of robust and secure systems.**
