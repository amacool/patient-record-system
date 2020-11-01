<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomAuthTrait;
use App\Http\Traits\SanitizeTrait;
use App\Company;
use App\Client;
use App\User;
use App\Transfer;
use App\Accessright;


class ClientController extends Controller
{
    use CustomAuthTrait;
    use SanitizeTrait;

    // Only allow logged in users
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        $this->middleware('revalidate');
    }

    /**
     * View clients for a logged in user
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::User();

        $this->checkSuspended($user->id, true);

        $ownClients = $user->clients;
        $coopClients = $user->coopClients;
        $clients = $ownClients->merge($coopClients);

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');
        return view('clients.index', compact('clients'));
    }

    /**
     * View active clients for a logged in user
     *
     * @return \Illuminate\Http\Response
     */
    public function activeIndex()
    {
        $user = Auth::User();

        $this->checkSuspended($user->id, true);

        $clients = $user->clients()->where('active', '1')->orderBy('lastname', 'ASC')->get();

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.activeIndex', compact('clients'));
    }

    /**
     * View archived clients for a logged in user
     *
     * @return \Illuminate\Http\Response
     */
    public function archiveIndex()
    {
        $user = Auth::User();

        $this->checkSuspended($user->id, true);

        $clients = $user->clients()->where('active', '0')->orderBy('lastname', 'ASC')->get();

        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.archiveIndex', compact('clients'));
    }

    /**
     * View own cooperation clients
     *
     * @return \Illuminate\Http\Response
     */
    public function coopIndex()
    {
        $user = Auth::User();

        $this->checkSuspended($user->id, true);

        $clients = $user->coopclients()->orderBy('lastname', 'ASC')->get();
        foreach ($clients as $client) {
            $client->lastname = Crypt::decrypt($client->lastname);
        }

        $clients = $clients->sortBy('lastname');

        return view('clients.coopIndex', compact('clients'));
    }

    /**
     * Role: Admin(all), Company Admin(own company)
     * View clients for any company
     *
     * @return \Illuminate\Http\Response
     */
    public function getClientsInCompany($companyId)
    {
        $user = Auth::User();
        $company = Company::find($companyId);

        $this->checkSuspended($user->id, true);

        if ($user->role === 2 || ($user->role === 1 && $user->company_id === $company->id)) {
            $users = $company->user;
            $user_ids = [];
            foreach ($users as $user) {
                array_push($user_ids, $user->id);
            }
            $clients = Client::whereIn('user_id', $user_ids)->get();

            foreach ($clients as $client) {
                $client->lastname = Crypt::decrypt($client->lastname);
            }

            $clients = $clients->sortBy('lastname');
            return view('companies.clients', compact('company', 'clients'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Role: Admin
     * View all clients in system
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllClients()
    {
        $user = Auth::User();

        $this->checkSuspended($user->id, true);

        if ($user->role === 2) {
            $clients = Client::simplePaginate(30);
            foreach ($clients as $client) {
                $client->lastname = Crypt::decrypt($client->lastname);
            }

            return view('clients.all', compact('clients'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Role: Admin(all), Company Admin(users in own company)
     * View active clients for any user
     *
     * @return \Illuminate\Http\Response
     */
    public function getActiveClientsForUser($companyId, $userId)
    {
        $loggedInUser = Auth::User();
        $user = User::find($userId);
        $company = Company::find($companyId);

        $this->checkSuspended($loggedInUser->id, true);

        if ($loggedInUser->role === 2 || ($loggedInUser->role === 1 && $loggedInUser->company_id === $user->company_id && $loggedInUser->company_id === $company->id)) {
            $clients = $user->clients()->where('active', 1)->get();

            foreach ($clients as $client) {
                $client->lastname = Crypt::decrypt($client->lastname);
            }

            $clients = $clients->sortBy('lastname');
            return view('users.activeClients', compact('company', 'user', 'clients'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Role: Admin(all), Company Admin(users in own company)
     * View archived clients for any user
     *
     * @return \Illuminate\Http\Response
     */
    public function getArchiveClientsForUser($companyId, $userId)
    {
        $loggedInUser = Auth::User();
        $company = Company::find($companyId);
        $user = User::find($userId);

        $this->checkSuspended($loggedInUser->id, true);

        if ($loggedInUser->role === 2 || ($loggedInUser->role === 1 && $loggedInUser->company_id === $user->company_id && $loggedInUser->company_id === $company->id)) {
            $clients = $user->clients()->where('active', 0)->get();

            foreach ($clients as $client) {
                $client->lastname = Crypt::decrypt($client->lastname);
            }

            $clients = $clients->sortBy('lastname');
            return view('users.archiveClients', compact('company', 'user', 'clients'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Role: Admin(all), Company Admin(users in own company)
     * View coop clients for any user
     *
     * @return \Illuminate\Http\Response
     */
    public function getCoopClientsForUser($companyId, $userId)
    {
        $loggedInUser = Auth::User();
        $company = Company::find($companyId);
        $user = User::find($userId);

        $this->checkSuspended($loggedInUser->id, true);

        if ($loggedInUser->role === 2 || ($loggedInUser->role === 1 && $loggedInUser->company_id === $user->company_id && $loggedInUser->company_id === $company->id)) {
            $coopClients = $user->coopClients;

            return view('users.coopClients', compact('company', 'user', 'coopClients'));
        }

        // Else, redirect to home page
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Move a client between active and archive status
     *
     * @return \Illuminate\Http\Response
     */
    public function archiveMove(Request $request)
    {
        $data = $request->all();
        $user = Auth::User();
        $client = Client::find($data['client_id']);

        // No matching client
        if (!$client) {
            return redirect('/')->with('message', 'Ingen matchende klient');
        }

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        if ($client->active === 0) {
            $client->active = 1;
            $client->save();
            return redirect()->route('clients.archive_index')->with('message', 'Klienten er nå aktiv');
        }

        if ($client->active === 1) {
            $client->active = 0;
            $client->save();
            return redirect()->route('clients.active_index')->with('message', 'Klienten er flyttet til arkivet');
        }
    }
    

    /**
     * Page for showing the access rights for a specific client
     *
     * @return \Illuminate\Http\Response
     */
    public function access($id)
    {
        $user = Auth::user();
        $client = Client::find($id);

        // No matching client
        if (!$client) {
            return redirect('/')->with('message', 'Ingen matchende klient');
        }

        // Check if the user is the owner of the client
        $owner = $this->owner($user->id, $client->id);
        if (!$owner && ($user->role !== 1 || $user->company_id !== $client->owner->company_id)) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $coopusers = $client->user;

        $id = [];

        foreach ($coopusers as $coopuser) {
            $id[] = $coopuser->id;
        }

        $company = $user->company;

        if ($user->role === 2) {
            $otherUsers = User::wherenotIn('id', $id)->get();
        } else {
            $otherUsers = $company->user()->wherenotIn('id', $id)->get();
        }

        return view('clients.access', compact('client', 'coopusers', 'otherUsers'));
    }

    public function accessForm($clientId, $userId)
    {
        $loggedInUser = Auth::user();
        $user = User::find($userId);
        $client = Client::find($clientId);

        // No matching client
        if (!$client) {
            return redirect('/')->with('message', 'Ingen matchende klient');
        }

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner && ($loggedInUser->role !== 1 || $loggedInUser->company_id !== $client->owner->company_id)) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $inCompany = $this->inCompany($loggedInUser->id, $user->id);
        if (!$inCompany) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('clients.accessForm', compact('client', 'user'));
    }

    public function accessFormPost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedInUser = Auth::user();
        $client = Client::find($data['client_id']);
        $user = User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner && ($loggedInUser->role !== 1 || $loggedInUser->company_id !== $client->owner->company_id)) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $inCompany = $this->inCompany($loggedInUser->id, $user->id);
        if (!$inCompany) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->user()->attach($user->id);

        // Log the event in table 'accessRights'
        $log = new Accessright;

        $log->given_by = $loggedInUser->id;
        $log->user_id = $user->id;
        $log->client_id = $client->id;
        $log->reason = $data['reason'];
        $log->datetime = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.access', [$client->id])->with('message', 'Tilganger endret');
    }

    public function removeAccessForm($clientId, $userId)
    {
        $loggedInUser = Auth::user();
        $user = User::find($userId);
        $client = Client::find($clientId);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner && ($loggedInUser->role !== 1 || $loggedInUser->company_id !== $client->owner->company_id)) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $isCoopClient = $this->cooperativeAccess($userId, $clientId);
        if (!$isCoopClient) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('clients.removeaccessform', compact('client', 'user'));
    }

    public function removeAccessFormPost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedInUser = Auth::user();
        $client = Client::find($data['client_id']);
        $user = User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner && ($loggedInUser->role !== 1 || $loggedInUser->company_id !== $client->owner->company_id)) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->user()->detach($user->id);

        // Log the event in table 'accessRights'
        $log = new Accessright;

        $log->revoked_by = $loggedInUser->id;
        $log->user_id = $user->id;
        $log->client_id = $client->id;
        $log->reason = $data['reason'];
        $log->datetime = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.access', [$client->id])->with('message', 'Tilganger endret');
    }

    public function transfer($id)
    {
        $user = Auth::user();
        $client = Client::find($id);

        // Check if the user is the owner of the client
        $owner = $this->owner($user->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $coopusers = $client->user;

        $id = [];

        foreach ($coopusers as $coopuser) {
            $id[] = $coopuser->id;
        }

        $company = $user->company;
        $restUsers = $company->user()->wherenotIn('id', $id)->get();
        if ($user->role === 2) {
            $restUsers = User::all()->whereNotIn('id', $id);
        }

        return view('clients.transfer', compact('client', 'coopusers', 'restUsers'));
    }

    public function transferForm($clientId, $userId)
    {
        $loggedInUser = Auth::user();
        $user = User::find($userId);
        $client = Client::find($clientId);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $inCompany = $this->inCompany($loggedInUser->id, $user->id);
        if (!$inCompany && $loggedInUser->role !== 2) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('clients.transferform', compact('client', 'user'));
    }

    public function transferFormPost(Requests\ProvideAccessRequest $request)
    {
        $data = $request->all();
        $loggedInUser = Auth::user();
        $client = Client::find($data['client_id']);
        $user = User::find($data['user_id']);

        // Check if the user is the owner of the client
        $owner = $this->owner($loggedInUser->id, $client->id);
        if (!$owner) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the other user is in loggedinusers company
        $inCompany = $this->inCompany($loggedInUser->id, $user->id);
        if (!$inCompany && $loggedInUser->role !== 2) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $client->user_id = $data['user_id'];
        $client->save();

        // Log the event!!
        $log = new Transfer;
        $log->transferred_by = $loggedInUser->id;
        $log->transferred_to = $user->id;
        $log->client_id = $client->id;
        $log->reason = $data['reason'];
        $log->datetime = \Carbon\Carbon::now();
        $log->save();

        if ($loggedInUser->role === 2) {
            return redirect()->route('clients.transfer', [$client->id])->with('message', 'Klient overflyttet');
        } else {
            return redirect()->route('clients.index', [$loggedInUser->id])->with('message', 'Klient overflyttet');
        }
    }

    /**
     * Role: Admin
     * View logs for a client
     *
     * @return \Illuminate\Http\Response
     */
    public function logs($id)
    {
        $user = Auth::user();

        $this->checkSuspended($user->id, true);

        $client = Client::find($id);

        if (Auth::user()->role !== 2) {
            return redirect('/')->with('message', 'You are not allowed to perform this operation');
        }

        // DECRYPT THE ENCRYPTED CLIENT INFO
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);

        return view('clients.logs', compact('client'));
    }


    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        $this->checkSuspended($user->id, true);

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
        $user = Auth::User();

        $input = $request->all();

        // saving the "born" date in separate variable
        $dateToSave = \Carbon\Carbon::createFromFormat('d.m.Y', $input['i2hmibi8a5']);

        $input2['user_id'] = $user->id;
        // firstname below
        $input2['firstname'] = Crypt::encrypt($input['dhjwhq3v7j']);
        // lastname below
        $input2['lastname'] = Crypt::encrypt($input['6x93mscfgo']);
        // born below
        $input2['born'] = $dateToSave->format('Y-m-d');
        // ssn below
        $input2['ssn'] = Crypt::encrypt($input['0rpk6x0uoe']);
        // civil status belos
        $input2['civil_status'] = Crypt::encrypt($input['g9npeyap1v']);
        // work status below
        $input2['work_status'] = Crypt::encrypt($input['vzjvte5v96']);
        // medication below
        $input2['medication'] = Crypt::encrypt($input['ulij51r2f9']);
        // street address below
        $input2['street_address'] = Crypt::encrypt($input['gvdd85c01k']);
        // postal code below
        $input2['postal_code'] = Crypt::encrypt($input['esrc80j3sc']);
        // city below
        $input2['city'] = Crypt::encrypt($input['753lqcsbk4']);
        // phone below
        $input2['phone'] = Crypt::encrypt($input['s7tjrdoliy']);
        // closest relative below
        $input2['closest_relative'] = Crypt::encrypt($input['3p1jm4zdyp']);
        // closest relative phone below
        $input2['closest_relative_phone'] = Crypt::encrypt($input['feucqwf7cx']);
        // children below
        $input2['children'] = Crypt::encrypt($input['7hvwzk7f7t']);
        // gp below
        $input2['gp'] = Crypt::encrypt($input['241i88imq9']);

        // individual plan below
        // $input2['individual_plan'] = Crypt::encrypt($input['wlj5betr3c']);

        // other_info below
        $input2['other_info'] = Crypt::encrypt($input['cya9753ajt']);
        $input2['active'] = 1;
        $input2['oldid'] = 0;
        $input2['individual_plan'] = '';

        $client = Client::create($input2);
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
        $user = Auth::user();

        $this->checkSuspended($user->id, true);

        $client = Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // DECRYPT THE ENCRYPTED CLIENT INFO
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
        // $client->individual_plan = Crypt::decrypt($client->individual_plan);
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
        $user = Auth::user();

        $this->checkSuspended($user->id, true);

        $client = Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // DECRYPT THE ENCRYPTED CLIENT INFO
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
        // $client->individual_plan = Crypt::decrypt($client->individual_plan);
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
        $user = Auth::user();
        $client = Client::find($id);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $prevSSN = Crypt::decrypt($client->ssn);
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
        // $input2['individual_plan'] = $input['wlj5betr3c'];

        if ($request->has('0rpk6x0uoe') && ($user->role === 2 || $prevSSN === '11111')) {
            $input2['ssn'] = Crypt::encrypt($input['0rpk6x0uoe']);
        }

        if ($request->has('dhjwhq3v7j') && $user->role === 2) {
            $input2['firstname'] = Crypt::encrypt($input['dhjwhq3v7j']);
        }

        if ($request->has('6x93mscfgo') && $user->role === 2) {
            $input2['lastname'] = Crypt::encrypt($input['6x93mscfgo']);
        }

        if ($request->has('i2hmibi8a5') && $user->role === 2) {
            // saving the "born" date in separate variable
            $dateToSave = \Carbon\Carbon::createFromFormat('d.m.Y', $input['i2hmibi8a5']);
            $input2['born'] = $dateToSave->format('Y-m-d');
        }

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
    }
}
