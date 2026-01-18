<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

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
        $this->call(AdminSeeder::class);

        // 固定テストユーザー
        $testUser = User::create([
            'name' => 'テスト太郎',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);
        $testUser->notify(new VerifyEmail());

        // ランダム一般ユーザー
        $randomUsers = User::factory(5)->unverified()->create();
        $randomUsers->each(function ($user) {
            $user->notify(new VerifyEmail());
        });

        $users = $randomUsers->prepend($testUser);

        $this->seedAttendances($users, [-1, 0, 1], 24);
    }

    /**
     * １ユーザー１日１件の勤怠を作成する制限
     */
    private function seedAttendances($users, array $monthOffsets, int $daysPerMonth): void
    {
        foreach ($users as $user) {
            foreach ($monthOffsets as $offset) {
                $monthStart = Carbon::today()->startOfMonth()->addMonths($offset);

                $createdCount = 0;

                for ($i = 0; $i < 31; $i++) {
                    $date = $monthStart->copy()->addDays($i);
                    // 月をまたいだら終了（2月対策）
                    if ($date->month !== $monthStart->month) {
                        break;
                    }
                    // 土日は基本スキップ（土曜は30%の確率で出勤）
                    if ($date->isWeekend()) {
                        if ($date->isSaturday() && rand(1, 100) <= 30) {
                            // 何も入れない
                        } else {
                            continue;
                        }
                    }

                    // 24日分作成したら終了
                    if ($createdCount >= $daysPerMonth) {
                        break;
                    }

                    Attendance::factory()
                        ->forDate($date)
                        ->create([
                            'user_id' => $user->id,
                        ]);
                }
            }
        }
    }
}