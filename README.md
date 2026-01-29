# EcoRide Backend

This project is the **backend application of EcoRide**, a carpooling platform developed as part of the
**ECF (Évaluation des Compétences Finales)** for the _Graduate Développeur Angular 2023–2029_ program at **Studi**.

The backend is built with **Symfony 7.3** and provides a **REST API** for the frontend Angular application.
It supports authentication via **JWT**, multiple user roles, and communicates with both **MySQL** and **MongoDB**.

### 🚀 Quick Start (Local Installation)

Steps to run the project locally:

1. Clone the repository:
   `git clone https://github.com/MayBLG84/EcoRide-Back.git`
   `cd EcoRide-Back`

2. Install PHP dependencies:
   `composer install`

3. Configure environment variables (see Environment Configuration below)

4. Create and migrate the database:
   `php bin/console doctrine:database:create`
   `php bin/console doctrine:migrations:migrate`

5. (Optional) Load fixtures for development/test data:
   `php bin/console doctrine:fixtures:load`

6. Start the Symfony server:
   `symfony server:start`
   or
   `php -S localhost:8000 -t public`

7. Access the API:
   `http://localhost:8000/api`

### ⚙️ Environment Configuration

Environment variables are defined in _.env_ and overridden locally in _.env.local_. **Do not commit secrets**.

Example .env variables:

- `APP_ENV=dev` – environment
- `APP_SECRET=<secret_key>` – Symfony app secret
- `DATABASE_URL=mysql://user:password@127.0.0.1:3306/ecoride?serverVersion=8.0.32&charset=utf8mb4`
- `MONGODB_URI=mongodb://localhost:27017`
- `MONGODB_DB=symfony`
- `CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'`
- `MAILER_DSN=null://null` – configure for email sending
- `MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0`

⚠️ Never commit real secrets to the repository.

### 🔐 Authentication & Roles

Authentication is handled via **JWT** using `lexik/jwt-authentication-bundle`.

Roles:

- `ROLE_ADMIN` – administrators
- `ROLE_EMPLOYEE` – employees
- `ROLE_DRIVER` – drivers
- `ROLE_PASSENGER` – passengers

Endpoints can be restricted per role using Symfony security configuration (_security.yaml_) or `@IsGranted()` annotations.

Refresh tokens are supported for renewing JWTs without requiring login.

### 📁 Project Structure

- _src/_
    - _Controller/_ – API endpoints
    - _DataFixtures/_ – classes to load sample or test data into the database
    - _Entity/_ – Doctrine ORM entities
    - _Repository/_ – entity repositories
    - _Service/_ – business logic services
    - _DTO/_ – data transfer objects
    - _kernel.php_ – app kernel
- _templates/_ – not used (Angular frontend handles UI)
- _tests/_ – PHPUnit tests
- _translations/_ – Symfony translation files
- _doc/_ – documentation (gitignored)
- _bootstrap.php_ – test bootstrap

### 🧩 Architecture Overview

- REST API following **Symfony best practices**
- Uses **Doctrine ORM** for MySQL and **Doctrine MongoDB ODM** for MongoDB
- JWT-based stateless authentication
- Role-based access control
- Separation of concerns: Controllers → Services → Repositories

### 🔌 API Documentation

The API is documented via **Swagger** using `nelmio/api-doc-bundle`:

- Accessible at: _/api/docs_
- Supports interactive exploration of endpoints
- Shows required request parameters, responses, and authentication

### 🧪 Testing

Unit and functional tests are configured with **PHPUnit**.

Available commands:

- Run all tests: `./vendor/bin/phpunit`
- Run tests in verbose mode: `./vendor/bin/phpunit --verbose`
- Run a single test file: `./vendor/bin/phpunit tests/Controller/ExampleTest.php`

### 🏗️ Database & Fixtures

**Database:**

- MySQL for structured data
- MongoDB for unstructured data (e.g., profile pictures)

**Doctrine Migrations:**

- Generate migration: `php bin/console make:migration`
- Run migrations: `php bin/console doctrine:migrations:migrate`

**Fixtures:**

- Install `doctrine/doctrine-fixtures-bundle` if not already:
  `composer require --dev doctrine/doctrine-fixtures-bundle`
- Create fixture classes in _src/DataFixtures_
- Load fixtures: `php bin/console doctrine:fixtures:load`
- Fixtures can create fake users, rides, evaluations, bookings for development/testing.

### ⚠️ Common Issues

- **Port conflict:** Symfony server defaults to 8000, adjust if needed:
  `symfony server:start --port=8001`

- **API connection from frontend:** Ensure `CORS_ALLOW_ORIGIN` matches Angular dev URL (`http://localhost:4200`)

- **JWT errors:** Make sure `APP_SECRET` is set and JWT keys are generated:
  `php bin/console lexik:jwt:generate-keypair`

### 🎯 Functional Scope

The backend provides:

- CRUD for rides, users, bookings, evaluations
- Role-based access and secure endpoints
- JWT authentication with refresh tokens
- API documentation via Swagger
- Multi-database support (MySQL + MongoDB)
- Email sending for notifications (via symfony/mailer)
- Doctrine migrations and optional fixtures for dev/test

### 🛠️ Development Tools

- **Symfony CLI**
- **PHP 8.2+**
- **Composer**
- **XAMPP / MySQL**
- **MongoDB**
- **PHPUnit**
- **VS Code** (recommended)

### 💻 OS Notes

These instructions assume Linux (Ubuntu). For other operating systems:

- Node.js, npm, PHP, and Symfony CLI installation may vary.
- On Windows, use CMD/PowerShell and adjust paths accordingly.
- Ensure MySQL and MongoDB are installed and running. Port numbers may differ.
- Linux/macOS are case-sensitive for file paths.

### 🌍 Language

- API messages: **English**
- Technical documentation: **English**

### 👤 Academic Context

This backend application was developed as part of the _Graduate Développeur Angular 2023–2029_ program at **Studi**, within the scope of the ECF (final competency evaluation).
