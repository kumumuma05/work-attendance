<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class GetDateAndTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_correct_current_time_is_displayed()
    {
        Carbon::setTestNow(
            Carbon::create(2026, 1, 5, 9, 30)
        );

        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('2026年1月5日(月)');
        $response->assertSee('09:30');

        Carbon::setTestNow();
    }
}
