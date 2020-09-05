<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Http\Traits\SanitizeTrait;

class CreateTemplateRequest extends Request
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
            'title' => 'required|max:30',
            'content' => 'required|max:10000'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Malen må ha en tittel',
            'title.max' => 'Tittelen kan ha max 30 tegn',
            'content.required' => 'Malen må ha innhold før du kan lagre den',
            'content.max' => 'Innholdet i malen kan ikke være lengre enn 10 000 tegn'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => SanitizeTrait::traitMethod($this->title),
        ]);
    }
}
