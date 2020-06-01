<?php

namespace App\Http\Controllers;

use Authy\AuthyApi as AuthyApi;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    // Only allow logged in users
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
        $user = \Auth::user();

        if ($user->role == 2) {
            $users = \App\User::where('active', 1)->get();

            return view('users.index', compact('users'));
        }

        return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
    }

    public function inactiveindex()
    {
        $user = \Auth::user();

        if ($user->role == 2) {
            $users = \App\User::where('active', 0)->get();

            return view('users.index', compact('users'));
        }

        return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($companyid, $userid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($companyid, $userid)
    {
        $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
        $user = \App\User::find($userid);
        $loggedinuser = \Auth::user();
        $company = \App\Company::find($companyid);
        $companies = \App\Company::lists('name', 'id');

        if ($user->authy_id !== null) {
            $authystatus = $authy_api->userStatus($user->authy_id);
            //dd($authystatus->ok());
            //dd($authystatus->errors());
            $authystatus = $authystatus->bodyvar('status');
        };

        if ($user->authy_id == null) {
            $authystatus = $authy_api->userStatus($user->authy_id);
            //dd($authystatus->ok());
            //dd($authystatus->errors());
            $authystatus = null;
        };

        $logins = $user->logins()->where('success', 'Success')->orderBy('id', 'DESC')->take(5)->get();
        $wrongpassword = $user->logins()->where('success', '')->where('combocorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();
        $crackedpassword = $user->logins()->where('success', '')->where('combocorrect', 'Correct')->where('tokencorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();

        if ($loggedinuser->role == '2') {
            return view('users.edit', compact('user', 'loggedinuser', 'company', 'logins', 'wrongpassword', 'crackedpassword', 'authystatus', 'companies'));
        }
        if ($user->id == $loggedinuser->id) {
            return view('users.edit', compact('user', 'loggedinuser', 'company', 'logins', 'wrongpassword', 'crackedpassword', 'authystatus', 'companies'));
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function accesslogs($companyid, $userid)
    {
        $user = \App\User::find($userid);
        $loggedinuser = \Auth::user();
        $company = \App\Company::find($companyid);

        $logins = $user->logins()->where('success', 'Success')->orderBy('id', 'DESC')->take(5)->get();
        $wrongpassword = $user->logins()->where('success', '')->where('combocorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();
        $crackedpassword = $user->logins()->where('success', '')->where('tokencorrect', 'Error')->orderBy('id', 'DESC')->take(5)->get();

        if ($loggedinuser->role == '2') {
            return view('users.logs', compact('user', 'loggedinuser', 'company', 'logins', 'wrongpassword', 'crackedpassword'));
        }
        if ($user->id == $loggedinuser->id) {
            return view('users.logs', compact('user', 'loggedinuser', 'company', 'logins', 'wrongpassword', 'crackedpassword'));
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $companyid, $userid)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:8|regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/',
        ]);

        $user = \App\User::find($userid);
        $loggedinuser = \Auth::user();
        $company = \App\Company::find($companyid);

        $credentials = $request->only(
            'password', 'password_confirmation'
        );

        // If user is admin, allow password change without old password
        if ($loggedinuser->role == '2') {
            $user->password = bcrypt($credentials['password']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Passord endret');

        }

        //If user is not admin, check if old password is correct before allowing change
        $check = Hash::check($request['oldpassword'], $user->password);
        if (!$check) {
            return redirect()->route('home')->with('message', 'Feil passord.');
        }

        if ($user->id == $loggedinuser->id) {
            $user->password = bcrypt($credentials['password']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Passord endret');

        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    public function standardtitle(Request $request, $companyid, $userid)
    {
        $this->validate($request, [
            'standardtitle' => 'max:30',
        ]);

        $user = \App\User::find($userid);
        $loggedinuser = \Auth::user();
        $company = \App\Company::find($companyid);
        $data = $request->all();


        if ($loggedinuser->role == '2') {
            $user->standardtitle = $request['standardtitle'];
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Standardtittel endret');
        }

        if ($user->id == $loggedinuser->id) {
            $user->standardtitle = $request['standardtitle'];
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Standardtittel endret');
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    public function secretquestion(Request $request, $companyid, $userid)
    {
        $this->validate($request, [
            'secretquestion' => 'required|max:255',
            'secretanswer' => 'required|max:255',
        ]);

        $user = \App\User::find($userid);
        $loggedinuser = \Auth::user();
        $company = \App\Company::find($companyid);
        $data = $request->all();


        if ($loggedinuser->role == '2') {
            $user->secretquestion = Crypt::encrypt($request['secretquestion']);
            $user->secretanswer = Crypt::encrypt($request['secretanswer']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Hemmelig spørsmål endret');
        }

        if ($user->id == $loggedinuser->id) {
            $user->secretquestion = Crypt::encrypt($request['secretquestion']);
            $user->secretanswer = Crypt::encrypt($request['secretanswer']);
            $user->save();
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Hemmelig spørsmål endret');
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }


    // AUTHY REGISTER USER
    public function registerauthy(Request $request, $companyid, $userid)
    {
        $user = \App\User::find($userid);

        $loggedinuser = \Auth::user();

        if ($loggedinuser->role == 2) {
            $data = $request->all();
            $phone = $data['phone'];
            $country_code = $data['country_code'];
            $email = $user->email;

            $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
            $registereduser = $authy_api->registerUser($email, $phone, $country_code);

            if ($registereduser->ok()) {
                $user->authy_id = $registereduser->id();
                $user->save();
            } else {
                // something went wrong
            }
            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Registrering hos Authy vellykket');
        }
        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    // AUTHY REGISTER USER
    public function deleteauthy(Request $request, $companyid, $userid)
    {

        $loggedinuser = \Auth::user();

        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);
            $data = $request->all();

            $authy_id = $data['authy_id'];

            $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
            $deleteduser = $authy_api->deleteUser($authy_id);

            $user->authy_id = "";
            $user->save();

            return redirect()->route('companies.users.edit', [$companyid, $userid])->with('message', 'Slettet hos Authy');

        }
        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');
    }

    /**
     * CHANGE TWO-FACTOR STATUS
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function changetwofactor(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);

            if ($user->tfa == '0') {
                $user->tfa = '1';
                $user->save();
                return redirect()->back()->with('message', '2FA er nå PÅ');
            }

            if ($user->tfa == '1') {
                $user->tfa = '0';
                $user->save();
                return redirect()->back()->with('message', 'ADVARSEL: 2FA er nå AV');
            }
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function paymentwarning(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);

            if ($user->paymentmissing == '0000-00-00') {
                $user->paymentmissing = \Carbon\Carbon::now();
                $user->save();
                return redirect()->back()->with('message', 'Betalingsadvarsel slått på');
            }

            if ($user->paymentmissing !== '0000-00-00') {
                $user->paymentmissing = "0000-00-00";
                $user->save();
                return redirect()->back()->with('message', 'Betalingsadvarsel slått av');
            }
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function suspenduser(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);

            if ($user->suspended == '0000-00-00') {
                $user->suspended = \Carbon\Carbon::now();
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto låst');
            }

            if ($user->suspended !== '0000-00-00') {
                $user->suspended = "0000-00-00";
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto åpnet');
            }
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function activatetoggle(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);

            if ($user->active == 0) {
                $user->active = 1;
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto aktivert');
            }

            if ($user->active !== 0) {
                $user->suspended = 0;
                $user->save();
                return redirect()->back()->with('message', 'Brukerkonto deaktivert');
            }
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function changecompany(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);
            $user->company_id = $data['company_id'];
            $user->save();
            return redirect()->route('companies.users.edit', [$user->company->id, $user->id])->with('message', 'Firmatilhørighet endret');
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function changerole(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);
            $user->role = $data['role'];
            $user->save();
            return redirect()->back()->with('message', 'Rolle endret');
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    public function changephone(Request $request, $companyid, $userid)
    {
        $loggedinuser = \Auth::user();
        $data = $request->all();

        // Action can only be performed by system admin
        if ($loggedinuser->role == 2) {
            $user = \App\User::find($userid);
            $user->phone = $data['phone'];
            $user->country_code = $data['country_code'];
            $user->save();
            return redirect()->back()->with('message', 'Telefonnummer endret');
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }

    /**
     * View logs for a client
     *
     * @return \Illuminate\Http\Response
     */
    public function accessandtransferlogs($companyid, $userid)
    {
        $loggedinuser = \Auth::user();
        $user = \App\User::find($userid);
        $company = \App\Company::find($companyid);

        if ($loggedinuser->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        if ($loggedinuser->role == '2') {
            return view('users.accessandtransfers', compact('user', 'company'));
        }
        if ($userid == $loggedinuser->id) {
            return view('users.accessandtransfers', compact('user', 'company'));
        }

        // Else, redirect to home page
        return redirect()->route('home')->with('message', 'Du har ikke tilgang');

    }


    // STARTING FUNCTIONS RELATED TO DATABASE TRANSFER

    // Function to decrypt a value from the old database
    public function olddecrypt($value)
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
    public function syncusers($email)
    {


        // Find the wordpress user by email address
        $wpuser = \App\Wpuser::where('user_email', $email)->first();

        // Grab the existing user if he exists in the new database
        $newuser = \App\User::where('email', $email)->first();

        // If the user exists in new system
        if (count($newuser)) {
            //add his ID to the wordpress user table
            $wpuser->newid = $newuser->id;
            $wpuser->save();

            // add the wordpress ID to the existing users table
            $newuser->oldid = $wpuser->ID;
            $newuser->save();
            return $newuser;
        }

        if (!count($newuser)) {

            $newuser = new \App\User;
            $newuser->oldid = $wpuser->ID;
            $newuser->email = $wpuser->user_email;
            $newuser->name = $wpuser->display_name;
            $newuser->password = bcrypt('password');
            $newuser->company_id = 1;
            $newuser->save();

            $wpuser->newid = $newuser->id;
            $wpuser->save();
            return $newuser;
        }
    }

    // Get all users clients from old database

    public function findclients($userid)
    {
        // FOR TESTING
        //$userid = '28';
        $user = \App\User::find($userid);

        // Get the users posts with "post_parent = 912", which is identifier for created patient
        $clients = $user->wpposts()->where('post_parent', 912)->where('post_type', 'page')->get();

        return $clients;
    }

    public function insertclients($userid, $clients)
    {
        foreach ($clients as $client) {

            $newclient = new \App\Client;
            $newclient->user_id = $userid;
            $newclient->save();

            foreach ($client->wppostmeta as $meta) {

                $newclient->oldid = $meta['post_id'];
                $newclient->save();

                if ($meta['meta_key'] == "first-name") {
                    $newclient->firstname = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "last-name") {
                    $newclient->lastname = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                /*if ($meta['meta_key'] == "date-of-birth") {

                        $newclient->born = \Carbon\Carbon::createFromFormat('d/m/Y', $meta['meta_value']);
                        $newclient->save();

                }*/

                if ($meta['meta_key'] == "date-of-birth") {
                    $newclient->born = $meta['meta_value'];
                    $newclient->save();

                }

                if ($meta['meta_key'] == "social-security-no") {
                    $newclient->ssn = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "marital-status") {
                    $newclient->civil_status = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "occupation") {
                    $newclient->work_status = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "medication") {
                    $newclient->medication = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "street-address") {
                    $newclient->street_address = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "zip-code") {
                    $newclient->postal_code = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "City") {
                    $newclient->city = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "phone1") {
                    $newclient->phone = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "closest-relative") {
                    $newclient->closest_relative = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "phone-number-of-closest-relative") {
                    $newclient->closest_relative_phone = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "children") {
                    $newclient->children = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "general-practitioner") {
                    $newclient->gp = Crypt::encrypt($this->olddecrypt($meta['meta_value']));
                    $newclient->save();
                }

                if ($meta['meta_key'] == "individual-plan") {
                    $newclient->individual_plan = $this->olddecrypt($meta['meta_value']);
                    $newclient->save();
                }

                $newclient->other_info = Crypt::encrypt("");
                $newclient->active = "1";
                $newclient->save();
            }
        }
    }

    public function insertwprecord($userid, $wprecord)
    {
        // FOR TESTING
        //$userid = '28';

        $user = \App\User::find($userid);

        $client = $wprecord->client;


        $record = new \App\Record;
        $record->client_id = $client->id;
        $record->created_by = $user->id;
        $record->oldid = $wprecord->ID;

        if ($wprecord->post_excerpt == "") {
            $record->title = Crypt::encrypt("Ingen tittel");
        }
        if ($wprecord->post_excerpt !== "") {
            $record->title = Crypt::encrypt($wprecord->post_excerpt);
        }
        $record->content = Crypt::encrypt($this->olddecrypt($wprecord->post_content));
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
            if (($meta['meta_key'] == "_check") AND ($meta['meta_value'] == "1")) {
                $record->signed_date = $wprecord->post_modified_gmt;
                $record->signed_by = $user->id;
            }
        }

        $record->save(['timestamps' => false]);

        return $record;

    }

    public function findtemplates($userid)
    {
        // FOR TESTING
        //$userid = '2';
        $user = \App\User::find($userid);


        // Get the users posts with "post_parent = 912", which is identifier for created patient
        $templates = $user->wpposts()->where('post_status', 'publish')->where('post_parent', 0)->where('post_type', 'post')->get();

        return $templates;
    }

    public function inserttemplate($userid, $wptemplate)
    {
        $user = \App\User::find($userid);

        $template = new \App\Template;
        $template->title = $wptemplate->post_title;
        $template->content = $this->olddecrypt($wptemplate->post_content);
        $template->category_id = "1";
        $template->created_by = $userid;
        $template->save();

    }

    public function filestobetransferred($userid)
    {
        $user = \App\User::find($userid);

        $files = $user->wpposts()->where('post_type', 'attachment')->where('post_parent', '!=', '912')->get();

        foreach ($files as $file) {

            $awaiting = New \App\Awaitingupload;

            $awaiting->client_id = $file->client->id;
            $awaiting->olduser_id = $file->user->oldid;
            $awaiting->oldclient_id = $file->post_parent;
            $awaiting->filename = $file->guid;
            $awaiting->user_id = $userid;
            $awaiting->awaiting = 1;
            $awaiting->save();
        }
    }

    public function insertdiagnoses($userid, $wprecord)
    {
        $awaiting = new \App\Awaitingdiagnoses;
        $awaiting->user_id = $userid;
        $awaiting->olduser_id = $wprecord->user->oldid;
        $awaiting->client_id = $wprecord->client->id;
        $awaiting->oldclient_id = $wprecord->post_parent;
        $awaiting->title = $this->olddecrypt($wprecord->post_title);
        $awaiting->content = $this->olddecrypt($wprecord->post_content);
        $awaiting->save();
    }

    public function dbtransfer($email)
    {
        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        //using this for testing
        //$email = 'katrinerelander@gmail.com';

        // REINSTATE THIS LATER
        $newuser = $this->syncusers($email);
        $userid = $newuser->id;

        //using this for testing
        //$userid = '29';

        $templates = $this->findtemplates($userid);
        foreach ($templates as $wptemplate) {
            $this->inserttemplate($userid, $wptemplate);
        }

        // REINSTATE THIS LATER
        $clients = $this->findclients($userid);

        // REINSTATE THIS LATER
        $this->insertclients($userid, $clients);

        $user = \App\User::find($userid);
        $newclients = $user->clients;


        // INSERT PATIENT RECORDS
        foreach ($newclients as $newclient) {
            $wprecords = $newclient->wprecords()->where('post_type', 'post')->where('post_status', 'publish')->get();
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
                $this->insertwprecord($userid, $wprecord);
            }
        }

        // INSERT DIAGNOSES

        foreach ($newclients as $newclient) {
            $wprecords = $newclient->wprecords()->where('post_type', 'post')->where('post_status', 'publish')->get();

            foreach ($wprecords as $wprecord) {
                if ($wprecord->wpterm->term_taxonomy_id == "26") {
                    $this->insertdiagnoses($userid, $wprecord);
                }
            }
        }

        $this->filestobetransferred($userid);

    }

    public function transferall()
    {
        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        // Remove users "anwar" and "test" manually from wpusers
        // SELECT ALL USER EMAIL ADDRESSES FROM OLD WP DATABASE
        $emails = \App\Wpuser::select('user_email')->get();
        // FOR EACH E-MAIL ADDRESS, RUN THE TRANSFER FUNCTION
        foreach ($emails as $email) {
            $address = $email->user_email;

            $this->dbtransfer($address);
        }

        return view('dbtransfer.result', compact('clients'));
    }

    public function transferclientrecords(Request $request, $companyid, $userid)
    {
        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        $data = $request->all();

        $client = \App\Client::find($data['client_id']);

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
            $this->insertwprecord($userid, $wprecord);
        }
        return redirect()->back()->with('message', 'Telefonnummer endret');

    }

    public function transfersinglewprecord(Request $request, $companyid, $userid)
    {
        // FOR TESTING
        //$userid = '28';

        $user = \App\User::find($userid);
        $data = $request->all();
        $wprecord = \App\Wpposts::find($request['wprecordid']);

        $client = $wprecord->client;

        $record = new \App\Record;
        $record->client_id = $client->id;
        $record->created_by = $user->id;
        $record->oldid = $wprecord->ID;

        if ($wprecord->post_excerpt == "") {
            $record->title = Crypt::encrypt("Ingen tittel");
        }
        if ($wprecord->post_excerpt !== "") {
            $record->title = Crypt::encrypt($wprecord->post_excerpt);
        }
        $record->content = Crypt::encrypt($this->olddecrypt($wprecord->post_content));
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
            if (($meta['meta_key'] == "_check") AND ($meta['meta_value'] == "1")) {
                $record->signed_date = $wprecord->post_modified_gmt;
                $record->signed_by = $user->id;
            }
        }

        $record->save(['timestamps' => false]);

        return redirect()->back()->with('message', 'Notat overført');

    }


    public function wpversionhistory()
    {
        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        $newrecordid = 1413;
        $newrecord = \App\Record::find($newrecordid);
        $wpid = $newrecord->oldid;

        $revisions = \App\Wpposts::where('post_parent', $wpid)->get();

        foreach ($revisions as $r) {
            $r->post_content = strip_tags($this->olddecrypt($r->post_content));
        }

        return view('records.wphistory', compact('revisions'));
    }

    public function deleteuser($companyid, $userid)
    {
        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        $user = \App\User::find($userid);

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

        $newpassword = generateRandomString();

        $user->password = bcrypt($newpassword);
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

        return redirect()->route('home')->with('message', 'Brukeren er slettet');
    }

}
