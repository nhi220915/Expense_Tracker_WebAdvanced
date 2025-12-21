# Expense Tracker: Smart Personal Finance Management

## Student Information

**Student Name:** [Tran Bao Nhi]  
**Student ID:** [22090015]  
**Course:** Advanced Web Development 
**Project:** Final Project - Expense Tracker

---

## Overview

**Expense Tracker** is a comprehensive personal finance management application that helps users track their income, expenses, budgets, and financial goals. Built with modern web technologies, this application provides an intuitive interface for managing your finances with real-time insights and automated notifications.

The application offers features such as:
- 📊 **Dashboard** with financial overview and analytics
- 💰 **Income Management** to track all revenue sources
- 💸 **Expense Tracking** categorized by type
- 🎯 **Budget Planning** with automated alerts when approaching limits
- 📧 **Email Notifications** for budget warnings and reminders
- 📈 **Real-time Analytics** with visual charts and reports

---

## Screenshots & Demo

### Dashboard Overview
*<img width="2145" height="1156" alt="image" src="https://github.com/user-attachments/assets/407512b4-c4d7-43b0-8278-187d87ccabd5" />
*

### Income Management
*<img width="2145" height="865" alt="image" src="https://github.com/user-attachments/assets/422ec0d7-8631-41e6-8fae-c70898ee1edc" />
]*

### Expense Tracking
*<img width="2146" height="1166" alt="image" src="https://github.com/user-attachments/assets/60026a43-99c1-44d9-a7ae-37afd7b2cf0c" />
*

### Budget Planning
*<img width="477" height="700" alt="image" src="https://github.com/user-attachments/assets/61ea78eb-42c6-4882-99a1-0c1313a922a3" />
*

*<img width="2170" height="1160" alt="image" src="https://github.com/user-attachments/assets/ce7e8f00-42a2-4c49-9891-9f78e9dce57a" />

*

*<img width="2176" height="1166" alt="image" src="https://github.com/user-attachments/assets/86d2e909-6815-43d9-804f-fd63f7542292" />
>
*

---

## Technologies

### Backend

- **Laravel 12**: A robust PHP framework for building scalable, secure, and high-performance web applications, offering tools for routing, database migrations, and templating.
- **Eloquent ORM**: Laravel's built-in Object-Relational Mapping (ORM) that provides an elegant and intuitive way to interact with the database, supporting relationships, query building, and model management.
- **Sanctum**: A Laravel package for managing API authentication and single-page application (SPA) tokens with simplicity and security.
- **Pulse**: A Laravel tool for monitoring application health and performance, providing insights into metrics, errors, and system statuses.
- **Telescope**: A debugging and monitoring tool for Laravel applications, providing insights into requests, jobs, exceptions, logs, and more, tailored for developers to enhance productivity.
- **Sentry**: A real-time error tracking and monitoring tool that helps developers identify and fix issues in applications, integrated seamlessly with Laravel.
- **Scramble**: A Laravel utility for generating API documentation dynamically, helping developers create clear and structured documentation directly from their codebase.
- **Laravel Breeze**: Minimal, simple authentication scaffolding for Laravel applications.
- **Laravel Queue**: Asynchronous job processing for email notifications and background tasks.

### Frontend

- **Blade Templates**: Laravel's powerful templating engine for creating dynamic web pages.
- **Alpine.js**: A lightweight JavaScript framework for composing behavior directly in markup.
- **Vite**: Next-generation frontend tooling for fast development and optimized builds.
- **Tailwind CSS**: A utility-first CSS framework for rapid UI development with modern, responsive designs.
- **Axios**: A JavaScript library for making HTTP requests, offering a simple API with support for Promises, JSON data handling, request cancellation, and easy configuration.

### Database

- **MySQL**: Lightweight, serverless database perfect for development and small-to-medium scale applications.

### Development Tools

- **Laravel Pail**: Real-time log viewer for Laravel applications.
- **Laravel Sail**: A lightweight command-line interface for interacting with Docker (optional Docker setup available).
- **Composer**: PHP dependency management.
- **NPM**: JavaScript package management.
- **Laravel Pint**: Code style fixer for Laravel applications.
- **PHPUnit**: Testing framework for PHP applications.

---

## Features

### User Management
- User registration and authentication
- Profile management
- Session-based security

### Income Management
- Add, edit, and delete income entries
- Categorize income sources
- Track income over time
- View income history and analytics

### Expense Management
- Add, edit, and delete expenses
- Categorize expenses with predefined categories
- Track spending patterns
- Visual expense analytics

### Budget Planning
- Create and manage budgets by category
- Set budget limits and timeframes
- Automated budget warnings via email
- Real-time budget utilization tracking
- Visual budget vs. actual spending comparison

### Notifications & Alerts
- Asynchronous email notifications
- Budget warning emails when spending reaches 80% of limit
- Task reminders (if applicable)
- Queue-based email processing for performance

### Analytics & Reporting
- Dashboard with financial overview
- Income vs. Expense charts
- Budget utilization metrics
- Category-wise spending breakdown
- Monthly/yearly financial summaries

### Monitoring & Debugging
- Laravel Telescope for request monitoring
- Laravel Pulse for application health metrics
- Sentry for error tracking and alerts
- Real-time log viewing with Pail

---

## Prerequisites

Before running this project, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** or **Yarn**
- **Git**
- **SQLite** (included with PHP)
- **Docker** (optional, for Laravel Sail setup)

---

## Installation Instructions

### 1. Clone the Repository

```bash
git clone [your-repository-url]
cd expense-tracker
```

### 2. Install Backend Dependencies

```bash
composer install
```

### 3. Environment Configuration

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Edit the `.env` file and configure your application settings:
- Set `APP_NAME` to "Expense Tracker"
- Configure database connection (SQLite is default)
- Set mail configuration for email notifications (optional for development)

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate --seed
```

This will create all necessary tables and seed the database with sample data.

### 6. Install Frontend Dependencies

```bash
npm install
```

### 7. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 8. Start the Development Server

You have two options:

#### Option A: Run services individually

Open three separate terminal windows:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work database --queue=emails --verbose
```

**Terminal 3 - Frontend Assets:**
```bash
npm run dev
```

#### Option B: Run all services concurrently (Recommended)

```bash
composer run dev
```

This command will start:
- Laravel development server (http://localhost:8000)
- Queue worker for email processing
- Real-time log viewer (Laravel Pail)
- Vite development server for hot module replacement

### 9. Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

---

## Project Structure

```
expense-tracker/
├── app/
│   ├── Console/Commands/    # Artisan commands
│   ├── Http/
│   │   ├── Controllers/     # Application controllers
│   │   ├── Middleware/      # HTTP middleware
│   │   └── Resources/       # API resources
│   ├── Jobs/                # Queue jobs (email notifications)
│   ├── Mail/                # Email templates
│   └── Models/              # Eloquent models
├── database/
│   ├── factories/           # Model factories for testing
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── public/                  # Public assets
├── resources/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── views/               # Blade templates
├── routes/
│   ├── api.php              # API routes
│   ├── web.php              # Web routes
│   └── console.php          # Console routes
├── tests/                   # Automated tests
├── .env.example             # Environment configuration template
├── composer.json            # PHP dependencies
├── package.json             # JavaScript dependencies
├── tailwind.config.js       # Tailwind CSS configuration
└── vite.config.js           # Vite configuration
```

---

## Key Features Implementation

### Asynchronous Email Processing

The application uses Laravel Queue to process emails asynchronously, ensuring the application remains responsive:

- Budget warning emails are sent when spending reaches 80% of the budget limit
- Emails are queued and processed in the background
- Queue workers handle email delivery without blocking user interactions

### Budget Management with Automated Alerts

- Users can create budgets for specific expense categories
- System monitors spending in real-time
- Automated warnings via email when budget utilization reaches 80%
- Visual indicators on dashboard showing budget health

### Real-time Monitoring

- **Laravel Telescope**: Monitor all requests, queries, jobs, and exceptions
- **Laravel Pulse**: Track application performance and health metrics
- **Sentry**: Real-time error tracking and alerting
- **Laravel Pail**: Live log streaming in the terminal

---

## API Documentation

API documentation is automatically generated using Laravel Scramble and can be accessed at:

```
http://localhost:8000/docs/api
```

---

## Testing

Run the test suite:

```bash
composer run test
```

Or manually:

```bash
php artisan test
```

---

## Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure your production database
4. Set up proper mail configuration
5. Build frontend assets: `npm run build`
6. Run migrations: `php artisan migrate --force`
7. Optimize application: 
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
8. Set up a queue worker service (Supervisor recommended)
9. Configure a proper web server (Nginx/Apache)

---

## Troubleshooting

### Queue not processing emails?
Make sure the queue worker is running:
```bash
php artisan queue:work database --queue=emails --verbose
```

### Database errors?
Reset and reseed the database:
```bash
php artisan migrate:fresh --seed
```

### Frontend assets not loading?
Rebuild the assets:
```bash
npm run build
```

## Contributing

This is an academic project for Advanced Web Development course. Contributions, suggestions, and feedback are welcome!

---

## License

This project is developed for educational purposes as part of the Advanced Web Development course at VNUK Institute.

---

## Acknowledgments

- Laravel Framework and its amazing ecosystem
- Tailwind CSS for beautiful, responsive design
- The open-source community for incredible tools and libraries
- VNUK Institute faculty for guidance and support

---

## Contact

**Student:** [Tran Bao Nhi - 22090015]  
**Email:** [nhi.tran220915@vnuk.edu.vn]  
**GitHub:** [[your-github-username](https://github.com/nhi220915/Expense_Tracker_WebAdvanced)]

---

