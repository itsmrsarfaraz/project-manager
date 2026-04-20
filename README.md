# Project Manager

A full-stack Laravel project management system with role-based access control,
real-time updates, and a REST API.

## Features

- **Multi-user projects** with owner/manager/member roles
- **Task management** with priority, status, due dates, labels, attachments
- **Comments** on tasks (polymorphic)
- **File attachments** on tasks
- **Real-time status updates** via Laravel Echo + Pusher
- **Email notifications** for task assignment and project invitations
- **Activity log** with Observer pattern
- **REST API v1** with Sanctum token authentication
- **Role-based authorization** via Policies and custom Middleware

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Blade, Tailwind CSS
- **Database**: MySQL
- **Queue**: Redis + Laravel Horizon
- **Real-time**: Pusher + Laravel Echo
- **Auth**: Laravel Breeze (web) + Sanctum (API)

## Architecture

Controller → Service → Action → Model
↓
fires Event
↓
Listener (queued)

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Redis (for production queues)

## Setup

```bash
# 1. Clone and install dependencies
git clone <repo>
cd project-manager
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Set up database
# Create a MySQL database named 'project_manager'
# Update DB_* values in .env

# 4. Run migrations and seed
php artisan migrate:fresh --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Build assets
npm run dev

# 7. Start servers (3 terminals)
php artisan serve        # Web server
php artisan horizon      # Queue worker
npm run dev             # Asset watcher
```

## Development Accounts (after seeding)

| Email | Password | Notes |
|-------|----------|-------|
| alice@example.com | password | Main dev account |
| bob@example.com | password | Secondary account |
| carol@example.com | password | Third account |

## API Usage

```bash
# Get token
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password","device_name":"my-client"}'

# Use token
curl http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Running Tests

```bash
php artisan test              # Run all tests
php artisan test --filter=Api # Run specific test class
```

## Developer Tools

- Telescope: `http://localhost:8000/telescope`
- Horizon:   `http://localhost:8000/horizon`

