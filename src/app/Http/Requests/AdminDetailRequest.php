<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminDetailRequest extends FormRequest
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
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i',
            'remarks' => 'required',
            'requested_breaks' => 'nullable|array',
            'requested_breaks.*.break_in'      => 'nullable|date_format:H:i',
            'requested_breaks.*.break_out'     => 'nullable|date_format:H:i',
        ];
    }
}
