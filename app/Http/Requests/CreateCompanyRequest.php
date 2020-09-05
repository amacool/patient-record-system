<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Http\Traits\SanitizeTrait;

class CreateCompanyRequest extends Request
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
            'name' => 'required|max:255|unique:companies',
            'seats' => 'required|Numeric',
            'orgnr' => 'required|Numeric'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Du må oppgi et firmanavn',
            'name.max'  => 'Firmanavnet kan ikke være lengre enn 255 tegn',
            'name.unique' => 'Firmanavnet er allerede i registeret',
            'seats.required' => 'Du må oppgi antall brukere',
            'seats.numeric' => 'Antall brukere må oppgis med siffer',
            'orgnr.required' => 'Du må oppgi organisasjonsnummer',
            'orgnr.numeric' => 'Organisasjonsnummeret må oppgis med siffer'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => SanitizeTrait::traitMethod($this->name),
        ]);
    }
}
