<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProvideAccessRequest extends Request
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
            'reason' => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'Du må angi en årsak',
        ];
    }
}
