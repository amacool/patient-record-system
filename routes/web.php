<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');

Route::get('/logged-in', function () {
  return redirect()->route('clients.index');
});


// Authentication routes...
Route::prefix('auth')->group(function () {
  Route::get('login', 'Auth\LoginController@getLogin')->name('login-page');
  Route::post('login', 'Auth\LoginController@postLogin')->name('login-users')->middleware("throttle:5,1");

  Route::get('logout', 'Auth\LoginController@getLogout')->name('logout');

  Route::get('two-factor', 'Auth\LoginController@getTwoFactor')->name('two-factor');
  Route::post('two-factor', 'Auth\LoginController@postTwoFactor')->name('post-two-factor');
});


// Registration routes...
Route::prefix('admin')->group(function () {
  Route::get('register', 'Auth\RegisterController@getRegister')->name('register-page');
  Route::post('register', 'Auth\RegisterController@postRegister')->name('register-users');
});


// ADMIN DIRECT FUNCTIONS
// Route to transfer DB
Route::get('dbtransfer/test', array('as' => 'db_transfer_test', 'uses' => 'UserController@transferAll'));
// Route to get WP revisions for an old post
Route::get('wphistory/get', array('as' => 'wphistory', 'uses' => 'UserController@wpVersionHistory'));


// Route to view all users
Route::prefix('users')->group(function () {
  Route::get('index', array('as' => 'users.index', 'uses' => 'UserController@index'));
  Route::get('inactive-index', array('as' => 'users.inactive_index', 'uses' => 'UserController@inactiveIndex'));
});


Route::prefix('companies')->group(function () {
  // Show index of clients that psychologists have access to through cooperation
  Route::post('{companyId}/users/{userId}/change-two-factor', array('as' => 'companies.users.change_two_factor', 'uses' => 'UserController@changeTwoFactor'));
  // Give user a payment warning
  Route::post('{companyId}/users/{userId}/payment-warning', array('as' => 'companies.users.payment_warning', 'uses' => 'UserController@paymentWarning'));
  // Give user a payment warning
  Route::post('{companyId}/users/{userId}/suspend', array('as' => 'companies.users.suspend_user', 'uses' => 'UserController@suspendUser'));
  // Give user a payment warning
  Route::post('{companyId}/users/{userId}/standard-title', array('as' => 'companies.users.standard_title', 'uses' => 'UserController@standardTitle'));
  Route::post('{companyId}/users/{userId}/secret-question', array('as' => 'companies.users.secret_question', 'uses' => 'UserController@secretQuestion'));
  Route::post('{companyId}/users/{userId}/activate-toggle', array('as' => 'companies.users.activate_toggle', 'uses' => 'UserController@activateToggle'));

  // Authy registration
  Route::post('{companyId}/users/{userId}/register-authy', array('as' => 'companies.users.register_authy', 'uses' => 'UserController@registerAuthy'));
  // Authy delete user
  Route::post('{companyId}/users/{userId}/delete-authy', array('as' => 'companies.users.delete_authy', 'uses' => 'UserController@deleteAuthy'));
  
  Route::post('{companyId}/users/{userId}/change-company', array('as' => 'companies.users.change_company', 'uses' => 'UserController@changeCompany'));
  Route::post('{companyId}/users/{userId}/change-role', array('as' => 'companies.users.change_role', 'uses' => 'UserController@changeRole'));
  Route::post('{companyId}/users/{userId}/change-phone', array('as' => 'companies.users.change_phone', 'uses' => 'UserController@changePhone'));
  Route::post('{companyId}/users/{userId}/transfer-single-wprecord', array('as' => 'companies.users.transfer_single_wprecord', 'uses' => 'UserController@transferSingleWprecord'));
  Route::post('{companyId}/users/{userId}/transfer-client-records', array('as' => 'companies.users.transfer_client_records', 'uses' => 'UserController@transferClientRecords'));
  Route::get('{companyId}/users/{userId}/access-transfer-logs', array('as' => 'companies.users.access_transfer_logs', 'uses' => 'UserController@accessAndTransferLogs'));
  Route::get('{companyId}/users/{userId}/access-logs', array('as' => 'companies.users.access_logs', 'uses' => 'UserController@accessLogs'));
  Route::post('{companyId}/users/{userId}/remove-this-user', array('as' => 'companies.users.delete', 'uses' => 'UserController@deleteUser'));
});
Route::resource('companies', 'CompanyController');
Route::resource('companies.users', 'UserController');


Route::prefix('clients')->group(function () {
  // Show index of clients that psychologists have access to through cooperation
  Route::get('cooperation-access', array('as' => 'clients.coop_index', 'uses' => 'ClientController@coopIndex'));
  // Show index of archived clients
  Route::get('archive', array('as' => 'clients.archive_index', 'uses' => 'ClientController@archiveIndex'));
  // Move client between active / archived
  Route::post('change-status', array('as' => 'clients.archive_move', 'uses' => 'ClientController@archiveMove'));
  // Remove Client
  // Route::post('remove', array('as' => 'clients.archive_remove', 'uses' => 'ClientController@archiveRemove'));
  // Create client
  Route::get('create', array('as' => 'clients.create', 'uses' => 'ClientController@create'));
  // Show index of clients
  Route::get('{clientId}', array('as' => 'clients.show', 'uses' => 'ClientController@show'));
  // Show index of archived clients
  Route::get('{clientId}/logs', array('as' => 'clients.logs', 'uses' => 'ClientController@logs'));
  // Show access rights for specific client
  Route::get('{clientId}/access', array('as' => 'clients.access', 'uses' => 'ClientController@access'));
  // Show form for providing access to a specific psychologist to a client
  Route::get('{clientId}/access/{userId}', array('as' => 'clients.access_form', 'uses' => 'ClientController@accessForm'));
  // Post form for providing access to a specific psychologist to a client
  Route::post('{clientId}/access/{userId}', array('as' => 'clients.access_form_post', 'uses' => 'ClientController@accessFormPost'));
  // Show form for removing access to a specific psychologist to a client
  Route::get('{clientId}/access/{userId}/remove', array('as' => 'clients.remove_access_form', 'uses' => 'ClientController@removeAccessForm'));
  // Post form for removing access to a specific psychologist to a client
  Route::post('{clientId}/access/{userId}/remove', array('as' => 'clients.remove_access_form_post', 'uses' => 'ClientController@removeAccessFormPost'));
  // Show transfer possibilities for specific client
  Route::get('{clientId}/transfer', array('as' => 'clients.transfer', 'uses' => 'ClientController@transfer'));
  // Show form for transferring a client to another user
  Route::get('{clientId}/access/{userId}/transfer', array('as' => 'clients.transfer_form', 'uses' => 'ClientController@transferForm'));
  // Post form for transferring a client to another user
  Route::post('{clientId}/access/{userId}/transfer', array('as' => 'clients.transfer_form_post', 'uses' => 'ClientController@transferFormPost'));

  // Choose which template to use when writing a client record
  Route::post('{clientId}/records/create', array('as' => 'templates.use', 'uses' => 'TemplateController@useTemplate'));

  // View all records for a client
  Route::get('{clientId}/records/list', array('as' => 'clients.records.list', 'uses' => 'RecordController@index'));
  // View all records for a client
  Route::get('{clientId}/records/view-all', array('as' => 'clients.records.view_all', 'uses' => 'RecordController@viewAll'));
  // Print view for all records for a client
  Route::get('{clientId}/records/print-all', array('as' => 'clients.records.print_all', 'uses' => 'RecordController@printAll'));
  // Post form for signing a record
  Route::post('{clientId}/records/sign', array('as' => 'clients.records.sign', 'uses' => 'RecordController@sign'));
  // View change history for a record
  Route::get('{clientId}/records/{recordId}/change-history', array('as' => 'clients.records.change_history', 'uses' => 'RecordController@changeHistory'));
  // View specific version
  Route::get('{clientId}/records/{recordId}/change-history/{version}', array('as' => 'clients.records.change_history_version', 'uses' => 'RecordController@changeHistoryVersion'));
  // Print view for specific record
  Route::get('{clientId}/records/{recordId}/print', array('as' => 'clients.records.print_show', 'uses' => 'RecordController@printShow'));
  // Form for unsigning record
  Route::get('{clientId}/records/{recordId}/unsign-form', array('as' => 'clients.records.unsign_form', 'uses' => 'RecordController@unsignForm'));
  // Form for unsigning record
  Route::post('{clientId}/records/{recordId}/unsign-form', array('as' => 'clients.records.unsign_form_post', 'uses' => 'RecordController@unsignFormPost'));

  // Show index of clients that psychologists have access to through cooperation
  Route::get('{clientId}/files/{filename}/download', array('as' => 'clients.files.download', 'uses' => 'FileController@download'));
});
Route::resource('clients', 'ClientController');
Route::resource('clients.files', 'FileController');
Route::resource('clients.records', 'RecordController');


// Set a favorite template for a user
Route::post('templates/{templateId}/set-favorite', array('as' => 'templates.set_favorite', 'uses' => 'TemplateController@setFavorite'));
Route::resource('templates', 'TemplateController');
