<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Http\Traits\SanitizeTrait;

class CreateClientRequest extends Request
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
            // firstname
            'dhjwhq3v7j' => 'required|max:18',
            // lastname
            '6x93mscfgo' => 'required|max:50',
            // born
            'i2hmibi8a5' => 'required|date_format:d.m.Y',
            // ssn
            '0rpk6x0uoe' => 'required|digits:5',
            // civil_status
            'g9npeyap1v' => 'nullable|max:100',
            // work status
            'vzjvte5v96' => 'nullable|string|max:100',
            // medication
            'ulij51r2f9' => 'nullable|max:255',
            // street_address
            'gvdd85c01k' => 'nullable|string|max:100',
            // postal_code
            'esrc80j3sc' => 'nullable|digits:4',
            // city
            '753lqcsbk4' => 'nullable|string|max:18',
            // phone
            's7tjrdoliy' => 'nullable|Numeric',
            // closest_relative
            '3p1jm4zdyp' => 'nullable|string|max:100',
            // closest relative phone
            'feucqwf7cx' => 'nullable|Numeric',
            // children
            '7hvwzk7f7t' => 'nullable|string|max:100',
            // gp
            '241i88imq9' => 'nullable|string|max:100',
            // individual plan
            'wlj5betr3c' => 'nullable|string|max:18',
            // other info
            'cya9753ajt' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            // firstname
            'dhjwhq3v7j.required' => 'Du må skrive inn fornavnet',
            'dhjwhq3v7j.max' => 'Fornavnet kan ha maksimalt 18 bokstaver',
            // lastname
            '6x93mscfgo.required' => 'Du må skrive inn etternavnet',
            '6x93mscfgo.max' => 'Etternavnet kan ha maksimalt 50 bokstaver',
            // born
            'i2hmibi8a5.required' => 'Du må angi en fødselsdato',
            'i2hmibi8a5.date_format' => 'Fødselsdatoen må angis i formatet dd.mm.åååå',
            // ssn
            '0rpk6x0uoe.required' => 'Du må angi et fødselsnummer',
            '0rpk6x0uoe.digits' => 'Fødselsnummeret må ha 5 tall',
            // civil_status
            'g9npeyap1v.max' => 'Svilstatus kan ha maksimalt 100 bokstaver',
            // work_status
            'vzjvte5v96.max' => 'Arbeidsstatus kan ha maksimalt 100 bokstaver',
            // medication
            'ulij51r2f9.max' => 'Medisiner kan ha maksimalt 255 bokstaver',
            // street_address
            'gvdd85c01k.max' => 'Adressefeltet kan ha maksimalt 100 bokstaver',
            // postal code
            'esrc80j3sc.digits' => 'Postkoden må bestå av fire tall',
            // city
            '753lqcsbk4.max' => 'By kan ha maksimalt 18 bokstaver',
            // phone number
            's7tjrdoliy.numeric' => 'Telefonnummeret må bestå av kun tall',
            // closest relative
            '3p1jm4zdyp.max' => 'Nærmeste pårørende kan ha maksimalt 100 bokstaver',
            // closest relative phone
            'feucqwf7cx.numeric' => 'Telefonnummeret til nærmeste pårørende må bestå av kun tall',
            // children
            '7hvwzk7f7t.max' => 'Feltet for barn kan ha maksimalt 100 bokstaver',
            // gp
            '241i88imq9.max' => 'Feltet for fastlege kan ha maksimalt 100 bokstaver',
            // individual plan
            'wlj5betr3c.max' => 'Feltet for individuell plan kan ha maksimalt 18 bokstaver',
            // other info
            'cya9753ajt.max' => 'Feltet for annen informasjon kan ha maksimalt 1000 bokstaver',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'dhjwhq3v7j' => SanitizeTrait::traitMethod($this['dhjwhq3v7j']),
            '6x93mscfgo' => SanitizeTrait::traitMethod($this['6x93mscfgo']),
            'g9npeyap1v' => SanitizeTrait::traitMethod($this['g9npeyap1v']),
            'vzjvte5v96' => SanitizeTrait::traitMethod($this['vzjvte5v96']),
            'ulij51r2f9' => SanitizeTrait::traitMethod($this['ulij51r2f9']),
            'gvdd85c01k' => SanitizeTrait::traitMethod($this['gvdd85c01k']),
            '753lqcsbk4' => SanitizeTrait::traitMethod($this['753lqcsbk4']),
            '3p1jm4zdyp' => SanitizeTrait::traitMethod($this['3p1jm4zdyp']),
            '7hvwzk7f7t' => SanitizeTrait::traitMethod($this['7hvwzk7f7t']),
            '241i88imq9' => SanitizeTrait::traitMethod($this['241i88imq9']),
            'wlj5betr3c' => SanitizeTrait::traitMethod($this['wlj5betr3c']),
            'cya9753ajt' => SanitizeTrait::traitMethod($this['cya9753ajt']),
        ]);
    }
}
