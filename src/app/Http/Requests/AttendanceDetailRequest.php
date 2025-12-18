<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_clock_in' => 'required|date_format:H:i',
            'requested_clock_out' => 'required|date_format:H:i',
            'remarks' => 'required',
            'requested_breaks' => 'nullable|array',
            'requested_breaks.*.break_in'      => 'nullable|date_format:H:i',
            'requested_breaks.*.break_out'     => 'nullable|date_format:H:i',
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages()
    {
        return [
            'requested_clock_in.required' => '出勤時間が入力されていません',
            'requested_clock_in.date_format' => '00:00の形式で入力してください',
            'requested_clock_out.required' => '退勤時間が入力されていません',
            'requested_clock_out.date_format' => '00:00の形式で入力してください',
            'remarks.required' => '備考を記入してください',
            'requested_breaks.*.break_in.date_format' => '00:00の形式で入力してください',
            'requested_breaks.*.break_out.date_format' => '00:00の形式で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $attendance = Attendance::findOrFail($this->route('id'));
            $baseDate = $attendance->clock_in->format('Y-m-d');
            $clockIn = Carbon::parse("{$baseDate} {$this->requested_clock_in}");
            $clockOut = $this->requested_clock_out
                ? Carbon::parse("{$baseDate}  {$this->requested_clock_out}")
                : null;

            // 出勤 > 退勤のバリデーション
            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 各休憩の開始・終了時間のチェック
            if ($this->requested_breaks) {
                foreach ($this->requested_breaks as $index => $break) {

                    $breakIn  = !empty($break['break_in'])
                        ? Carbon::parse("{$baseDate} {$break['break_in']}")
                        : null;
                    $breakOut = !empty($break['break_out'])
                    ? Carbon::parse("{$baseDate} {$break['break_out']}")
                    : null;

                    // 休憩開始 < 出勤のバリデーション
                    if ($breakIn && $clockIn && $breakIn->lt($clockIn)) {
                        $validator->errors()->add("requested_breaks.$index.break_in", '休憩時間が不適切な値です');
                    }

                    // 休憩開始 > 退勤
                    if ($breakIn && $clockOut && $breakIn > $clockOut) {
                        $validator->errors()->add("requested_breaks.$index.break_in", '休憩時間が不適切な値です');
                    }

                    // 休憩終了 > 退勤
                    if ($breakOut && $clockOut && $breakOut > $clockOut) {
                        $validator->errors()->add("requested_breaks.$index.break_out", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
