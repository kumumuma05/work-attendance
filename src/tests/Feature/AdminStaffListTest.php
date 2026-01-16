<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_view_all_users_name_and_email_addresses() {
        // 準備
        $users = [
            ['name' => 'user1', 'email' => 'user1@test.com'],
            ['name' => 'user2', 'email' => 'user2@test.com'],
            ['name' => 'user3', 'email' => 'user3@test.com'],
        ];
        foreach ($users as $user) {
            User::factory()->create($user);
        }

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // スタッフ一覧を開く
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('user1');
        $response->assertSee('user1@test.com');
        $response->assertSee('user2');
        $response->assertSee('user2@test.com');
        $response->assertSee('user3');
        $response->assertSee('user3@test.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示されることを確認
     */
    public function test_admin_sees_users_attendance_information_correctly() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response->assertSee('user1さんの勤怠');
        $response->assertSee('01/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
