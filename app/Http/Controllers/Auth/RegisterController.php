<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\SanitizeTrait;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Company;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;
    use SanitizeTrait;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/'],
            'country_code' => ['regex:/^\d{1,3}$/'],
            'phone' => ['regex:/^\d{1,13}$/'],
        ], [
            'name.required' => 'Du må oppgi et navn',
            'name.string' => 'Navnet må være av typen string',
            'name.max' => 'Navnet kan ikke være lengre enn 255 tegn',
            'email.required' => 'Du må oppgi en epostadresse',
            'email.string' => 'Epostadressen må være av typen string',
            'email.email' => 'Epostformatet er ugyldig',
            'email.max' => 'Epostadressen kan ikke være lengre enn 255 tegn',
            'email.unique' => 'Epostadressen er allerede registrert i systemet',
            'password.required' => 'Du må oppgi et passord',
            'password.confirmed' => 'Passordet må skrives inn likt begge plasser',
            'password.regex' => 'Passordet må være minst 8 tegn, og inneholde både små bokstaver, store bokstaver og tall',
            'country_code.regex' => 'Formatet for landskode er ugyldig',
            'phone.regex' => 'Formatet for telefonnummer er ugyldig',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login-page');
        }

        // If user has role of admin, allow showing admin-page for registration of new user
        if ($user->role === 2 || ($user->role === 1 && $user->company_id == $data['company_id'])) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'oldid' => 0,
                'phone' => $data['phone'],
                'role' => 0,
                'favtemplate' => 0,
                'country_code' => $data['country_code'],
                'company_id' => $data['company_id'],
                'tfa' => 0,
                'standard_title' => "",
                'payment_missing' => null,
                'suspended' => null,
                'secret_question' => "",
                'active' => 1,
                'created_by' => $user->id
            ]);
        }

        // If neither system admin or company admin, redirect to home page
        return redirect('/')->with('message', 'You are trying to perform an illegal action.');
    }

    public function postRegister(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login-page');
        }

        $data = $this->sanitizeRequest($request->all());
        $validator = $this->validator($data);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        // Check for company validation
        $company = Company::findOrFail($data['company_id']);
        if (!$company) {
            return back()->withInput()->with('message', 'Invalid company!');;
        }

        // Create the user if it passes validation
        // The create function will check if user is allowed to create a new user (admins and companyadmins)
        $this->create($data);

        // Register the new user with Authy. This will include an sms to the user that he has been registered,
        // and also a link to download the app
        // $user->registerAuthy();

        return redirect($this->redirectPath());
    }

    public function getRegister()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login-page');
        }

        // If user has role of admin, allow showing admin-page for registration of new user
        if ($user->role === 2) {
            $companiesArr = Company::select('name', 'id')->get();
            $companies = [];
            foreach ($companiesArr as $company) {
                $companies[$company->id] = $company->name;
            }

            return view('auth.aregister', compact('companies'));
        }

        // If user has role of "company admin", allow showing of registration of new users for this specific company
        if ($user->role === 1) {
            $company = $user->company;
            return view('auth.caregister', compact('company'));
        }

        // If neither system admin nor company admin, redirect to home page
        return redirect('/')->with('message', 'You are trying to perform an illegal action.');
    }
}
