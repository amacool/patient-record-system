<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Classes\golonka\bbcodeparser\src\BBCodeParser;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Company;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App;

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
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $user = Auth::user();

        // If system admin, show page
        if ($user->role === 2) {
            $this->validate($request, [
                'name' => ['required', Rule::unique('companies')->ignore($company->id), 'max:255'],
                'orgnr' => ['required', 'Numeric'],
                'seats' => ['required', 'Numeric'],
            ], [
                'name.required' => 'Du må oppgi et firmanavn',
                'name.max'  => 'Firmanavnet kan ikke være lengre enn 255 tegn',
                'name.unique' => 'Firmanavnet er allerede i registeret',
                'orgnr.required' => 'Du må oppgi organisasjonsnummer',
                'orgnr.numeric' => 'Organisasjonsnummeret må oppgis med siffer',
                'seats.required' => 'Du må oppgi antall brukere',
                'seats.numeric' => 'Antall brukere må oppgis med siffer',
            ]);
            $data = $request->all();
            $input['name'] = $data['name'];
            $input['orgnr'] = $data['orgnr'];
            $input['seats'] = $data['seats'];
            $company->update($input);
            return redirect()->route('companies.show', [$id])->with('message', 'Firmainformasjon opprettet');
        }

        // If company admin for this specific company, show page
        if ($user->role === 1) {
            if ($company->id == $user->company_id) {
                $this->validate($request, [
                    'seats' => ['required', 'Numeric'],
                ], [
                    'seats.required' => 'Du må oppgi antall brukere',
                    'seats.numeric' => 'Antall brukere må oppgis med siffer',
                ]);
                $data = $request->all();
                $input['seats'] = $data['seats'];
                $company->update($input);
                return redirect()->route('companies.show', [$id])->with('message', 'Firmainformasjon opprettet');
            }
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Show the form for exporting companies.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportCompanyDataForm($id)
    {
        $user = Auth::user();
        // If system admin, show page
        if ($user->role === 2) {
            $company = Company::find($id);
            return view('companies.export', ['company' => $company, 'exportExist' => false]);
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Export data for a specific company.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportCompanyData(Request $request, $id)
    {
        $loggedInUser = Auth::user();

        set_time_limit(0);

        // If system admin, export data
        if ($loggedInUser->role === 2) {
            $company = Company::find($id);
            $checkedArray = $request->except(['_token']);
            $userIds = [];
            $exportCsv = false;
            $exportUploadedPdf = false;
            $exportRecordsPdf = false;

            foreach ($checkedArray as $key => $value) {
                if (strpos($key, "check_") !== false) {
                    array_push($userIds, $value);
                } else if (strpos($key, "export_csv") !== false && $value === 'on') {
                    $exportCsv = true;
                } else if (strpos($key, "export_uploaded_pdf") !== false && $value === 'on') {
                    $exportUploadedPdf = true;
                } else if (strpos($key, "export_records_pdf") !== false && $value === 'on') {
                    $exportRecordsPdf = true;
                }
            }

            $exportPdf = $exportUploadedPdf || $exportRecordsPdf;

            if (!$exportCsv && !$exportPdf) {
                return redirect("companies/$id/exportForm")->with('downloadLink', "company_data_$id");
            }

            $users = $company->user->whereIn('id', $userIds);

            $path = str_ireplace('\\', '/', base_path() . "/storage/docs/download");
            if (!file_exists($path)) {
                mkdir($path);
            }

            $companyPath = $path . '/' . $id;

            $companyFile = $companyPath . ($exportPdf ? '/csv' : '') . '/company.csv';
            $usersFile = $companyPath . ($exportPdf ? '/csv' : '') . '/users.csv';
            $clientsFile = $companyPath . ($exportPdf ? '/csv' : '') . '/clients.csv';
            $recordsFile = $companyPath . ($exportPdf ? '/csv' : '') . '/records.csv';

            if (file_exists($companyPath)) {
                File::deleteDirectory($companyPath);
            }
            mkdir($companyPath);
            if ($exportPdf && $exportCsv) {
                mkdir($companyPath . '/csv');
            }

            if ($exportCsv) {
                $file = fopen($companyFile, 'a');
                $company2 = $company->toArray();

                unset($company2['seats']);
                unset($company2['created_at']);
                unset($company2['updated_at']);
                unset($company2['user']);

                fputcsv($file,$company2, "|");
                fclose($file);
            }

            foreach ($users as $user) {
                if ($exportCsv) {
                    $file2 = fopen($usersFile, 'a');

                    $user2 = $user->toArray();
                    $user3['id'] = $user2['id'];
                    $user3['name'] = $user2['name'];
                    $user3['company_id'] = $user2['company_id'];

                    fputcsv($file2,$user3, "|");
                    fclose($file2);

                    foreach ($user->clients as $client) {
                        $file3 = fopen($clientsFile, 'a');

                        $client2 = $client->toArray();

                        unset($client2['oldid']);
                        unset($client2['civil_status']);
                        unset($client2['work_status']);
                        unset($client2['medication']);
                        unset($client2['phone']);
                        unset($client2['closest_relative']);
                        unset($client2['closest_relative_phone']);
                        unset($client2['children']);
                        unset($client2['gp']);
                        unset($client2['individual_plan']);
                        unset($client2['other_info']);
                        unset($client2['created_at']);
                        unset($client2['updated_at']);

                        $client2['firstname'] = Crypt::decrypt($client2['firstname']);
                        $client2['lastname'] = Crypt::decrypt($client2['lastname']);
                        $client2['ssn'] = Crypt::decrypt($client2['ssn']);
                        $client2['street_address'] = Crypt::decrypt($client2['street_address']);
                        $client2['postal_code'] = Crypt::decrypt($client2['postal_code']);
                        $client2['city'] = Crypt::decrypt($client2['city']);

                        fputcsv($file3,$client2, "|");
                        fclose($file3);

                        foreach ($client->records as $record) {
                            $file4 = fopen($recordsFile, 'a');

                            $record2 = $record->toArray();
                            $record2['title'] = Crypt::decrypt($record2['title']);
                            $record2['content'] = Crypt::decrypt($record2['content']);

                            // IF YOU WANT TO EXPORT THE CONTENT WITH HTML-TAGS RATHER THAN BBCode, uncomment the below
                            // $parser = new BBCodeParser();
                            // $record2['content'] = $parser->parse($record2['content']);

                            unset($record2['oldid']);
                            unset($record2['category_id']);

                            // null was not recognized as a value here when importing, need to set a timestamp
                            if ($record2['signed_date'] == null) {
                                $record2['signed_date'] = '0000-00-00 00:00:00';
                            }

                            fputcsv($file4,$record2, "|");
                            fclose($file4);
                        }
                    }
                }

                // decrypt, export files
                if ($exportPdf) {
                    $userPath = $companyPath . '/user_' . $user->id;
                    // export decrypted pdf files
                    $basePath = base_path() . '/storage/docs/clientdocs/';
                    $parser = new BBCodeParser();

                    $recordsFolder = $userPath . '/records';
                    $filesFolder = $userPath . '/files';

                    foreach ($user->clients as $client) {
                        // export uploaded pdfs
                        if ($exportUploadedPdf) {
                            $originalDirPath = $basePath . $client->id;

                            if (File::exists($originalDirPath)) {
                                if (!file_exists($userPath)) {
                                    mkdir($userPath);
                                }

                                if (!file_exists($filesFolder)) {
                                    mkdir($filesFolder);
                                }

                                $subFolder = $filesFolder . '/' . $client->id;
                                if (!file_exists($subFolder)) {
                                    mkdir($subFolder);
                                }

                                $files = File::files($originalDirPath);

                                foreach ($files as $file) {

                                    if (!strpos($file->getBasename(), 'decrypted')) {
                                        $content = File::get($file);
                                        // decrypt the contents of the new file
                                        $decryptedContent = Crypt::decrypt($content);
                                        // put the decrypted content in the new file
                                        File::put($subFolder . '/decrypted-' . $file->getFilename(), $decryptedContent);
                                    }
                                }
                            }
                        }
                        // export pdfs with client info and records
                        if ($exportRecordsPdf) {
                            if (!file_exists($userPath)) {
                                mkdir($userPath);
                            }
                            if (!file_exists($recordsFolder)) {
                                mkdir($recordsFolder);
                            }

                            // create a PDF containing all records for a client
                            $recordContent = "";
                            foreach ($client->records as $record) {
                                $recordContent .= "
                                <div style='border: 1px solid #ddd; margin-bottom: 20px; border-radius: 4px'>
                                    <div class='panel-heading' style='background: #f5f5f5; color: #333333; border-bottom: 1px solid #ddd; padding: 10px 15px'>
                                        \"" . Crypt::decrypt($record->title) . "\", forfatter " . $record->user->name . " (opprettet " . $record->created_at->format('d/m/Y') . ")
                                        <span style='float: right'>Avtaledato ". ($record->app_date->format('d/m/Y') == '30/11/-0001' ? 'ikke angitt' : $record->app_date->format('d/m/Y')) ."</span>
                                        <br/>Notatet ble sist oppdatert " . $record->updated_at->format('d/m/Y') . " av " . $record->user->name . "
                                        <span style='float: right'>"
                                    . ($record->signed_by == null ? 'Ikke signert' : 'Signert ' . $record->signed_date->format('d/m/Y') . ' av ' . $record->user->name) .
                                    "</span>
                                    </div>
                                    <div class='panel-body' style='padding: 15px;'>" . $parser->parse(Crypt::decrypt($record->content)) . "</div>
                                </div>
                            ";
                            }

                            $pdf = App::make('dompdf.wrapper');
                            $html = "
                            <div class='col-md-9 col-md-offset-3'>
                                <h3>Alle notater for <strong>" . $parser->parse(Crypt::decrypt($client->firstname)) . " " . $parser->parse(Crypt::decrypt($client->lastname)) . "</strong> (født " . $client->born->format('d-m-Y') . ")
                                </h3>
                                <hr />
                                $recordContent
                            </div>
                        ";
                            $pdf->loadHTML($html)->save($recordsFolder . '/'. $client->id .'.pdf');
                        }
                    }
                }
            }

            if ($exportPdf) {
                // export to a single zip file
                $rootPath = realpath($companyPath);
                $zipFilename = "company_data_$id.zip";
                $downloadPath = base_path() . '/storage/docs/download/';

                // initialize archive object
                $zip = new ZipArchive();
                $zip->open($downloadPath.$zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                // create recursive directory iterator
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($rootPath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    // skip directories (they would be added automatically)
                    if (!$file->isDir()) {
                        // get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($rootPath) + 1);

                        // add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                // zip archive will be created only after closing object
                $zip->close();
            }

            return redirect("companies/$id/exportForm")->with('downloadLink', "company_data_$id");
        }

        // Else, redirect to home page with warning
        return redirect('/')->with('message', 'Du har ikke tilgang');
    }

    /**
     * Download data for specific company.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadCompanyData($clientId, $file)
    {
        $loggedInUser = Auth::user();

        // If system admin, export data
        if ($loggedInUser->role === 2) {
            $ext = '.zip';
            $fileName =  $file . $ext;
            $headers = [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'inline; ' . $fileName,
            ];
            return Storage::download("download/$fileName", $fileName, $headers);
        }
    }
}
