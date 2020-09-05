<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomAuthTrait;
use App\Client;
use App\Fileupload;


class FileController extends Controller
{
    use CustomAuthTrait;

    // Only allow logged in users
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('timeout');
        // There was an error while using revalidate middleware on the download function. Something with headers sent. Removed it.
        $this->middleware('revalidate', ['except' => ['download']]);
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
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        return view('files.index', compact('client'));
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

        //DECRYPT DATA TO BE SHOWN
        $client->firstname = Crypt::decrypt($client->firstname);
        $client->lastname = Crypt::decrypt($client->lastname);

        return view('files.create', compact('client', 'clientId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\UploadFileRequest $request, $clientId)
    {
        $this->validate($request, [
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $client = Client::find($clientId);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // How many files does this client have in database?
        $clientfiles = count($client->files);

        // Set the next filename as a number larger than this
        // Check if the filename already exists (if a record has been deleted manually from the database, this can happen)
        // If it exists, increment it and check again until the file does not exist anymore. Proceed with this filename (path)

        $incrementor = 1;
        do {
            $newNumber = $clientfiles + $incrementor;
            $path = base_path() . '/storage/docs/clientdocs/' . $clientId . '/' . $newNumber . '.pdf';
            $incrementor++;
        } while (File::exists($path));

        // Check if a file is uploaded
        if ($request->hasFile('file')) {

            // Get original file name
            $fileName = $request->file('file')->getClientOriginalName();

            // Get file extension
            $fileExt = $request->file('file')->getClientOriginalExtension();

            // Set new filename
            // Removing this, to force pdf-filename
            // $fileName = $newNumber . '.' . $fileExt;
            $fileName = $newNumber . '.pdf';

            // Get path based on client id
            // $path = base_path() . '/public/docs/clientdocs/' . $clientId;
            $path = base_path() . '/storage/docs/clientdocs/' . $clientId;

            // Make directory if it does not exist
           File::makeDirectory($path, $mode = 0777, true, true);

            // Move file to directory, with original file name
            $request->file('file')->move(
                $path, $fileName
            );

            // Get the path to the file
            $file = $path . '/' . $fileName;

            // Get the content of the file
            $content = File::get($file);

            // Encrypt the content
            $encryptedContent = Crypt::encrypt($content);

            // Replace the original content with the encrypted content
            File::put($file, $encryptedContent);

            // PROCESS FOR DECRYPTING
            // $content = File::get($file);
            // $decryptedcontent = Crypt::decrypt($content);
            // $newcontent = File::put($file, $decryptedcontent);

            // Insert the path to database
            $fileupload = new Fileupload;
            $fileupload->user_id = Auth::user()->id;
            $fileupload->client_id = $clientId;
            $fileupload->file = $fileName;
            $fileupload->description = $request->description;
            $fileupload->deleted = '0';
            $fileupload->save();
        }

        return redirect()->route('clients.files.index', $clientId)->with('message', 'Fil lastet opp');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
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
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($clientId, $fileName)
    {
        $client = Client::find($clientId);
        $user = Auth::user();

        // Check if the user is the owner of the client, or if he has access through cooperation.
        $ownerOrAccess = $this->ownerOrAccess($user->id, $client->id);
        if (!$ownerOrAccess) {
            // If not, redirect to home page with warning
            return redirect('/')->with('message', 'Du har ikke tilgang.');
        }

        // Get path based on client id
        // $path = base_path() . '/public/docs/clientdocs/' . $clientid;
        $path = base_path() . '/storage/docs/clientdocs/' . $clientId;
        $relativePath = 'clientdocs/' . $clientId;

        // Get full path to file
        $file = $path . '/' . $fileName;

        // Extract file extension from filename
        // Remove this to force pdf
        // $ext = pathinfo($file, PATHINFO_EXTENSION);
        $ext = "pdf";

        // Extract filename
        $number = pathinfo($file, PATHINFO_FILENAME);

        // Create title of decrypted file
        $decryptedName = $number . 'decrypted.' . $ext;
        // Set destination for decrypted file
        $newDestination = $path . '/' . $decryptedName;
        $relativeDestination = $relativePath . '/' . $decryptedName;

        // Copy original file
        File::copy($file, $newDestination);

        // PROCESS FOR DECRYPTING
        // Get contents of new file
        $content = File::get($newDestination);
        // Decrypt the contents of the new file
        $decryptedContent = Crypt::decrypt($content);
        // Put the decrypted content in the new file
        File::put($newDestination, $decryptedContent);

        $headers = [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; ' . $decryptedName,
        ];

        // FOR Å LASTE NED FILEN; OG DERETTER SLETTE DEN
        // return \Response::download($newdestination,  $decryptedName)->deleteFileAfterSend(true);

        // FOR Å SE FILEN I BROWSER (MEN FÅR DA IKKE SLETTET DEN FORELØPIG)
        // return Response::make(file_get_contents($newDestination), 200, $headers);

        return Storage::download($relativeDestination, $decryptedName, $headers);
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
