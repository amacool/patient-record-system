<?php

namespace App\Http\Controllers;

use Authy\AuthyApi as AuthyApi;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomAuthTrait;
use App\Http\Traits\SanitizeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Company;
use App\Wpuser;
use App\Client;
use App\Record;
use App\Wpposts;
use App\Template;
use App\Awaitingupload;
use App\Awaitingdiagnoses;


class UserController extends Controller
{
    use CustomAuthTrait;
    use SanitizeTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        $this->middleware('revalidate');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 2) {
            $users = User::where('active', 1)->get();
            return view('users.index', compact('users'));
        }

        return redirect('/')->with('message', 'Du har ikke tilgang.');
    }

    public function inactiveIndex()
    {
        $user = Auth::user();

        if ($user->role === 2) {
            $users = User::where('active', 0)->get();
            return view('users.index', compact('users'));
        }

        return redirect('/')->with('message', 'Du har ikke tilgang.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($companyid, $userid)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($companyId, $userId)
    {
        $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
        $user = User::find($userId);
        $loggedInUser = Auth::user();
        $company = Company::find($companyId);
        $companies = Company::select('name', 'id')->get();
        $companyPairs = [];

        // Neither a system admin nor a logged in user
        if ($loggedInUser->role !== 2 && $user->id !== $loggedInUser->id) {
            return redirect('/')->with('message', 'Du har ikke tilgang');
        }

        foreach ($companies as $item) {
            $companyPairs[$item->id] = $item->name;
        }

        if ($user->authy_id !== null) {
            $authyStatus = $authy_api->userStatus($user->authy_id);
            $authyStatus = $authyStatus->bodyvar('status');
        };

        if ($user->authy_id == null) {
            $authyStatus = $authy_api->userStatus($user->authy_id);
            $authyStatus = null;
        };

        $logins = $user->logins()->where('success', 'Success')->orderBy('id', 'DESC')->take(5)->get();
        $wrongPassword = $user->logins()->where('success', '')->where('combocorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();
        $crackedPassword = $user->logins()->where('success', '')->where('combocorrect', 'Correct')->where('tokencorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();

        return view('users.edit', compact('user', 'company', 'logins', 'wrongPassword', 'crackedPassword', 'authyStatus', 'companyPairs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function accessLogs($companyId, $userId)
    {
        $user = User::find($userId);
        $loggedInUser = Auth::user();
        $company = Company::find($companyId);

        // Neither a system admin nor a logged in user
        if ($loggedInUser->role !== 2 && $user->id !== $loggedInUser->id) {
            return redirect('/')->with('message', 'Du har ikke tilgang');
        }

        $logins = $user->logins()->where('success', 'Success')->orderBy('id', 'DESC')->take(5)->get();
        $wrongPassword = $user->logins()->where('success', '')->where('combocorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();
        $crackedPassword = $user->logins()->where('success', '')->where('tokencorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();

        return view('users.logs', compact('user', 'loggedInUser', 'company', 'logins', 'wrongPassword', 'crackedPassword'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $companyId, $userId)
    {
        $this->validate($request, [
            'password' => ['required', 'confirmed', 'regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/'],
        ], [
            'password.required' => 'Du må oppgi et passord',
            'password.confirmed' => 'Passordet må skrives inn likt begge plasser',
            'password.regex' => 'Passordet må være minst 8 tegn, og inneholde både små bokstaver, store bokstaver og tall'
        ]);

        $user = User::find($userId);
        $loggedInUser = Auth::user();
        $credentials = $request->only(
            'password', 'password_confirmation'
        );

        // Neither a system admin nor a logged in user
        if ($loggedInUser->role !== 2 && $user->id !== $loggedInUser->id) {
            return redirect('/')->with('message', 'Du har ikke tilgang');
        }

        // If user is admin, allow password change without old password
        if ($loggedInUser->role === 2) {
            $user->password = bcrypt($credentials['password']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Passord endret');
        }

        // If user is not admin, check if old password is correct before allowing change
        $check = Hash::check($request['old_password'], $user->password);
        if (!$check) {
            return redirect('/')->with('message', 'Feil passord.');
        }

        if ($user->id == $loggedInUser->id) {
            $user->password = bcrypt($credentials['password']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Passord endret');
        }
    }

    public function standardTitle(Request $request, $companyId, $userId)
    {
        $this->validate($request, [
            'standard_title' => 'max:30',
        ], [
            'standard_title.max' => 'Standardtittelen kan ikke være lengre enn 30 tegn'
        ]);

        $user = User::find($userId);
        $loggedInUser = Auth::user();

        // Neither a system admin nor a logged in user
        if ($loggedInUser->role !== 2 && $user->id !== $loggedInUser->id) {
            return redirect('/')->with('message', 'Du har ikke tilgang');
        }

        $user->standard_title = SanitizeTrait::traitMethod($request['standard_title']);
        $user->save();
        return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Standardtittel endret');
    }

    public function secretQuestion(Request $request, $companyId, $userId)
    {
        $this->validate($request, [
            'secret_question' => 'required|max:255',
            'secret_answer' => 'required|max:255',
        ], [
            'secret_question.required' => 'Du må oppgi et hemmelig spørsmål',
            'secret_question.max' => 'Det hemmelige spørsmålet kan ikke være lengre enn 255 tegn',
            'secret_answer.required' => 'Du må oppgi et svar på det hemmelige spørsmålet',
            'secret_answer.max' => 'Svaret på det hemmelige spørsmålet kan ikke være lengre enn 255 tegn',
        ]);

        $user = User::find($userId);
        $loggedInUser = Auth::user();

        // Neither a system admin nor a logged in user
        if ($loggedInUser->role !== 2 && $user->id !== $loggedInUser->id) {
            return redirect('/')->with('message', 'Du har ikke tilgang');
        }

        $user->secret_question = Crypt::encrypt(SanitizeTrait::traitMethod($request['secret_question']));
        $user->secret_answer = Crypt::encrypt(SanitizeTrait::traitMethod($request['secret_answer']));
        $user->save();
        return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Hemmelig spørsmål endret');
    }


    // AUTHY REGISTER USER
    public function registerAuthy(Request $request, $companyId, $userId)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'country_code' => 'regex:/^\d{1,3}$/',
            'phone' => 'regex:/^\d{1,13}$/',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput($request->only('country_code', 'phone'))
                ->withErrors($validator);
        }

        $user = User::find($userId);
        $loggedInUser = Auth::user();

        if ($loggedInUser->role === 2) {
            $phone = $data['phone'];
            $country_code = $data['country_code'];
            $email = $user->email;

            $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
            $registeredUser = $authy_api->registerUser($email, $phone, $country_code);

            if ($registeredUser->ok()) {
                $user->authy_id = $registeredUser->id();
                $user->save();
            } else {
                // something went wrong
            }
            return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Registrering hos Authy vellykket');
        }
        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    // AUTHY REGISTER USER
    public function deleteAuthy(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();

        if ($loggedInUser->role === 2) {
            $user = User::find($userId);
            $data = $request->all();

            $authy_id = $data['authy_id'];

            $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));

            $user->authy_id = "";
            $user->save();

            return redirect()->route('companies.users.edit', [$companyId, $userId])->with('message', 'Slettet hos Authy');
        }
        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * CHANGE TWO-FACTOR STATUS
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function changeTwoFactor(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);

            if ($user->tfa === 0) {
                $user->tfa = 1;
                $user->save();
                return redirect()->back()->with('message', '2FA er nå PÅ');
            } else if ($user->tfa === 1) {
                $user->tfa = 0;
                $user->save();
                return redirect()->back()->with('message', 'ADVARSEL: 2FA er nå AV');
            }
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function paymentWarning(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);

            if (!$this->checkPaymentMissing($userId)) {
                $user->payment_missing = \Carbon\Carbon::now();
                $user->save();
                return redirect()->back()->with('message', 'Betalingsadvarsel slått på');
            } else {
                $user->payment_missing = null;
                $user->save();
                return redirect()->back()->with('message', 'Betalingsadvarsel slått av');
            }
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function suspendUser(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);

            if (!$this->checkSuspended($user->id, false)) {
                $user->suspended = \Carbon\Carbon::now();
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto låst');
            } else {
                $user->suspended = null;
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto åpnet');
            }
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function activateToggle(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);

            if ($user->active == 0) {
                $user->active = 1;
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto aktivert');
            } else {
                $user->active = 0;
                $user->suspended = null;
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto deaktivert');
            }
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function changeCompany(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);
            $user->company_id = $data['company_id'];
            $user->save();
            return redirect()->route('companies.users.edit', [$user->company->id, $user->id])->with('message', 'Firmatilhørighet endret');
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function changeRole(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);
            $user->role = $data['role'];
            $user->save();
            return redirect()->back()->with('message', 'Rolle endret');
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function changePhone(Request $request, $companyId, $userId)
    {
        $loggedInUser = Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedInUser->role === 2) {
            $user = User::find($userId);
            $user->phone = $data['phone'];
            $user->country_code = $data['country_code'];
            $user->save();
            return redirect()->back()->with('message', 'Telefonnummer endret');
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * View logs for a client
     *
     * @return \Illuminate\Http\Response
     */
    public function accessAndTransferLogs($companyId, $userId)
    {
        $loggedInUser = Auth::user();
        $user = User::find($userId);
        $company = Company::find($companyId);

        $this->checkSuspended($loggedInUser->id, true);

        if ($loggedInUser->role === 2 || $userId == $loggedInUser->id) {
            return view('users.accessAndTransfers', compact('user', 'company'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }


    // STARTING FUNCTIONS RELATED TO DATABASE TRANSFER

    // Function to decrypt a value from the old database
    public function oldDecrypt($value)
    {
        $key = env('WPKEY');

        // DECRYPT A VALUE FROM THE OLD DATABASE
        if (strpos($value, "enx") !== false) {
            if (strpos($value, "enx2:") !== false) {
                $value = str_replace("enx2:", "", $value);
                $salt = "djkns(235mk^p";
                $password = "7%r?1C" . $key . "jr-3";
                $key = hash('SHA256', $salt . $password, true);
                $iv = base64_decode(substr($value, 0, 22) . '==');
                $encrypted = substr($value, 22);
                $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
                $hash = substr($decrypted, -32);
                $decrypted = substr($decrypted, 0, -32);
                if (md5($decrypted) != $hash) $value = '';
                $value = $decrypted;
                return $value;
            }
        }
    }

    // Function to sync an old user with new id using email address as identifier
    public function syncUsers($email)
    {
        // Find the wordpress user by email address
        $wpuser = Wpuser::where('user_email', $email)->first();

        // Grab the existing user if he exists in the new database
        $newUser = User::where('email', $email)->first();

        // If the user exists in new system
        if (count($newUser)) {
            // add his ID to the wordpress user table
            $wpuser->newid = $newUser->id;
            $wpuser->save();

            // add the wordpress ID to the existing users table
            $newUser->oldid = $wpuser->ID;
            $newUser->save();
            return $newUser;
        }

        if (!count($newUser)) {
            $newUser = new User;
            $newUser->oldid = $wpuser->ID;
            $newUser->email = $wpuser->user_email;
            $newUser->name = $wpuser->display_name;
            $newUser->password = bcrypt('password');
            $newUser->company_id = 1;
            $newUser->save();

            $wpuser->newid = $newUser->id;
            $wpuser->save();
            return $newUser;
        }
    }

    // Get all users clients from old database

    public function findClients($userId)
    {
        // FOR TESTING
        // $userId = '28';
        $user = User::find($userId);

        // Get the users posts with "post_parent = 912", which is identifier for created patient
        $clients = $user->wpposts()->where('post_parent', 912)->where('post_type', 'page')->get();

        return $clients;
    }

    public function insertClients($userId, $clients)
    {
        foreach ($clients as $client) {
            $newclient = new Client;
            $newclient->user_id = $userId;
            $newclient->save();

            foreach ($client->wppostmeta as $meta) {
                $newclient->oldid = $meta['post_id'];
                $newclient->save();

                if ($meta['meta_key'] == "first-name") {
                    $newclient->firstname = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "last-name") {
                    $newclient->lastname = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                }

                /*if ($meta['meta_key'] == "date-of-birth") {
                    $newclient->born = \Carbon\Carbon::createFromFormat('d/m/Y', $meta['meta_value']);
                    $newclient->save();
                }*/

                if ($meta['meta_key'] == "date-of-birth") {
                    $newclient->born = $meta['meta_value'];
                    $newclient->save();
                } else if ($meta['meta_key'] == "social-security-no") {
                    $newclient->ssn = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "marital-status") {
                    $newclient->civil_status = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "occupation") {
                    $newclient->work_status = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "medication") {
                    $newclient->medication = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "street-address") {
                    $newclient->street_address = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "zip-code") {
                    $newclient->postal_code = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "City") {
                    $newclient->city = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "phone1") {
                    $newclient->phone = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "closest-relative") {
                    $newclient->closest_relative = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "phone-number-of-closest-relative") {
                    $newclient->closest_relative_phone = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "children") {
                    $newclient->children = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "general-practitioner") {
                    $newclient->gp = Crypt::encrypt($this->oldDecrypt($meta['meta_value']));
                    $newclient->save();
                } else if ($meta['meta_key'] == "individual-plan") {
                    $newclient->individual_plan = $this->oldDecrypt($meta['meta_value']);
                    $newclient->save();
                }

                $newclient->other_info = Crypt::encrypt("");
                $newclient->active = "1";
                $newclient->save();
            }
        }
    }

    public function insertWpRecord($userId, $wprecord)
    {
        // FOR TESTING
        // $userId = '28';

        $user = User::find($userId);
        $client = $wprecord->client;

        $record = new Record;
        $record->client_id = $client->id;
        $record->created_by = $user->id;
        $record->oldid = $wprecord->ID;

        if ($wprecord->post_excerpt == "") {
            $record->title = Crypt::encrypt("Ingen tittel");
        } else {
            $record->title = Crypt::encrypt($wprecord->post_excerpt);
        }
        $record->content = Crypt::encrypt($this->oldDecrypt($wprecord->post_content));
        $record->app_date = "";
        $record->created_at = $wprecord->post_date_gmt;
        $record->updated_at = $wprecord->post_modified_gmt;

        // What category is the record?
        // term taxonomy 1 = journal note
        if ($wprecord->wpterm->term_taxonomy_id == "27") {
            $record->category_id = "1";
        }

        // term taxonomy 2 = treatment plan
        if ($wprecord->wpterm->term_taxonomy_id == "28") {
            $record->category_id = "2";
        }

        // term taxonomy 3 = report
        if ($wprecord->wpterm->term_taxonomy_id == "29") {
            $record->category_id = "3";
        }

        // Is the record signed or not?
        $meta = $wprecord->wppostmeta;
        foreach ($meta as $meta) {
            if (($meta['meta_key'] == "_check") || ($meta['meta_value'] == "1")) {
                $record->signed_date = $wprecord->post_modified_gmt;
                $record->signed_by = $user->id;
            }
        }

        $record->save(['timestamps' => false]);

        return $record;
    }

    public function findTemplates($userId)
    {
        // FOR TESTING
        // $userId = '2';
        $user = User::find($userId);

        // Get the users posts with "post_parent = 912", which is identifier for created patient
        $templates = $user->wpposts()->where('post_status', 'publish')->where('post_parent', 0)->where('post_type', 'post')->get();

        return $templates;
    }

    public function insertTemplate($userId, $wptemplate)
    {
        $template = new Template;
        $template->title = $wptemplate->post_title;
        $template->content = $this->oldDecrypt($wptemplate->post_content);
        $template->category_id = "1";
        $template->created_by = $userId;
        $template->save();
    }

    public function filesToBeTransferred($userId)
    {
        $user = User::find($userId);
        $files = $user->wpposts()->where('post_type', 'attachment')->where('post_parent', '!=', '912')->get();

        foreach ($files as $file) {
            $awaiting = New Awaitingupload;
            $awaiting->client_id = $file->client->id;
            $awaiting->olduser_id = $file->user->oldid;
            $awaiting->oldclient_id = $file->post_parent;
            $awaiting->filename = $file->guid;
            $awaiting->user_id = $userId;
            $awaiting->awaiting = 1;
            $awaiting->save();
        }
    }

    public function insertDiagnoses($userId, $wprecord)
    {
        $awaiting = new Awaitingdiagnoses;
        $awaiting->user_id = $userId;
        $awaiting->olduser_id = $wprecord->user->oldid;
        $awaiting->client_id = $wprecord->client->id;
        $awaiting->oldclient_id = $wprecord->post_parent;
        $awaiting->title = $this->oldDecrypt($wprecord->post_title);
        $awaiting->content = $this->oldDecrypt($wprecord->post_content);
        $awaiting->save();
    }

    public function dbTransfer($email)
    {
        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        // using this for testing
        // $email = 'katrinerelander@gmail.com';

        // REINSTATE THIS LATER
        $newuser = $this->syncUsers($email);
        $userId = $newuser->id;

        // using this for testing
        // $userId = '29';

        $templates = $this->findTemplates($userId);
        foreach ($templates as $wptemplate) {
            $this->insertTemplate($userId, $wptemplate);
        }

        // REINSTATE THIS LATER
        $clients = $this->findClients($userId);

        // REINSTATE THIS LATER
        $this->insertClients($userId, $clients);

        $user = User::find($userId);
        $newClients = $user->clients;

        // INSERT PATIENT RECORDS
        foreach ($newClients as $newClient) {
            $wprecords = $newClient->wprecords()->where('post_type', 'post')->where('post_status', 'publish')->get();
            foreach ($wprecords as $wprecord) {
                // Do not store if record is not journal note, treatment plan or report
                if (
                    ($wprecord->wpterm->term_taxonomy_id == "25") OR
                    ($wprecord->wpterm->term_taxonomy_id == "26") OR
                    ($wprecord->wpterm->term_taxonomy_id == "30") OR
                    ($wprecord->wpterm->term_taxonomy_id == "31")
                ) {
                    break;
                }
                $this->insertWpRecord($userId, $wprecord);
            }
        }

        // INSERT DIAGNOSES
        foreach ($newClients as $newClient) {
            $wprecords = $newClient->wprecords()->where('post_type', 'post')->where('post_status', 'publish')->get();

            foreach ($wprecords as $wprecord) {
                if ($wprecord->wpterm->term_taxonomy_id == "26") {
                    $this->insertDiagnoses($userId, $wprecord);
                }
            }
        }

        $this->filesToBeTransferred($userId);
    }

    public function transferAll()
    {
        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        // Remove users "anwar" and "test" manually from wpusers
        // SELECT ALL USER EMAIL ADDRESSES FROM OLD WP DATABASE
        $emails = Wpuser::select('user_email')->get();
        // FOR EACH E-MAIL ADDRESS, RUN THE TRANSFER FUNCTION
        foreach ($emails as $email) {
            $address = $email->user_email;

            $this->dbTransfer($address);
        }

        return view('dbtransfer.result', compact('clients'));
    }

    public function transferClientRecords(Request $request, $companyId, $userId)
    {
        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        $data = $request->all();
        $client = Client::find($data['client_id']);

        if (!$client) {
            return redirect()->back()->with('message', 'There is no client.');
        }

        $wprecords = $client->wprecords()->where('post_type', 'post')->where('post_status', 'publish')->get();

        foreach ($wprecords as $wprecord) {
            /* // Do not store if record is not journal note, treatment plan or report
             if (
                 ($wprecord->wpterm->term_taxonomy_id == "25") OR
                 ($wprecord->wpterm->term_taxonomy_id == "26") OR
                 ($wprecord->wpterm->term_taxonomy_id == "30") OR
                 ($wprecord->wpterm->term_taxonomy_id == "31")
             ) {
                 break;
             }*/
            $this->insertWpRecord($userId, $wprecord);
        }
        return redirect()->back()->with('message', 'Telefonnummer endret');
    }

    public function transferSingleWprecord(Request $request, $companyId, $userId)
    {
        // FOR TESTING
        // $userid = '28';

        $user = User::find($userId);
        $wprecord = Wpposts::find($request['wprecordid']);

        $client = $wprecord->client;

        $record = new Record;
        $record->client_id = $client->id;
        $record->created_by = $user->id;
        $record->oldid = $wprecord->ID;

        if ($wprecord->post_excerpt == "") {
            $record->title = Crypt::encrypt("Ingen tittel");
        } else {
            $record->title = Crypt::encrypt($wprecord->post_excerpt);
        }
        $record->content = Crypt::encrypt($this->oldDecrypt($wprecord->post_content));
        $record->app_date = "";
        $record->created_at = $wprecord->post_date_gmt;
        $record->updated_at = $wprecord->post_modified_gmt;

        // What category is the record?
        // term taxonomy 1 = journal note
        if ($wprecord->wpterm->term_taxonomy_id == "27") {
            $record->category_id = "1";
        }

        // term taxonomy 2 = treatment plan
        if ($wprecord->wpterm->term_taxonomy_id == "28") {
            $record->category_id = "2";
        }

        // term taxonomy 3 = report
        if ($wprecord->wpterm->term_taxonomy_id == "29") {
            $record->category_id = "3";
        }

        // Is the record signed or not?
        $meta = $wprecord->wppostmeta;
        foreach ($meta as $meta) {
            if (($meta['meta_key'] == "_check") || ($meta['meta_value'] == "1")) {
                $record->signed_date = $wprecord->post_modified_gmt;
                $record->signed_by = $user->id;
            }
        }

        $record->save(['timestamps' => false]);

        return redirect()->back()->with('message', 'Notat overført');
    }

    public function wpVersionHistory()
    {
        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        $newRecordId = 1413;
        $newrecord = Record::find($newRecordId);
        $wpid = $newrecord->oldid;

        $revisions = Wpposts::where('post_parent', $wpid)->get();

        foreach ($revisions as $r) {
            $r->post_content = strip_tags($this->oldDecrypt($r->post_content));
        }

        return view('records.wphistory', compact('revisions'));
    }

    public function deleteUser($companyId, $userId)
    {
        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        $user = User::find($userId);
        $user->active = 0;

        function generateRandomString($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        $newPassword = generateRandomString();

        $user->password = bcrypt($newPassword);
        $user->save();

        foreach ($user->clients as $client) {
            $client->firstname = Crypt::encrypt("Klient");
            $client->lastname = Crypt::encrypt("Slettet");
            $client->born = "1970-01-01";
            $client->ssn = Crypt::encrypt("");
            $client->civil_status = Crypt::encrypt("");
            $client->work_status = Crypt::encrypt("");
            $client->medication = Crypt::encrypt("");
            $client->street_address = Crypt::encrypt("");
            $client->postal_code = Crypt::encrypt("");
            $client->city = Crypt::encrypt("");
            $client->phone = Crypt::encrypt("");
            $client->closest_relative = Crypt::encrypt("");
            $client->closest_relative_phone = Crypt::encrypt("");
            $client->children = Crypt::encrypt("");
            $client->gp = Crypt::encrypt("");
            $client->other_info = Crypt::encrypt("Klienten er slettet fordi klientansvarlig sin profil i systemet er slettet");
            $client->save();
        }

        foreach ($user->records as $record) {
            $record->title = Crypt::encrypt("Slettet");
            $record->content = Crypt::encrypt("Notatet er slettet fordi forfatteren slettet profilen sin i journalsystemet");
            $record->save();
        }

        foreach ($user->changerecord as $record) {
            $record->formertitle = Crypt::encrypt("Slettet");
            $record->formercontent = Crypt::encrypt("Historikken for notatet er slettet fordi forfatteren slettet profilen sin i systemet");
            $record->newtitle = Crypt::encrypt("Slettet");
            $record->newcontent = Crypt::encrypt("Historikken for notatet er slettet fordi forfatteren slettet profilen sin i systemet");
            $record->save();
        }

        foreach ($user->files as $file) {
            $file->file = "Slettet";
            $file->description = "Slettet fordi bruker avviklet journalsystem";
            $file->save();
        }

        // Set the company of the user to "Psykologbasen", to avoid the current company seeing him
        $user->company_id = 1;
        $user->save();

        return redirect('/')->with('message', 'Brukeren er slettet');
    }
}
