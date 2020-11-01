<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Classes\golonka\bbcodeparser\src\BBCodeParser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomAuthTrait;
use App\Client;
use App\Template;
use App\Record;
use App\Readrecordlog;
use App\Changerecordlog;
use App\Signlog;
use PDF;


class RecordController extends Controller
{
    use CustomAuthTrait;

    // Only allow logged in users
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        $this->middleware('revalidate');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($clientId)
    {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'DESC')->get();

        return view('records.index', compact('client', 'records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientId)
    {
        $client = Client::find($clientId);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Get all template ID's for drop down list
        $templatesArr = $user->templates()->select('title', 'id')->get();
        $templates = [];

        foreach ($templatesArr as $template) {
          $templates[$template->id] = $template->title;
        }

        // Include the empty template shared by all
        $templates[4] = "Empty template";

        // Has the user set a favorite template?
        if (($user->favtemplate !== 0) || ($user->favtemplate !== null)) {
            $templateid = $user->favtemplate;
            $template = Template::find($templateid);
        }

        if (($user->favtemplate === 0) || ($user->favtemplate == null)) {
            // Set the empty template as default chosen in drop down if no template is defined by user
            $template = Template::find('4');
        }

        // DECRYPT DATA TO BE SHOWN
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);

        return view('records.create', compact('user', 'client', 'clientId', 'template', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateRecordRequest $request)
    {
        $user = Auth::user();
        $clientId = $request['client_id'];
        $client = Client::find($clientId);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $input = $request->all();
        $data['created_by'] = Auth::user()->id;
        // convert the date for mysql
        $data['app_date'] = date('Y-m-d', (strtotime($input['app_date'])));
        // ENCRYPT THE SENSITIVE DATA
        $data['title'] = Crypt::encrypt(strip_tags($input['title']));
        $data['content'] = Crypt::encrypt(strip_tags($input['content']));
        $data['oldid'] = 0;
        $data['category_id'] = 1;
        $data['client_id'] = $clientId;

        Record::create($data);

        return redirect()->route('clients.records.index', $input['client_id'])->with('message', 'Notat opprettet');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($clientId, $recordId)
    {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        $record = Record::find($recordId);
        if (!$record) {
            return redirect()->back()->with('message', 'No record found!');
        }

        $clientId = $record->client_id;

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $clientId);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        // PARSE THE BBCODE content
        $parser = new BBCodeParser();
        $record->content = $parser->parse($record->content);

        // Log the event in table 'readrecordlog'
        $log = new Readrecordlog();

        $log->read_by = $user->id;
        $log->client_id = $client->id;
        $log->record_id = $record->id;
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return view('records.show', compact('record', 'client'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printShow($clientId, $recordId)
    {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        $record = Record::find($recordId);

        $clientId = $record->client_id;

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $clientId);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        // PARSE THE BBCODE content
        $parser = new BBCodeParser();
        $record->content = $parser->parse($record->content);

        // Log the event in table 'readrecordlog'
        $log = new Readrecordlog();

        $log->read_by = $user->id;
        $log->client_id = $client->id;
        $log->record_id = $record->id;
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return view('records.printshow', compact('record', 'client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($clientId, $recordId)
    {
        $record = Record::find($recordId);
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        // If the record is signed, redirect to home page
        if ($record->signed_by !== null) {
            return redirect()->back()->with('message', 'Notatet er signert og kan ikke endres');
        }

        // DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        return view('records.edit', compact('client', 'record'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\CreateRecordRequest $request, $clientId, $recordId)
    {
        $user = Auth::user();
        $record = Record::find($recordId);

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record = Record::findOrFail($recordId);
        $oldRecord = Record::findOrFail($recordId);

        $input = $request->all();

        // ENCRYPT THE SENSITIVE DATA
        $data['title'] = Crypt::encrypt($input['title']);
        $data['content'] = Crypt::encrypt($input['content']);

        // convert the date for mysql
        $data['app_date'] = date('Y-m-d',(strtotime($input['app_date'])));

        $record->update($data);

        // Log the event in table 'readrecordlog'
        $log = new Changerecordlog;
        $log->created_by = $oldRecord->created_by;
        $log->changed_by = $user->id;
        $log->client_id = $clientId;
        $log->record_id = $record->id;
        $log->formertitle = $oldRecord->title;
        $log->newtitle = $record->title;
        $log->formercontent = $oldRecord->content;
        $log->newcontent = $record->content;
        $log->formerapp_date = $oldRecord->app_date;
        $log->newapp_date = $record->app_date;
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.records.show', [$clientId, $recordId])->with('message', 'Notat endret');
    }

    public function sign(Request $request)
    {
        $data = $request->all();
        $record = Record::find($data['record_id']);
        $user = Auth::user();

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record->signed_date = \Carbon\Carbon::now();
        $record->signed_by = $data['created_by'];
        // To not make the signing change the "updated_at" column
        $record->timestamps = false;
        $record->save();

        // Log the event in table 'signlog'
        $log = new Signlog;
        $log->client_id = $record->client->id;
        $log->record_id = $record->id;
        $log->signed_by = $user->id;
        $log->reason = "";
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.records.index', [$data['client_id']])->with('message', 'Notat signert');
    }


    public function unsignForm($clientId, $recordId)
    {
        $record = Record::find($recordId);
        $user = Auth::user();
        $client = Client::find($clientId);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        // Check if the record is signed
        if ($record->signed_by == null) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Notatet er ikke signert.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('records.unsignForm', compact('record', 'user', 'client'));
    }

    public function unsignFormPost(Requests\UnsignRequest $request)
    {
        $data = $request->all();
        $record = Record::find($data['record_id']);
        $client = Client::find($data['client_id']);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record->signed_date = null;
        $record->signed_by = null;
        // To not make the signing change the "updated_at" column
        $record->timestamps = false;
        $record->save();

        // Log the event in table 'signlog'
        $log = new Signlog;
        $log->client_id = $record->client->id;
        $log->record_id = $record->id;
        $log->unsigned_by = Auth::user()->id;
        $log->timestamp = \Carbon\Carbon::now();
        $log->reason = $data['reason'];
        $log->save();

        return redirect()->route('clients.records.index', [$data['client_id']])->with('message', 'Notat åpnet');
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewAll($clientId)
    {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'ASC')->get();

        // Load parser
        $parser = new BBCodeParser();

        foreach ($records as $record) {
            // Log the event in table 'readrecordlog'
            $log = new Readrecordlog();
            $log->read_by = $user->id;
            $log->client_id = $client->id;
            $log->record_id = $record->id;
            $log->timestamp = \Carbon\Carbon::now();
            $log->save();
        }

        return view('records.viewall', compact('records', 'client', 'parser'));
    }

    /**
     * Export all view of records to pdf.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportToPDF($clientId) {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'ASC')->get();

        // Load parser
        $parser = new BBCodeParser();

        // share data to view
        // view()->share('records', compact('records'));
        // view()->share('client', compact('client'));
        // view()->share('parser', compact('parser'));
        $pdf = PDF::loadView('records.viewAll', compact('records', 'client', 'parser'));

        // download PDF file with download method
        return $pdf->download('pdf_file.pdf');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printAll($clientId)
    {
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'ASC')->get();

        // Load parser
        $parser = new BBCodeParser();

        foreach ($records as $record) {
            // Log the event in table 'readrecordlog'
            $log = new Readrecordlog();
            $log->read_by = $user->id;
            $log->client_id = $client->id;
            $log->record_id = $record->id;
            $log->timestamp = \Carbon\Carbon::now();
            $log->save();
        }

        return view('records.printall', compact('records', 'client', 'parser'));
    }

    public function changeHistory($clientId, $recordId)
    {
        $record = Record::find($recordId);
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke skrevet dette notatet, og har derfor ikke tilgang');
        }

        // DECRYPT THE ENCRYPTED DATA FOR THE MAIN RECORD
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        // Load parser
        $parser = new BBCodeParser();

        // Find the earlier versions
        $earlierVersions = Changerecordlog::where('record_id', $recordId)->get();

        return view('records.changehistory', compact('client', 'record', 'parser', 'earlierVersions'));
    }

    public function changeHistoryVersion($clientId, $recordId, $changeRecordId)
    {
        $record = Changerecordlog::find($changeRecordId);
        $client = Client::find($clientId);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = Auth::user();

        // Check if the user is writer of the note
        $writer = $this->allowedVersionHistory($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke skrevet dette notatet, og har derfor ikke tilgang');
        }

        // DECRYPT THE ENCRYPTED DATA
        $record->formertitle = Crypt::decrypt($record->formertitle);
        $record->formercontent = Crypt::decrypt($record->formercontent);
        $record->newtitle = Crypt::decrypt($record->newtitle);
        $record->newcontent = Crypt::decrypt($record->newcontent);

        // Load parser
        $parser = new BBCodeParser();

        return view('records.changehistoryversion', compact('client', 'record', 'parser'));
    }

    public function move($clientId, $recordId, Request $request)
    {
        $user = Auth::user();
        if ($user->role === 2) {
            $search = strtolower($request->search);
            $record = Record::find($recordId);

            if (!$search) {
                $clients = Client::where('id', '!=', $clientId)->simplePaginate(30);
            } else {
                $clients = Client::where('id', $search)->simplePaginate(30);
            }

            $client = Client::find($clientId);
            $client->firstname = Crypt::decrypt($client->firstname);
            $client->lastname = Crypt::decrypt($client->lastname);

            return view('records.move', compact('client', 'record', 'clients', 'search'));
        }
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    public function movePost(Request $request, $clientId, $recordId, $receiverId)
    {
        $user = Auth::user();
        if ($user->role === 2) {
            $record = Record::find($recordId);
            $record->client_id = $receiverId;
            $record->save();

            return redirect()->route('clients.records.list', [$clientId]);
        }
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }
}
