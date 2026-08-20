<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['username' => 'dev_bruce'],
            [
                'email' => 'bruceknight31@gmail.com',
                'full_name' => 'admin',
                'password_hash' => Hash::make('Malakai 4:5'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

    }
}
