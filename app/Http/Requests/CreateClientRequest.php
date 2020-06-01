<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

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
            //lastname
            '6x93mscfgo' => 'required|max:50',
            //born
            'i2hmibi8a5' => 'required|date|regex:/\d{1,2}.\d{1,2}.\d{4}/|date_format:d.m.Y',
            //ssn
            '0rpk6x0uoe' => 'required|digits:5',
            //civil_status
            'g9npeyap1v' => 'max:100',
            //work status
            'vzjvte5v96' => 'string|max:100',
            //medication
            'ulij51r2f9' => 'max:255',
            //street_address
            'gvdd85c01k' => 'string|max:100',
            //postal_code
            'esrc80j3sc' => 'digits:4',
            //city
            '753lqcsbk4' => 'string|max:18',
            //phone
            's7tjrdoliy' => 'Numeric',
            //closest_relative
            '3p1jm4zdyp' => 'string|max:100',
            //closest relative phone
            'feucqwf7cx' => 'Numeric',
            //children
            '7hvwzk7f7t' => 'string|max:100',
            //gp
            '241i88imq9' => 'string|max:100',
            //individual plan
            'wlj5betr3c' => 'string|max:18',
            //other info
            'cya9753ajt' => 'string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            //firstname
            'dhjwhq3v7j.required' => 'Du må skrive inn fornavnet',
            'dhjwhq3v7j.max' => 'Fornavnet kan ha maksimalt 18 bokstaver',
            //lastname
            '6x93mscfgo.required' => 'Du må skrive inn etternavnet',
            '6x93mscfgo.max' => 'Etternavnet kan ha maksimalt 50 bokstaver',
            //born
            'i2hmibi8a5.required' => 'Du må angi en fødselsdato',
            //ssn
            '0rpk6x0uoe.required' => 'Du må angi et fødselsnummer',
            '0rpk6x0uoe.digits' => 'Fødselsnummeret må ha 5 tall',
            //born
            'i2hmibi8a5.date' => 'Du må angi en fødselsdato i formatet dd.mm.åååå',
            'i2hmibi8a5.date_format' => 'Fødselsdatoen må angis i formatet dd.mm.åååå',
            'i2hmibi8a5.regex' => 'Formatet for fødselsdato er ikke akseptert. Bruk dd.mm.åååå',
            //civil_status
            'g9npeyap1v.max' => 'Svilstatus kan ha maksimalt 100 bokstaver',
            //work_status
            'vzjvte5v96.max' => 'Arbeidsstatus kan ha maksimalt 100 bokstaver',
            //medication
            'ulij51r2f9.max' => 'Medisiner kan ha maksimalt 255 bokstaver',
            //street_address
            'gvdd85c01k.max' => 'Adressefeltet kan ha maksimalt 100 bokstaver',
            //postal code
            'esrc80j3sc.digits' => 'Postkoden må bestå av fire tall',
            // city
            '753lqcsbk4.max' => 'By kan ha maksimalt 18 bokstaver',
            //phone number
            's7tjrdoliy.numeric' => 'Telefonnummeret må bestå av kun tall',
            //closest relative
            '3p1jm4zdyp.max' => 'Nærmeste pårørende kan ha maksimalt 100 bokstaver',
            //closest relative phone
            'feucqwf7cx.numeric' => 'Telefonnummeret til nærmeste pårørende må bestå av kun tall',
            //children
            '7hvwzk7f7t.max' => 'Feltet for barn kan ha maksimalt 100 bokstaver',
            //gp
            '241i88imq9.max' => 'Feltet for fastlege kan ha maksimalt 100 bokstaver',
            //individual plan
            'wlj5betr3c.max' => 'Feltet for individuell plan kan ha maksimalt 18 bokstaver',
            //other info
            'cya9753ajt.max' => 'Feltet for annen informasjon kan ha maksimalt 1000 bokstaver',
        ];
    }
}
