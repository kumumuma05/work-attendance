<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 管理者
        $this->call(          AdminUserSeeder::class);

        // 固定テストユーザー
        $testUser = User::create([
            'name' => 'user1',
            'email' => 'user1@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
        Attendance::factory(30)->create([
            'user_id' => $testUser->id,
            'email_verified_at' => now(),
        ]);

        // ランダム一般ユーザー
        $users = User::factory(15)->create();

        foreach ($users as $user) {
            Attendance::factory(30)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
