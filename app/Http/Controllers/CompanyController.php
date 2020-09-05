<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;


class CompanyController extends Controller
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
        $user = Auth::user();

        //If company admin, move directly to specific company page
        if ($user->role === 1) {
            $companyid = $user->company->id;
            return redirect()->route('companies.show', [$companyid]);
        }

        // If system admin, show list of all companies
        if ($user->role === 2) {
            $companies = Company::all();
            return view('companies.index', compact('companies'));
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        // If system admin, show page
        if ($user->role === 2) {
            return view('companies.create');
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\CreateCompanyRequest $request)
    {
        $input = $request->all();
        $user = Auth::user();

        // If system admin, show page
        if ($user->role === 2) {
            $data['name'] = $input['name'];
            $data['orgnr'] = $input['orgnr'];
            $data['seats'] = $input['seats'];
            Company::create($data);

            return redirect()->route('companies.create')->with('message', 'Firma opprettet');
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
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

        // If system admin, show page
        if ($user->role === 2) {
            $company = Company::find($id);
            return view('companies.show', compact('company'));
        }

        // If company admin for this specific company, show page
        if ($user->role === 1) {
            $company = Company::find($id);
            if ($company->id == $user->company_id) {
                return view('companies.show', compact('company'));
            }
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
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

        // If system admin, show page
        if ($user->role === 2) {
            $company = Company::find($id);
            return view('companies.edit', compact('company'));
        }

        // If company admin for this specific company, show page
        if ($user->role === 1) {
            $company = Company::find($id);
            if ($company->id == $user->company_id) {
                return view('companies.edit', compact('company'));
            }
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UpdateCompanyRequest $request, $id)
    {
        $company = Company::findOrFail($id);
        $data = $request->all();
        $user = Auth::user();

        // If system admin, show page
        if ($user->role === 2) {
            $input['seats'] = $data['seats'];
            $company->update($input);
            return redirect()->route('companies.show', [$id])->with('message', 'Firmainformasjon opprettet');
        }

        // If company admin for this specific company, show page
        if ($user->role === 1) {
            if ($company->id == $user->company_id) {
                $input['seats'] = $data['seats'];
                $company->update($input);
                return redirect()->route('companies.show', [$id])->with('message', 'Firmainformasjon opprettet');
            }
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }
}
