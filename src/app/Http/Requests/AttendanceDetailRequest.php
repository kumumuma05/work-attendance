<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'requested_clock_out.required' => '出勤時間が入力されていません',
            'remarks.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->requested_clock_in;
            $clockOut = $this->requested_clock_out;

            // 出勤 > 退勤 の異常
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 各休憩の開始・終了時間のチェック
            if ($this->requested_breaks) {
                foreach ($this->requested_breaks as $index => $break) {

                $breakIn  = $break['break_in']  ?? null;
                $breakOut = $break['break_out'] ?? null;

                    // 休憩開始 < 出勤
                    if ($breakIn && $clockIn && $breakIn < $clockIn) {
                        $validator->errors()->add("breaks.$index.break_in", '休憩時間が不適切な値です');
                    }

                    // 休憩開始 > 退勤
                    if ($breakIn && $clockOut && $breakIn > $clockOut) {
                        $validator->errors()->add("breaks.$index.break_in", '休憩時間が不適切な値です');
                    }

                    // 休憩終了 > 退勤
                    if ($breakOut && $clockOut && $breakOut > $clockOut) {
                        $validator->errors()->add("breaks.$index.break_out", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
