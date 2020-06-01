<?php

namespace App\Http\Controllers;

use Golonka\BBCode\BBCodeParser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
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
    public function index()
    {
        $user = \Auth::user();
        $templates = $user->templates;
        return view('templates.index', compact('templates', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = \App\Category::lists('title', 'id');
        return view('templates.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateTemplateRequest $request)
    {
        $user = \Auth::user();
        $input = $request->all();
        $input['content'] = strip_tags($input['content']);
        $input['created_by'] = $user->id;
        $template = \App\Template::create($input);
        return redirect()->route('templates.create')->with('message', 'Mal opprettet');
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
        $template = \App\Template::find($id);

        // If the user is the owner of the template, show the page
        if ($template->created_by == $user->id) {

            $template = \App\Template::find($id);
            //PARSE THE BBCODE content
            $parser = new BBCodeParser();
            $template->content = $parser->parse($template->content);
            return view('templates.show', compact('template', 'user'));
        }

        // If the user is a system admin, show the page
        if ($user->role == 2) {

            $template = \App\Template::find($id);
            //PARSE THE BBCODE content
            $parser = new BBCodeParser();
            $template->content = $parser->parse($template->content);
            return view('templates.show', compact('template', 'user'));
        }

        // Else, redirect to home page with warning
        return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
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
        $template = \App\Template::find($id);

        // If the user is the owner of the template, show the page
        if ($template->created_by == $user->id) {
        return view('templates.edit', compact('template'));
        }

        // If user is system admin, allow
        if ($user->role == 2) {
            return view('templates.edit', compact('template'));
        }

        // Else, redirect to home page with warning
        return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setfavorite(Request $request, $templateid)
    {
        $user = \Auth::user();

        if ($user->favtemplate == $templateid) {
            $user->favtemplate = 0;
            $user->save();

            return redirect()->back()->with('message', 'Malen er fjernet som standard');
        }

        if ($user->favtemplat !== $templateid) {
            $user->favtemplate = $templateid;
            $user->save();

            return redirect()->back()->with('message', 'Malen brukes nå som standard når du oppretter nye notater');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\CreateTemplateRequest $request, $id)
    {
        $template = \App\Template::findOrFail($id);
        $data = $request->all();
        $data['content'] = strip_tags($data['content']);
        $template->update($data);

        return redirect()->route('templates.show', [$id])->with('message', 'Mal oppdatert');
    }

    /**
     * Use the template for writing a new client record
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function usetemplate(Request $request, $id)
    {
        $data = $request->all();
        $user = \Auth::user();
        $client = \App\Client::find($id);

        //DECRYPT DATA TO BE SHOWN
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        $template = \App\Template::find($data['template_id']);

        if ($template->id !== 4) {
            if ($template->created_by !== $user->id) {
                // If template does not belong to user, redirect to home page with warning
                return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
            }
        }

        // Get all template ID's for drop down list
        $templates = $user->templates()->lists('title', 'id');
        // Include the empty template shared by all
        $templates[4] = "Empty template";

        return view('records.create', compact('user', 'client', 'template', 'templates'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        $template = \App\Template::find($id);

        // If the user is the owner of the template, delete the template
        if ($template->created_by == $user->id) {
        $template->delete();
        return redirect()->route('templates.index')->with('message', 'Mal slettet');
        }

        // If the user is admin, allow
        if ($user->role == 2) {
            $template->delete();
            return redirect()->route('templates.index')->with('message', 'Mal slettet');
        }

        // Else, redirect to home page with warning
        return redirect()->route('home')->with('message', 'Du har ikke tilgang.');
    }
}
