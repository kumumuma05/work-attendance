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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'click_in' => 'required|date_format:H:i',
            'click_out' => 'required|date_format:H:i',
            'break_in.*'  => ['nullable', 'date_format:H:i'],
            'break_out.*' => ['nullable', 'date_format:H:i'],
        ];
    }
}
