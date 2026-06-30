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
            ['username' => 'admin'],
            [
                'email' => 'bruceknight31@gmail.com',
                'full_name' => 'admin',
                'password_hash' => Hash::make('admin1234'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        AdminUser::updateOrCreate(
            ['username' => 'moderator'],
            [
                'email' => 'moderator@ninasdata.com',
                'full_name' => 'Moderator',
                'password_hash' => Hash::make('password'),
                'role' => 'moderator',
                'is_active' => true,
            ]
        );
    }
}
