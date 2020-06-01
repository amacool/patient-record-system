<?php

namespace App\Http\Controllers;

use Golonka\BBCode\BBCodeParser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class RecordController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($clientid)
    {
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'DESC')->get();

        return view('records.index', compact('client', 'records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientid)
    {
        $client = \App\Client::find($clientid);
        $user = \Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Get all template ID's for drop down list
        $templates = $user->templates()->lists('title', 'id');
        // Include the empty template shared by all
        $templates[4] = "Empty template";

        // Has the user set a favorite template?
        if (($user->favtemplate !== 0) OR ($user->favtemplate !== null)) {
            $templateid = $user->favtemplate;
            $template = \App\Template::find($templateid);
        }

        if (($user->favtemplate == 0) OR ($user->favtemplate == null))
        {
            // Set the empty template as default chosen in drop down if no template is defined by user
            $template = \App\Template::find('4');
        }

        //DECRYPT DATA TO BE SHOWN
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('records.create', compact('user', 'client', 'clientid', 'template', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateRecordRequest $request)
    {
        $user = \Auth::user();
        $clientid = $request['client_id'];
        $client = \App\Client::find($clientid);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $input = $request->all();
        $input['created_by'] = \Auth::user()->id;
        //convert the date for mysql
        $input['app_date'] = date('Y-m-d',(strtotime($input['app_date'])));

        // ENCRYPT THE SENSITIVE DATA
        $input['title'] = Crypt::encrypt($input['title']);
        $input['content'] = strip_tags($input['content']);
        $input['content'] = Crypt::encrypt($input['content']);

        $record = \App\Record::create($input);

        return redirect()->route('clients.records.index', $input['client_id'])->with('message', 'Notat opprettet');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($clientid, $recordid)
    {
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        $record = \App\Record::find($recordid);

        $clientid = $record->client_id;

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $clientid);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        //DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        //PARSE THE BBCODE content
        $parser = new BBCodeParser();
        $record->content = $parser->parse($record->content);

        // Log the event in table 'readrecordlog'
        $log = new \App\Readrecordlog();

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
    public function printshow($clientid, $recordid)
    {
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        $record = \App\Record::find($recordid);

        $clientid = $record->client_id;

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $clientid);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        //DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        //PARSE THE BBCODE content
        $parser = new BBCodeParser();
        $record->content = $parser->parse($record->content);

            return view('records.printshow', compact('record', 'client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($clientid, $recordid)
    {
        $record = \App\Record::find($recordid);
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        //DECRYPT THE ENCRYPTED DATA
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        // If the record is signed, redirect to home page
        if ($record->signed_by !== null) {
            return redirect()->back()->with('message', 'Notatet er signert og kan ikke endres');
        }

            return view('records.edit', compact('client', 'record'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\CreateRecordRequest $request, $clientid, $recordid)
    {
        $user = \Auth::user();
        $client = \App\Client::find($clientid);
        $record = \App\Record::find($recordid);

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record = \App\Record::findOrFail($recordid);
        $oldrecord = \App\Record::findOrFail($recordid);

        $input = $request->all();

        // ENCRYPT THE SENSITIVE DATA
        $input['title'] = Crypt::encrypt($input['title']);
        $input['content'] = strip_tags($input['content']);
        $input['content'] = Crypt::encrypt($input['content']);

        //convert the date for mysql
        $input['app_date'] = date('Y-m-d',(strtotime($input['app_date'])));
        $user = \Auth::user();

        $record->update($input);

        // Log the event in table 'readrecordlog'
        $log = new \App\Changerecordlog;

        $log->created_by = $oldrecord->created_by;
        $log->changed_by = $user->id;
        $log->client_id = $clientid;
        $log->record_id = $record->id;
        $log->formertitle = $oldrecord->title;
        $log->newtitle = $record->title;
        $log->formercontent = $oldrecord->content;
        $log->newcontent = $record->content;
        $log->formerapp_date = $oldrecord->app_date;
        $log->newapp_date = $record->app_date;
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.records.show', [$clientid, $recordid])->with('message', 'Notat endret');
    }

    public function sign(Request $request)
    {
        $data = $request->all();
        $record = \App\Record::find($data['record_id']);
        $user = \Auth::user();

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record->signed_date = \Carbon\Carbon::now();
        $record->signed_by = $data['created_by'];
        // To not make the signing change the "updated_at" column
        $record->timestamps = false;
        $record->save();

        // Log the event in table 'signlog'
        $log = new \App\Signlog;

        $log->client_id = $record->client->id;
        $log->record_id = $record->id;
        $log->signed_by = \Auth::user()->id;
        $log->timestamp = \Carbon\Carbon::now();
        $log->save();

        return redirect()->route('clients.records.index', [$data['client_id']])->with('message', 'Notat signert');
    }


    public function unsignform($clientid, $recordid)
    {
        $record = \App\Record::find($recordid);
        $user = \Auth::user();
        $client = \App\Client::find($clientid);

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        // Check if the record is signed
        if ($record->signed_by == null) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Notatet er ikke signert.');
        }

        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('records.unsignform', compact('record', 'user', 'client'));
    }

    public function unsignformpost(Requests\UnsignRequest $request)
    {
        $data = $request->all();
        $record = \App\Record::find($data['record_id']);
        $client = \App\Client::find($data['client_id']);
        $user = \Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du kan kun endre notater du selv har skrevet');
        }

        $record->signed_date = '0000-00-00';
        $record->signed_by = null;
        // To not make the signing change the "updated_at" column
        $record->timestamps = false;
        $record->save();

        // Log the event in table 'signlog'
        $log = new \App\Signlog;

        $log->client_id = $record->client->id;
        $log->record_id = $record->id;
        $log->unsigned_by = \Auth::user()->id;
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewall($clientid)
    {
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'ASC')->get();

        //Load parser
        $parser = new BBCodeParser();

            foreach ($records as $record) {
                // Log the event in table 'readrecordlog'
                $log = new \App\Readrecordlog();

                $log->read_by = $user->id;
                $log->client_id = $client->id;
                $log->record_id = $record->id;
                $log->timestamp = \Carbon\Carbon::now();
                $log->save();
            }

            return view('records.viewall', compact('records', 'client', 'parser'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printall($clientid)
    {
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $owneroraccess = $this->owneroraccess($user->id, $client->id);
        if (!$owneroraccess) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
        }

        $records = $client->records()->orderBy('created_at', 'ASC')->get();

        //Load parser
        $parser = new BBCodeParser();

        return view('records.printall', compact('records', 'client', 'parser'));
    }

    public function changehistory($clientid, $recordid)
    {
        $record = \App\Record::find($recordid);
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is writer of the note
        $writer = $this->writer($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke skrevet dette notatet, og har derfor ikke tilgang');
        }

        //DECRYPT THE ENCRYPTED DATA FOR THE MAIN RECORD
        $record->title = Crypt::decrypt($record->title);
        $record->content = Crypt::decrypt($record->content);

        //Load parser
        $parser = new BBCodeParser();

        // Find the earlier versions
        $earlierversions = \App\Changerecordlog::where('record_id', $recordid)->get();

        return view('records.changehistory', compact('client', 'record', 'parser', 'earlierversions'));

    }

    public function changehistoryversion($clientid, $recordid, $changerecordid)
    {
        $record = \App\Changerecordlog::find($changerecordid);
        $client = \App\Client::find($clientid);
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $user = \Auth::user();

        // Check if the user is writer of the note
        $writer = $this->allowedversionhistory($user->id, $record->id);
        if (!$writer) {
            // If not, redirect to home page with warning
            return redirect()->route('home')->with('message', 'Du har ikke skrevet dette notatet, og har derfor ikke tilgang');
        }

        //DECRYPT THE ENCRYPTED DATA
        $record->formertitle = Crypt::decrypt($record->formertitle);
        $record->formercontent = Crypt::decrypt($record->formercontent);
        $record->newtitle = Crypt::decrypt($record->newtitle);
        $record->newcontent = Crypt::decrypt($record->newcontent);

        //Load parser
        $parser = new BBCodeParser();

            return view('records.changehistoryversion', compact('client', 'record', 'parser'));
    }
}
