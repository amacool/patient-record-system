<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    use CustomTraitAuth;

    // Only allow logged in users
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        $this->middleware('revalidate');
    }

    /**
     * View archived clients for a logged in user
     *
     * @return \Illuminate\Http\Response
     */
    public function archiveindex()
    {
        $user = \Auth::User();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din har blitt begrenset på grunn av mangelfull betaling');
        }

        $clients = $user->clients()->where('active', '0')->orderBy('lastname', 'ASC')->get();

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.archiveindex', compact('clients'));
    }

    /**
     * Move a client between active and archive status
     *
     * @return \Illuminate\Http\Response
     */
    public function archivemove(Request $request)
    {
        $data = $request->all();
        $user = \Auth::User();
        $client = \App\Client::find($data['client_id']);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

            if ($client->active == '0') {
                $client->active = '1';
                $client->save();
                return redirect()->route('clients.archiveindex')->with('message', 'Klienten er nå aktiv');
            }

            if ($client->active == '1') {
                $client->active = '0';
                $client->save();
                return redirect()->route('clients.index')->with('message', 'Klienten er flyttet til arkivet');
            }
    }

    /**
     * Page for showing the access rights for a specific client
     *
     * @return \Illuminate\Http\Response
     */
    public function access($id)
    {
        $user = \Auth::user();
        $client = \App\Client::find($id);

        // Check if the user is the owner of the client
        $owner = $this->owner($user->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $coopusers = $client->user;

        $id = [];

        foreach ($coopusers as $coopuser) {
            $id[] = $coopuser->id;
        }

        $company = $user->company;
        $restofcompany = $company->user()->wherenotIn('id', $id)->get();

        return view('clients.access', compact('client', 'coopusers', 'restofcompany'));
    }

    public function accessform($clientid, $userid)
    {
        $loggedinuser = \Auth::user();
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('clients.accessform', compact('client', 'user'));
    }

    public function accessformpost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedinuser = \Auth::user();
        $client = \App\Client::find($data['client_id']);
        $user = \App\User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->user()->attach($user->id);

        // Log the event in table 'accessrights'
        $log = new \App\Accessright;

        $log->given_by = $loggedinuser->id;
        $log->user_id = $user->id;
        $log->client_id = $client->id;
        $log->reason = $data['reason'];
        $log->datetime = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.access', [$client->id])->with('message', 'Tilganger endret');

    }

    public function removeaccessform($clientid, $userid)
    {
        $loggedinuser = \Auth::user();
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $iscoopclient = $this->cooperativeaccess($userid, $clientid);
        if (!$iscoopclient){
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

            return view('clients.removeaccessform', compact('client', 'user'));
    }

    public function removeaccessformpost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedinuser = \Auth::user();
        $client = \App\Client::find($data['client_id']);
        $user = \App\User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

            $client->user()->detach($user->id);

            // Log the event in table 'accessrights'
            $log = new \App\Accessright;

            $log->revoked_by = $loggedinuser->id;
            $log->user_id = $user->id;
            $log->client_id = $client->id;
            $log->reason = $data['reason'];
            $log->datetime = \Carbon\Carbon::now();
            $log->save();

            return redirect()->route('clients.access', [$client->id])->with('message', 'Tilganger endret');
    }

    public function transfer($id)
    {
        $user = \Auth::user();
        $client = \App\Client::find($id);

        // Check if the user is the owner of the client
        $owner = $this->owner($user->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $coopusers = $client->user;

        $id = [];

        foreach ($coopusers as $coopuser) {
            $id[] = $coopuser->id;
        }

        $company = $user->company;
        $restofcompany = $company->user()->wherenotIn('id', $id)->get();

            return view('clients.transfer', compact('client', 'coopusers', 'restofcompany'));
    }

    public function transferform($clientid, $userid)
    {
        $loggedinuser = \Auth::user();
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }


        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

            return view('clients.transferform', compact('client', 'user'));
    }

    public function transferformpost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedinuser = \Auth::user();
        $client = \App\Client::find($data['client_id']);
        $user = \App\User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedinuser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $incompany = $this->incompany($loggedinuser->id, $user->id);
        if (!$incompany) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $client->user_id = $data['user_id'];
        $client->save();

        // Log the event!!
        $log = new \App\Transfer;

        $log->transferred_by = $loggedinuser->id;
        $log->transferred_to = $user->id;
        $log->client_id = $client->id;
        $log->reason = $data['reason'];
        $log->datetime = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.index', [$loggedinuser->id])->with('message', 'Klient overflyttet');

    }

    /**
     * View logs for a client
     *
     * @return \Illuminate\Http\Response
     */
    public function logs($id)
    {
        $user = \Auth::user();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        $client = \App\Client::find($id);

        if (\Auth::user()->role !== 2) {
            return redirect()->route('home')->with('message', 'You are not allowed to perform this operation');
        }

        //DECRYPT THE ENCRYPTED CLIENT INFO
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);

        return view('clients.logs', compact('client'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::User();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        $clients = $user->clients()->where('active', '1')->orderBy('lastname', 'ASC')->get();

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.index', compact('clients'));
    }

    public function coopindex()
    {
        $user = \Auth::User();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        $clients = $user->coopclients()->orderBy('lastname', 'ASC')->get();

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.coopindex', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateClientRequest $request)
    {
        $user = \Auth::User();

        $input = $request->all();
        $input2 = [];

        //saving the "born" date in separate variable
        $dateToSave = \Carbon\Carbon::createFromFormat('d.m.Y', $input['i2hmibi8a5']);

        $input2['user_id'] = $user->id;
        //firstname below
        $input2['firstname'] = Crypt::encrypt($input['dhjwhq3v7j']);
        //lastname below
        $input2['lastname'] = Crypt::encrypt($input['6x93mscfgo']);
        //born below
        $input2['born'] = $dateToSave->format('Y-m-d');
        //ssn below
        $input2['ssn'] = Crypt::encrypt($input['0rpk6x0uoe']);
        //civil status belos
        $input2['civil_status'] = Crypt::encrypt($input['g9npeyap1v']);
        //work status below
        $input2['work_status'] = Crypt::encrypt($input['vzjvte5v96']);
        //medication below
        $input2['medication'] = Crypt::encrypt($input['ulij51r2f9']);
        //street address below
        $input2['street_address'] = Crypt::encrypt($input['gvdd85c01k']);
        //postal code below
        $input2['postal_code'] = Crypt::encrypt($input['esrc80j3sc']);
        //city below
        $input2['city'] = Crypt::encrypt($input['753lqcsbk4']);
        //phone below
        $input2['phone'] = Crypt::encrypt($input['s7tjrdoliy']);
        //closest relative below
        $input2['closest_relative'] = Crypt::encrypt($input['3p1jm4zdyp']);
        //closest relative phone below
        $input2['closest_relative_phone'] = Crypt::encrypt($input['feucqwf7cx']);
        //children below
        $input2['children'] = Crypt::encrypt($input['7hvwzk7f7t']);
        //gp below
        $input2['gp'] = Crypt::encrypt($input['241i88imq9']);
        /*//individual plan below
        $input2['individual_plan'] = Crypt::encrypt($input['wlj5betr3c']);*/
        //other_info below
        $input2['other_info'] = Crypt::encrypt($input['cya9753ajt']);
        $input2['active'] = 1;

        $client = \App\Client::create($input2);
        return redirect()->route('clients.show', $client->id)->with('message', 'Klient opprettet');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        $client = \App\Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        //DECRYPT THE ENCRYPTED CLIENT INFO
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);
        $client->civil_status = Crypt::decrypt($client->civil_status);
        $client->work_status = Crypt::decrypt($client->work_status);
        $client->medication = Crypt::decrypt($client->medication);
        $client->street_address = Crypt::decrypt($client->street_address);
        $client->postal_code = Crypt::decrypt($client->postal_code);
        $client->city = Crypt::decrypt($client->city);
        $client->phone = Crypt::decrypt($client->phone);
        $client->closest_relative = Crypt::decrypt($client->closest_relative);
        $client->closest_relative_phone = Crypt::decrypt($client->closest_relative_phone);
        $client->children = Crypt::decrypt($client->children);
        $client->gp = Crypt::decrypt($client->gp);
        //$client->individual_plan = Crypt::decrypt($client->individual_plan);
        $client->other_info = Crypt::decrypt($client->other_info);

            return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = \Auth::user();

        if ($user->suspended !== "0000-00-00") {
            return redirect()->route('home')->with('message', 'Tilgangen din er begrenset på grunn av mangelfull betaling');
        }

        $client = \App\Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        //DECRYPT THE ENCRYPTED CLIENT INFO
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);
        $client->civil_status = Crypt::decrypt($client->civil_status);
        $client->work_status = Crypt::decrypt($client->work_status);
        $client->medication = Crypt::decrypt($client->medication);
        $client->street_address = Crypt::decrypt($client->street_address);
        $client->postal_code = Crypt::decrypt($client->postal_code);
        $client->city = Crypt::decrypt($client->city);
        $client->phone = Crypt::decrypt($client->phone);
        $client->closest_relative = Crypt::decrypt($client->closest_relative);
        $client->closest_relative_phone = Crypt::decrypt($client->closest_relative_phone);
        $client->children = Crypt::decrypt($client->children);
        $client->gp = Crypt::decrypt($client->gp);
        //$client->individual_plan = Crypt::decrypt($client->individual_plan);
        $client->other_info = Crypt::decrypt($client->other_info);

        return view('clients.edit', compact('client', 'user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UpdateClientRequest $request, $id)
    {
        $user = \Auth::user();
        $client = \App\Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

            $input = $request->all();

            // ENCRYPTION
        $input2['civil_status'] = Crypt::encrypt($input['g9npeyap1v']);
        $input2['work_status'] = Crypt::encrypt($input['vzjvte5v96']);
        $input2['medication'] = Crypt::encrypt($input['ulij51r2f9']);
        $input2['street_address'] = Crypt::encrypt($input['gvdd85c01k']);
        $input2['postal_code'] = Crypt::encrypt($input['esrc80j3sc']);
        $input2['city'] = Crypt::encrypt($input['753lqcsbk4']);
        $input2['phone'] = Crypt::encrypt($input['s7tjrdoliy']);
        $input2['closest_relative'] = Crypt::encrypt($input['3p1jm4zdyp']);
        $input2['closest_relative_phone'] = Crypt::encrypt($input['feucqwf7cx']);
        $input2['children'] = Crypt::encrypt($input['7hvwzk7f7t']);
        $input2['gp'] = Crypt::encrypt($input['241i88imq9']);
        $input2['other_info'] = Crypt::encrypt($input['cya9753ajt']);
        //$input2['individual_plan'] = $input['wlj5betr3c'];

        if ($request->has('0rpk6x0uoe')) {
            $input2['ssn'] = Crypt::encrypt($input['0rpk6x0uoe']);
        }

        if ($request->has('dhjwhq3v7j')) {
            $input2['firstname'] = Crypt::encrypt($input['dhjwhq3v7j']);
        }

        if ($request->has('6x93mscfgo')) {
            $input2['lastname'] = Crypt::encrypt($input['6x93mscfgo']);
        }

        if ($request->has('i2hmibi8a5')) {

            //saving the "born" date in separate variable
            $dateToSave = \Carbon\Carbon::createFromFormat('d-m-Y', $input['i2hmibi8a5']);

            $input2['born'] = $dateToSave->format('Y-m-d');

        }

            //UPDATE
            $client->update($input2);

            return redirect()->route('clients.show', [$id])->with('message', 'Personlig informasjon oppdatert');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
