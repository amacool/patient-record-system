<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/loggedin', function () {
    return redirect()->route('clients.index');
});

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin')->name('loginpage');
Route::post('auth/login', 'Auth\AuthController@postLogin')->name('loginusers');
Route::get('auth/logout', 'Auth\AuthController@getLogout')->name('logout');
Route::get('auth/twofactor', 'Auth\AuthController@getTwofactor')->name('twofactor');
Route::post('auth/twofactor', 'Auth\AuthController@postTwofactor')->name('posttwofactor');

// Registration routes...
Route::get('admin/register', 'Auth\AuthController@getRegister')->name('registrationpage');
Route::post('admin/register', 'Auth\AuthController@postRegister')->name('registerusers');

/////ADMIN DIRECT FUNCTIONS
//Route to transfer DB
Route::get('dbtransfer/test', array('as' => 'dbtransfertest', 'uses' => 'UserController@transferall'));
//Route to get WP revisions for an old post
Route::get('wphistory/get', array('as' => 'wphistory', 'uses' => 'UserController@wpversionhistory'));


//Route to view all users
Route::get('users/index', array('as' => 'users.index', 'uses' => 'UserController@index'));
Route::get('users/inactiveindex', array('as' => 'users.inactiveindex', 'uses' => 'UserController@inactiveindex'));
Route::resource('companies', 'CompanyController');
//  Show index of clients that psychologists have access to through cooperation
Route::post('companies/{companyid}/users/{userid}/changetwofactor', array('as' => 'companies.users.changetwofactor', 'uses' => 'UserController@changetwofactor'));
//  Give user a payment warning
Route::post('companies/{companyid}/users/{userid}/paymentwarning', array('as' => 'companies.users.paymentwarning', 'uses' => 'UserController@paymentwarning'));
//  Give user a payment warning
Route::post('companies/{companyid}/users/{userid}/suspend', array('as' => 'companies.users.suspenduser', 'uses' => 'UserController@suspenduser'));
//  Give user a payment warning
Route::post('companies/{companyid}/users/{userid}/standardtitle', array('as' => 'companies.users.standardtitle', 'uses' => 'UserController@standardtitle'));
Route::post('companies/{companyid}/users/{userid}/secretquestion', array('as' => 'companies.users.secretquestion', 'uses' => 'UserController@secretquestion'));
Route::post('companies/{companyid}/users/{userid}/activatetoggle', array('as' => 'companies.users.activatetoggle', 'uses' => 'UserController@activatetoggle'));


//  Authy registration
Route::post('companies/{companyid}/users/{userid}/registerauthy', array('as' => 'companies.users.registerauthy', 'uses' => 'UserController@registerauthy'));
//  Authy delete user
Route::post('companies/{companyid}/users/{userid}/deleteauthy', array('as' => 'companies.users.deleteauthy', 'uses' => 'UserController@deleteauthy'));


Route::post('companies/{companyid}/users/{userid}/changecompany', array('as' => 'companies.users.changecompany', 'uses' => 'UserController@changecompany'));
Route::post('companies/{companyid}/users/{userid}/changerole', array('as' => 'companies.users.changerole', 'uses' => 'UserController@changerole'));
Route::post('companies/{companyid}/users/{userid}/changephone', array('as' => 'companies.users.changephone', 'uses' => 'UserController@changephone'));
Route::post('companies/{companyid}/users/{userid}/transfersinglewprecord', array('as' => 'companies.users.transfersinglewprecord', 'uses' => 'UserController@transfersinglewprecord'));
Route::post('companies/{companyid}/users/{userid}/transferclientrecords', array('as' => 'companies.users.transferclientrecords', 'uses' => 'UserController@transferclientrecords'));
Route::get('companies/{companyid}/users/{userid}/accessandtransferlogs', array('as' => 'companies.users.accessandtransferlogs', 'uses' => 'UserController@accessandtransferlogs'));
Route::get('companies/{companyid}/users/{userid}/accesslogs', array('as' => 'companies.users.accesslogs', 'uses' => 'UserController@accesslogs'));
Route::post('companies/{companyid}/users/{userid}/removethisuser', array('as' => 'companies.users.delete', 'uses' => 'UserController@deleteuser'));
Route::resource('companies.users', 'UserController');

//  Show index of clients that psychologists have access to through cooperation
Route::get('clients/cooperationaccess', array('as' => 'clients.coopindex', 'uses' => 'ClientController@coopindex'));
//  Show index of archived clients
Route::get('clients/archive', array('as' => 'clients.archiveindex', 'uses' => 'ClientController@archiveindex'));
//  Move client between active / archived
Route::post('clients/changestatus', array('as' => 'clients.archivemove', 'uses' => 'ClientController@archivemove'));
//  Show index of archived clients
Route::get('clients/{clientid}/logs', array('as' => 'clients.logs', 'uses' => 'ClientController@logs'));
//  Show access rights for specific client
Route::get('clients/{clientid}/access', array('as' => 'clients.access', 'uses' => 'ClientController@access'));
//  Show form for providing access to a specific psychologist to a client
Route::get('clients/{clientid}/access/{userid}', array('as' => 'clients.accessform', 'uses' => 'ClientController@accessform'));
//  Post form for providing access to a specific psychologist to a client
Route::post('clients/{clientid}/access/{userid}', array('as' => 'clients.accessformpost', 'uses' => 'ClientController@accessformpost'));
//  Show form for removing access to a specific psychologist to a client
Route::get('clients/{clientid}/access/{userid}/remove', array('as' => 'clients.removeaccessform', 'uses' => 'ClientController@removeaccessform'));
//  Post form for removing access to a specific psychologist to a client
Route::post('clients/{clientid}/access/{userid}/remove', array('as' => 'clients.removeaccessformpost', 'uses' => 'ClientController@removeaccessformpost'));
//  Show transfer possibilities for specific client
Route::get('clients/{clientid}/transfer', array('as' => 'clients.transfer', 'uses' => 'ClientController@transfer'));
//  Show form for transferring a client to another user
Route::get('clients/{clientid}/access/{userid}/transfer', array('as' => 'clients.transferform', 'uses' => 'ClientController@transferform'));
//  Post form for transferring a client to another user
Route::post('clients/{clientid}/access/{userid}/transfer', array('as' => 'clients.transferformpost', 'uses' => 'ClientController@transferformpost'));

Route::resource('clients', 'ClientController');

//  Choose which template to use when writing a client record
Route::post('clients/{client}/records/create', array('as' => 'templates.use', 'uses' => 'TemplateController@usetemplate'));

//  Set a favorite template for a user
Route::post('templates/{templateid}/setfavorite', array('as' => 'templates.setfavorite', 'uses' => 'TemplateController@setfavorite'));
Route::resource('templates', 'TemplateController');

//  View all records for a client
Route::get('clients/{clientid}/records/viewall', array('as' => 'clients.records.viewall', 'uses' => 'RecordController@viewall'));
//  Print view for all records for a client
Route::get('clients/{clientid}/records/printall', array('as' => 'clients.records.printall', 'uses' => 'RecordController@printall'));
//  Post form for signing a record
Route::post('clients/{clientid}/records/sign', array('as' => 'clients.records.sign', 'uses' => 'RecordController@sign'));
//  View change history for a record
Route::get('clients/{clientid}/records/{recordid}/changehistory', array('as' => 'clients.records.changehistory', 'uses' => 'RecordController@changehistory'));
//  View specific version
Route::get('clients/{clientid}/records/{recordid}/changehistory/{version}', array('as' => 'clients.records.changehistoryversion', 'uses' => 'RecordController@changehistoryversion'));
//  Print view for specific record
Route::get('clients/{clientid}/records/{recordid}/print', array('as' => 'clients.records.printshow', 'uses' => 'RecordController@printshow'));
//  Form for unsigning record
Route::get('clients/{clientid}/records/{recordid}/unsignform', array('as' => 'clients.records.unsignform', 'uses' => 'RecordController@unsignform'));
//  Form for unsigning record
Route::post('clients/{clientid}/records/{recordid}/unsignform', array('as' => 'clients.records.unsignformpost', 'uses' => 'RecordController@unsignformpost'));


Route::resource('clients.records', 'RecordController');


//  Show index of clients that psychologists have access to through cooperation
Route::get('clients/{clientid}/files/{filename}/download', array('as' => 'clients.files.download', 'uses' => 'FileController@download'));
Route::resource('clients.files', 'FileController');

