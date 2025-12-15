<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with beautiful, realistic data.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // Step 1: Create Users
        $this->command->info('Step 1: Creating users...');
        $this->createUsers();
        $this->command->newLine();

        // Step 2: Create Expense Categories
        $this->command->info('Step 2: Creating expense categories...');
        $this->call(ExpenseCategorySeeder::class);
        $this->command->newLine();

        // Step 3: Create Incomes
        $this->command->info('Step 3: Creating incomes...');
        $this->call(IncomeSeeder::class);
        $this->command->newLine();

        // Step 4: Create Expenses
        $this->command->info('Step 4: Creating expenses...');
        $this->call(ExpenseSeeder::class);
        $this->command->newLine();

        // Step 5: Create Budgets
        $this->command->info('Step 5: Creating budgets...');
        $this->call(BudgetSeeder::class);
        $this->command->newLine();

        $this->command->info('Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('Test Accounts:');
        $this->command->info('   Admin: admin@expensetracker.com / password');
        $this->command->info('   User 1: john.doe@example.com / password');
        $this->command->info('   User 2: jane.smith@example.com / password');
        $this->command->newLine();
    }

    /**
     * Create realistic test users
     */
    private function createUsers(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@expensetracker.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('   Created admin user');

        // Create test users with realistic names
        $testUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
            ],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $this->command->info("   Created user: {$userData['name']}");
        }

        // Create additional random users (5-10)
        $randomUserCount = rand(5, 10);
        User::factory()->count($randomUserCount)->create();
        $this->command->info("   Created {$randomUserCount} random users");
    }
}
