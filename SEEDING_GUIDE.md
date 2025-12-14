# Database Seeding Guide

This project includes a comprehensive seeding system that creates beautiful, realistic test data for easy development and testing.

## 🎯 Overview

The seeding system uses Laravel's **Model**, **Factory**, and **Seeder** classes to generate realistic financial data including:

-   Multiple test users
-   Expense categories with proper colors
-   Realistic expenses spread across 6 months
-   Monthly income entries (salary, freelance, bonuses)
-   Budget allocations that make sense

## 📦 What's Included

### Factories

-   `UserFactory` - Creates users with verified emails
-   `ExpenseCategoryFactory` - Creates expense categories with colors
-   `ExpenseFactory` - Creates realistic expenses with category-appropriate notes and amounts
-   `IncomeFactory` - Creates income entries (salary, freelance, bonuses, etc.)
-   `BudgetFactory` - Creates budget limits based on category types

### Seeders

-   `DatabaseSeeder` - Main seeder that orchestrates everything
-   `ExpenseCategorySeeder` - Seeds 10 default categories per user
-   `ExpenseSeeder` - Seeds 15-30 expenses per month per user (last 6 months)
-   `IncomeSeeder` - Seeds monthly salary + occasional bonuses/freelance
-   `BudgetSeeder` - Seeds budgets for current and previous 2 months

## 🚀 Usage

### Run All Seeders

```bash
php artisan db:seed
```

### Run Specific Seeder

```bash
php artisan db:seed --class=ExpenseSeeder
php artisan db:seed --class=IncomeSeeder
php artisan db:seed --class=BudgetSeeder
```

### Fresh Migration + Seeding

```bash
php artisan migrate:fresh --seed
```

## 👥 Test Accounts

After seeding, you can log in with these accounts:

| Email                       | Password | Role  |
| --------------------------- | -------- | ----- |
| admin@expensetracker.com    | password | Admin |
| john.doe@example.com        | password | User  |
| jane.smith@example.com      | password | User  |
| michael.johnson@example.com | password | User  |
| sarah.williams@example.com  | password | User  |

Plus 5-10 additional random users.

## 📊 Data Characteristics

### Expense Categories

-   **Food** - $5-$150 per transaction
-   **Transport** - $2-$100 per transaction
-   **Entertainment** - $10-$200 per transaction
-   **Utilities** - $30-$500 per transaction
-   **Shopping** - $20-$1000 per transaction
-   **Healthcare** - $15-$500 per transaction
-   **Education** - $50-$2000 per transaction
-   **Travel** - $100-$5000 per transaction
-   **Bills** - $50-$2000 per transaction
-   **Other** - $5-$500 per transaction

### Income Types

-   **Salary** - $2,500-$8,000 (monthly, at start of month)
-   **Freelance** - $200-$5,000 (occasional)
-   **Bonus** - $500-$5,000 (occasional)
-   **Investment** - $50-$2,000 (occasional)

### Budget Allocations

Budgets are allocated based on realistic percentages:

-   Food: 25%
-   Transport: 15%
-   Shopping: 15%
-   Utilities: 12%
-   Entertainment: 10%
-   Healthcare: 8%
-   Bills: 5%
-   Education: 5%
-   Travel: 3%
-   Other: 2%

Total monthly budget per user: $2,000-$5,000

## 🎨 Realistic Features

1. **Category-Appropriate Notes**: Expenses have realistic notes (e.g., "Highland Coffee" for Food, "Uber ride" for Transport)

2. **Realistic Amounts**: Each category has appropriate price ranges

3. **Time Distribution**: Expenses are spread across the last 6 months

4. **Salary Timing**: Salary payments typically arrive at the start of each month

5. **Budget Alignment**: Budgets are created for categories that exist

6. **Relationship Integrity**: All foreign keys are properly maintained

## 🔧 Customization

### Adjust Number of Users

Edit `DatabaseSeeder.php`:

```php
$randomUserCount = rand(5, 10); // Change this
```

### Adjust Expense Count

Edit `ExpenseSeeder.php`:

```php
$expensesPerMonth = rand(15, 30); // Change this
$expensesPerCategory = rand(2, 8); // Change this
```

### Adjust Time Range

Edit seeders to change `$monthsToSeed`:

```php
$monthsToSeed = 6; // Change to desired number of months
```

## ✅ Verification

After seeding, verify data with:

```bash
php artisan tinker
```

Then run:

```php
User::count()
ExpenseCategory::count()
Expense::count()
Income::count()
Budget::count()
```

## 📝 Notes

-   All passwords are set to: `password`
-   All users have verified emails
-   Data is generated using Faker for realistic variety
-   Budgets ensure 100% allocation across all categories
-   Expenses are linked to proper categories and users
