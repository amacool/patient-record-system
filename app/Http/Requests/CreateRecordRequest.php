<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Http\Traits\SanitizeTrait;

class CreateRecordRequest extends Request
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
            'app_date' => 'required|date',
            'title' => 'required|max:18',
            'content' => 'required|max:15000'
        ];
    }

    public function messages()
    {
        return [
            'app_date.required' => 'Du må sette en dato for avtalen. Dersom du skriver en rapport / notat uten avtaledato, fyller du inn datoen da du begynte å skrive',
            'app_date.date' => 'Avtaledato må være i formatet dd-mm-åååå',
            'title.required' => 'Notatet må ha en tittel',
            'title.max' => 'Tittelen kan ha max 18 tegn',
            'content.required' => 'Notatet må ha innhold før du kan lagre det',
            'content.max' => 'Notatet kan ikke være lengre enn 15000 tegn'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => SanitizeTrait::traitMethod($this->title),
            'app_date' => SanitizeTrait::traitMethod($this->app_date),
        ]);
    }
}
