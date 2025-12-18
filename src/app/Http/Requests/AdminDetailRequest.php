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
            
        ];
    }
}
