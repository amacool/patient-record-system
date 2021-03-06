<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateCompanyRequest extends Request
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
            'seats' => 'required|Numeric'
        ];
    }

    public function messages()
    {
        return [
            'seats.required' => 'Du må oppgi antall brukere',
            'seats.numeric' => 'Antall brukere må oppgis med siffer'
        ];
    }
}
