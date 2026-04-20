# Project Manager (Laravel 12)

A lightweight, scalable project management system built with Laravel 12.
Designed to manage tasks, teams, and workflows with simplicity and performance in mind.

---

## Features

* Project & Task Management
* User Authentication & Authorization
* Role-Based Access Control (RBAC)
* Clean and Modular Architecture
* RESTful API Ready
* Scalable for SaaS extension

---

## Tech Stack

* PHP 8.2
* Laravel 12
* MySQL
* Composer
* Blade / (optional: Vue/React if added later)

---

## Installation

```bash
# Clone the repository
git clone https://github.com/itsmrsarfaraz/project-manager.git

# Navigate into the project
cd project-manager

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env

# Run migrations
php artisan migrate

## Seed database
php artisan db:seed

# Start development servers
npm run dev
php artisan serve
```

---

## Environment Requirements

* PHP >= 8.2
* Composer >= 2.x
* MySQL / MariaDB
* Tailwind CSS

---

## Project Structure

* `app/` → Core application logic
* `routes/` → Route definitions
* `resources/` → Views & frontend assets
* `database/` → Migrations & seeders

---

## Roadmap

* [ ] API Authentication (Sanctum / Passport)
* [ ] Team Collaboration Features
* [ ] Notifications System
* [ ] SaaS Multi-Tenancy
* [ ] Admin Dashboard

---

## Contributing

Pull requests are welcome. For major changes, open an issue first to discuss what you’d like to change.

---

## License

This project is open-sourced under the MIT License.

---

## Author

Developed by Sarfaraz Shah
