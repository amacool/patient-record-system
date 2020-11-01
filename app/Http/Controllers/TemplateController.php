<?php

namespace App\Http\Controllers;

use App\Classes\golonka\bbcodeparser\src\BBCodeParser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Client;
use App\Category;
use App\Template;


class TemplateController extends Controller
{
    // Only allow logged in users
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        $this->middleware('revalidate');
    }

    // FIRST THE METHODS FOR THE RESOURCE CONTROLLER
    
    /**
     * Show a list of all templates for the logged-in-user
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $templates = $user->templates;
        return view('templates.index', compact('templates', 'user'));
    }

    /**
     * Show a page for creating a new template
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::select('title', 'id');
        return view('templates.create', compact('categories'));
    }

    /**
     * Store a newly created template
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateTemplateRequest $request)
    {
        $user = Auth::user();
        $input = $request->all();
        $data['title'] = $input['title'];
        $data['content'] = $input['content'];
        $data['category_id'] = $input['category_id'];
        $data['created_by'] = $user->id;
        Template::create($data);

        return redirect()->route('templates.create')->with('message', 'Mal opprettet');
    }

    /**
     * Show the contents of a specific template
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $template = Template::find($id);

        // If the user is the owner of the template or system admin, show the page
        if ($template->created_by === $user->id || $user->role === 2) {
            $template = Template::find($id);
            // PARSE THE BBCODE content
            $parser = new BBCodeParser();
            $template->content = $parser->parse($template->content);
            return view('templates.show', compact('template', 'user'));
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang.');
    }

    /**
     * Show the page for editing a specific template
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        $template = Template::find($id);

        // If the user is the owner of the template or system admin, show the page
        if ($template->created_by === $user->id || $user->role === 2) {
            return view('templates.edit', compact('template'));
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang.');
    }

    /**
     * Update a specific template in the database
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\CreateTemplateRequest $request, $id)
    {
        $user = Auth::user();
        $template = Template::findOrFail($id);

        // If the user is neither the owner of the template nor system admin, block
        if ($template->created_by !== $user->id && $user->role !== 2) {
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        $input = $request->all();
        $data['title'] = $input['title'];
        $data['content'] = $input['content'];
        $data['category_id'] = $input['category_id'];
        $template->update($data);

        return redirect()->route('templates.show', [$id])->with('message', 'Mal oppdatert');
    }

    /**
     * Delete a specific template
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $template = Template::find($id);

        // If the user is the owner of the template or system admin, delete the template
        if ($template->created_by === $user->id || $user->role === 2) {
            $template->delete();
            return redirect()->route('templates.index')->with('message', 'Mal slettet');
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang.');
    }
    
    // THEN CUSTOM METHODS
    
    /**
     * Set a favorite template that will be the default when creating new records
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function setFavorite(Request $request, $templateId)
    {
        $user = Auth::user();

        if ($user->favtemplate == $templateId) {
            $user->favtemplate = 0;
            $user->save();

            return redirect()->back()->with('message', 'Malen er fjernet som standard');
        }

        if ($user->favtemplate !== $templateId) {
            $user->favtemplate = $templateId;
            $user->save();

            return redirect()->back()->with('message', 'Malen brukes nå som standard når du oppretter nye notater');
        }
    }

    /**
     * When creating a new record, this method lets the user select a template and the contents
     * from this template will fill the CKEditor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function useTemplate(Request $request, $id)
    {
        $data = $request->all();
        $user = Auth::user();
        $client = Client::find($id);

        // DECRYPT DATA TO BE SHOWN
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);
        $client->ssn = Crypt::decrypt($client->ssn);

        $template = Template::find($data['template_id']);

        if ($template->id !== 4) {
            if ($template->created_by !== $user->id) {
                // If template does not belong to user, redirect to home page with warning
                return redirect('/')->with('message', 'Du har ikke tilgang.');
            }
        }

        // Get all template ID's for drop down list
        $templatesArr = $user->templates()->select('title', 'id', 'content')->get();
        $templates = [];

        foreach ($templatesArr as $templateItem) {
          $templates[$templateItem->id] = $templateItem->title;
        }

        // Include the empty template shared by all
        $templates[4] = "Empty template";

        return view('records.create', compact('user', 'client', 'template', 'templates'));
    }

    
}
