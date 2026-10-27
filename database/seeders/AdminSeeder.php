<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = SuperAdmin::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('admin123'),
            ]
        );

        if ($this->command) {
            $this->command->info("Super Admin seeded successfully: {$superAdmin->email} / admin123");
        }
    }
}
