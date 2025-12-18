<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween(
            now()->subMonths(3)->startOfMonth(),
            now()->addMonths(3)->endOfMonth()
        )->format('Y-m-d');

        $clockIn = Carbon::parse("$date 08:00")
            ->addMinutes($this->faker->numberBetween(0,180));
        $clockOut = $clockIn->copy()->addHours(
            $this->faker->numberBetween(7,12)
        );

        return [
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($attendance) {

            // 休憩をランダムに作る
            $breakCount = $this->faker->numberBetween(0, 2);

            for ($i = 0; $i < $breakCount; $i++) {

                // 出勤時間から1〜3時間後に休憩開始
                $breakIn = $attendance->clock_in
                    ->copy()
                    ->addHours($this->faker->numberBetween(1, 3));

                // 休憩終了は30〜90分後
                $breakOut = $breakIn
                    ->copy()
                    ->addMinutes($this->faker->numberBetween(30, 90));

                // 退勤を超えない
                if ($breakOut > $attendance->clock_out) {
                    $breakOut = $attendance->clock_out->copy();
                }

                // breakレコード作成
                $attendance->breaks()->create([
                    'break_in'  => $breakIn,
                    'break_out' => $breakOut,
                ]);
            }
        });
    }

}
