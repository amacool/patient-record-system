<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UploadFileRequest extends Request
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
            'file' => 'required|mimes:pdf|max:10240',
            'description' => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Du må legge til en fil',
            'file.mimes' => 'Du kan kun laste opp filer av typen pdf',
            'file.max' => 'Du kan ikke laste opp filer større enn 10 MB',
            'description.required' => 'Du må legge til en beskrivelse av filen',
        ];
    }
}
