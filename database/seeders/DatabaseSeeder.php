<?php

use App\Models\User;

class DatabaseSeeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }

    protected function call(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            (new $seeder())->run();
        }
    }
}

class UserSeeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@apileon.com',
            'password' => 'password123',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ]);

        // Create test users
        $testUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'email_verified_at' => null,
                'status' => 'active'
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => 'password123',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'inactive'
            ],
            [
                'name' => 'Alice Wilson',
                'email' => 'alice@example.com',
                'password' => 'password123',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ],
            [
                'name' => 'Charlie Brown',
                'email' => 'charlie@example.com',
                'password' => 'password123',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'suspended'
            ]
        ];

        foreach ($testUsers as $userData) {
            User::create($userData);
        }

        echo "Created " . (count($testUsers) + 1) . " users\n";
    }
}
