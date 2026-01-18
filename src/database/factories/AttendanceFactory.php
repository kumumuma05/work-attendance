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

    private function makeClockTimes(Carbon $date): array
    {
        $clockIn  = $date->copy()->setTime(8, 0)->addMinutes($this->faker->numberBetween(0, 180));
        $clockOut = $clockIn->copy()->addHours($this->faker->numberBetween(7, 12))->addMinutes($this->faker->numberBetween(0, 59));;

        return ['clock_in' => $clockIn, 'clock_out' => $clockOut];
    }

    public function definition()
    {
        return $this->makeClockTimes(Carbon::today());
    }

    public function forDate(Carbon | string $date)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $this->state(function () use ($date) {
            return $this->makeClockTimes($date);
        });
    }

    public function configure()
    {
        return $this->afterCreating(function ($attendance) {

            // 休憩をランダムに作る
            $breakCount = $this->faker->numberBetween(1, 2);
            $workStart = $attendance->clock_in->copy();
            $workEnd = $attendance->clock_out->copy();

            $cursor = $workStart->copy()->addHours($this->faker->numberBetween(1, 3));

            for ($i = 0; $i < $breakCount; $i++) {

                // 休憩開始が退勤以降なら作れない
                if ($cursor->greaterThanOrEqualTo($workEnd)) {
                    break;
                }

                $breakIn = $cursor->copy();

                // 休憩終了は30〜90分後（ただし退勤を超えない）
                $breakOut = $breakIn->copy()->addMinutes($this->faker->numberBetween(30, 90));
                if ($breakOut->greaterThan($workEnd)) {
                    $breakOut = $workEnd->copy();
                }

                // 0分休憩は作らない
                if ($breakOut->lessThanOrEqualTo($breakIn)) {
                    break;
                }

                // breakレコード作成
                $attendance->breaks()->create([
                    'break_in'  => $breakIn,
                    'break_out' => $breakOut,
                ]);

                // 次の休憩は、1～2時間後
                $cursor = $breakOut->copy()->addHours($this->faker->numberBetween(1, 2));
            }
        });
    }
}
