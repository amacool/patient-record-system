<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Http\Traits\SanitizeTrait;

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
            'reason.max' => 'Beskrivelsen av årsak kan ikke være lengre enn 255 tegn'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'reason' => SanitizeTrait::traitMethod($this->reason),
        ]);
    }
}
