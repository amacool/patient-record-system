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
  // 01.11.20: But this url is not actively used in the LoginController, it sends users to 'clients/active', ok.
  return redirect()->route('clients.active_index');
});


// Authentication routes
Route::prefix('auth')->group(function () {
  //Show the login-page (username + password)
  Route::get('login', 'Auth\LoginController@getLogin')->name('login-page');
  // Post the username & password and process it on the server
  Route::post('login', 'Auth\LoginController@postLogin')->name('login-users')->middleware("throttle:5,1");
  // The url 'auth/logout' logs the user out through a GET-request
  Route::get('logout', 'Auth\LoginController@getLogout')->name('logout');
  // If 2 Factor Authentication (2FA) is enabled, the user will see a form for token after correct username + password
  Route::get('two-factor', 'Auth\LoginController@getTwoFactor')->name('two-factor');
  // Posts the 2FA-token, then processes it externally (Twilio). A response is received, and user is logged in if correct token.
  Route::post('two-factor', 'Auth\LoginController@postTwoFactor')->name('post-two-factor');
});

// Registration routes (Registering users is done by RegisterController, not UserController in this app)
Route::prefix('admin')->group(function () {
  // Form for registering a new user (a user is a psychologist, NOT client / patient)
  Route::get('register', 'Auth\RegisterController@getRegister')->name('register-page');
  // Posting the info about the new user
  Route::post('register', 'Auth\RegisterController@postRegister')->name('register-users');
});

// Routes to show lists of the users in the system, regardless of which company they belong to
Route::prefix('users')->group(function () {
  // Show list of all active users in the system (psychologists)
  Route::get('index', array('as' => 'users.index', 'uses' => 'UserController@index'));
  // Show list of all inactive users in the system (psychologists)
  Route::get('inactive-index', array('as' => 'users.inactive_index', 'uses' => 'UserController@inactiveIndex'));
});

// Routes for administration of companies and users, including some client administration
Route::prefix('companies')->group(function () {
  // Turn 2FA ON / OFF for a user
  Route::post('{companyId}/users/{userId}/change-two-factor', array('as' => 'companies.users.change_two_factor', 'uses' => 'UserController@changeTwoFactor'));
  // Issue a warning to a user that has not paid an invoice (shows a bootstrap-alert telling the user to pay when logged in)
  Route::post('{companyId}/users/{userId}/payment-warning', array('as' => 'companies.users.payment_warning', 'uses' => 'UserController@paymentWarning'));
  // Suspend a user that has not paid despite of warning. This will again prevent the user from doing certain operations in the system.
  Route::post('{companyId}/users/{userId}/suspend', array('as' => 'companies.users.suspend_user', 'uses' => 'UserController@suspendUser'));
  // A user can set a standard-title that will be the default when creating records.
  Route::post('{companyId}/users/{userId}/standard-title', array('as' => 'companies.users.standard_title', 'uses' => 'UserController@standardTitle'));
  // A user can register a secret question & answer that is available to the admin. Will be used if the user makes contact.
  Route::post('{companyId}/users/{userId}/secret-question', array('as' => 'companies.users.secret_question', 'uses' => 'UserController@secretQuestion'));
  // Admin can toggle a user between status ACTIVE vs INACTIVE
  Route::post('{companyId}/users/{userId}/activate-toggle', array('as' => 'companies.users.activate_toggle', 'uses' => 'UserController@activateToggle'));
  // Register the user with 2FA provider (Authy)
  Route::post('{companyId}/users/{userId}/register-authy', array('as' => 'companies.users.register_authy', 'uses' => 'UserController@registerAuthy'));
  // Authy delete user (but currently only removes the Authy ID locally, does not actually delete the user with Authy)
  Route::post('{companyId}/users/{userId}/delete-authy', array('as' => 'companies.users.delete_authy', 'uses' => 'UserController@deleteAuthy'));
  // Change name & email adress of a user
  Route::post('{companyId}/users/{userId}/change-info', array('as' => 'companies.users.change_info', 'uses' => 'UserController@changeInfo'));
  // Change which company a user belongs to
  Route::post('{companyId}/users/{userId}/change-company', array('as' => 'companies.users.change_company', 'uses' => 'UserController@changeCompany'));
  // Change the role of a user (can be either regular user (0) or company admin (1). Changing to system admin should not be allowed.
  Route::post('{companyId}/users/{userId}/change-role', array('as' => 'companies.users.change_role', 'uses' => 'UserController@changeRole'));
  // Change a users phone number (only locally in the database, has no effect on the instance that is currently registered with Authy)
  Route::post('{companyId}/users/{userId}/change-phone', array('as' => 'companies.users.change_phone', 'uses' => 'UserController@changePhone'));
  // Old function from when the app was migrated from wordpress to laravel, probably not relevant anymore
  Route::post('{companyId}/users/{userId}/transfer-single-wprecord', array('as' => 'companies.users.transfer_single_wprecord', 'uses' => 'UserController@transferSingleWprecord'));
  // Old function from when the app was migrated from wordpress to laravel, probably not relevant anymore
  Route::post('{companyId}/users/{userId}/transfer-client-records', array('as' => 'companies.users.transfer_client_records', 'uses' => 'UserController@transferClientRecords'));
  // Show a log of all Accesses given / revoked and transfers performed for a specific user
  Route::get('{companyId}/users/{userId}/access-transfer-logs', array('as' => 'companies.users.access_transfer_logs', 'uses' => 'UserController@accessAndTransferLogs'));
  // Show a log of all successful logins and login-attempts for a specific user
  Route::get('{companyId}/users/{userId}/access-logs', array('as' => 'companies.users.access_logs', 'uses' => 'UserController@accessLogs'));
  // Delete a user (01.11.20: THIS FUNCTION NEEDS MORE WORK)
  Route::post('{companyId}/users/{userId}/remove-this-user', array('as' => 'companies.users.delete', 'uses' => 'UserController@deleteUser'));
  // Show all clients in a specific company (a collection of clients that belongs to the users from this company)
  Route::get('{companyId}/clients', array('as' => 'companies.clients', 'uses' => 'ClientController@getClientsInCompany'));
  // Show all active clients for another user (Admin can do this for all users, company admin for users in own company)
  Route::get('{companyId}/users/{userId}/clients/active', array('as' => 'companies.clients.active', 'uses' => 'ClientController@getActiveClientsForUser'));
  // Show all archived clients for another user (Admin can do this for all users, company admin for users in own company)
  Route::get('{companyId}/users/{userId}/clients/archive', array('as' => 'companies.clients.archive', 'uses' => 'ClientController@getArchiveClientsForUser'));
  // Show all clients where another specific user has been given access (Admin can do this for all users, company admin for users in own company)
  Route::get('{companyId}/users/{userId}/cooperation-access', array('as' => 'companies.clients.coop', 'uses' => 'ClientController@getCoopClientsForUser'));
  // Show page for alternatives when exporting a companys data
  Route::get('{companyId}/exportForm', array('as' => 'companies.export', 'uses' => 'CompanyController@exportCompanyDataForm'));
  // Start the export of a companys data
  Route::post('{companyId}/export', array('as' => 'companies.upload', 'uses' => 'CompanyController@exportCompanyData'));
  // Download a file with the company data
  Route::get('{companyId}/download/{fileName}', array('as' => 'companies.download', 'uses' => 'CompanyController@downloadCompanyData'));
});
// WHAT DOES THE RESOURCE-ROUTE BELOW CONTAIN?
// /companies (GET, index) - Show an index of all instances of the resource (01.11.20: Ok)
// /companies/create (GET, create) - Show page for creating a new instance of the resource (01.11.20: Ok)
// /companies (POST, store) - Post a request to store a new instance of the resource (01.11.20: Ok)
// /companies/{ID} (GET, show) - Show a specific instance of the resource (01.11.20: Ok)
// /companies/{ID}/edit (GET, edit) - Show page for editing a specific instance of the resource) (01.11.20: Ok)
// /companies/{ID} (PUT PATCH, update) - Send a PUT / PATCH request to update an instance of the resource (01.11.20: Ok)
// /companies/{ID} (DELETE, destroy) - Send a DELETE request to delete an instance of the resource (01.11.20: CompanyController@destroy does not exist)
Route::resource('companies', 'CompanyController');
// WHAT DOES THE RESOURCE-ROUTE BELOW CONTAIN?
// Resource-route companies.users seems a bit pointless, because registration is done through RegisterController, but ok...
// /companies/{ID}/users (GET, index) - Show an index of all instances of the resource (01.11.20: Shows all users in the system)
// /companies/{ID}/users/create (GET, create) - Show page for creating a new instance of the resource (01.11.20: UserController@create does not exist)
// /companies{ID}/users (POST, store) - Post a request to store a new instance of the resource (01.11.20: UserController@store does not exist)
// /companies/{ID}/users/{ID} (GET, show) - Show a specific instance of the resource (01.11.20: UserController@show does nothing)
// /companies/{ID}/users/{ID}/edit (GET, edit) - Show page for editing a specific instance of the resource)(01.11.20 - This works)
// /companies/{ID}/users/{ID} (PUT PATCH, update) - Send a PUT / PATCH request to update an instance of the resource (01.11.20 - Currently updates user password)
// /companies/{ID}/users/{ID} (DELETE, destroy) - Send a DELETE request to delete an instance of the resource (01.11.20 - UserController@destroy does not exist)
Route::resource('companies.users', 'UserController');

// Routes for working with clients, including records and files
Route::prefix('clients')->group(function () {
  // ONLY FOR ADMIN: Shows all the clients in the system
  Route::get('index', array('as' => 'clients.all', 'uses' => 'ClientController@getAllClients'));
  // Logged-in-user: see clients where user has been given coop-access
  Route::get('cooperation-access', array('as' => 'clients.coop_index', 'uses' => 'ClientController@coopIndex'));
  // Logged-in-user: see own active clients
  Route::get('active', array('as' => 'clients.active_index', 'uses' => 'ClientController@activeIndex'));
  // Logged-in-user: see own archived / inactive clients
  Route::get('archive', array('as' => 'clients.archive_index', 'uses' => 'ClientController@archiveIndex'));
  // Move a client between active & archived status
  Route::post('change-status', array('as' => 'clients.archive_move', 'uses' => 'ClientController@archiveMove'));
  // Show form to create a new client
  Route::get('create', array('as' => 'clients.create', 'uses' => 'ClientController@create'));
  // Show personal info for a specific client
  Route::get('{clientId}', array('as' => 'clients.show', 'uses' => 'ClientController@show'));
  // Show logged events for a specific client
  Route::get('{clientId}/logs', array('as' => 'clients.logs', 'uses' => 'ClientController@logs'));
  // Show access rights & possibilities for specific client
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
  // View list of all records for a client
  Route::get('{clientId}/records/list', array('as' => 'clients.records.list', 'uses' => 'RecordController@index'));
  // View content of all records for a client
  Route::get('{clientId}/records/view-all', array('as' => 'clients.records.view_all', 'uses' => 'RecordController@viewAll'));
  // Export all records for a client to PDF (IS THIS IN USE??)
  Route::get('{clientId}/records/pdf', array('as' => 'clients.records.pdf', 'uses' => 'RecordController@exportToPDF'));
  // Print view for all records for a client
  Route::get('{clientId}/records/print-all', array('as' => 'clients.records.print_all', 'uses' => 'RecordController@printAll'));
  // Post form for signing a record
  Route::post('{clientId}/records/sign', array('as' => 'clients.records.sign', 'uses' => 'RecordController@sign'));
  // View summary of change history for a record
  Route::get('{clientId}/records/{recordId}/change-history', array('as' => 'clients.records.change_history', 'uses' => 'RecordController@changeHistory'));
  // View specific version from change-history of a record
  Route::get('{clientId}/records/{recordId}/change-history/{version}', array('as' => 'clients.records.change_history_version', 'uses' => 'RecordController@changeHistoryVersion'));
  // Print view for specific record
  Route::get('{clientId}/records/{recordId}/print', array('as' => 'clients.records.print_show', 'uses' => 'RecordController@printShow'));
  // Form for unsigning record
  Route::get('{clientId}/records/{recordId}/unsign-form', array('as' => 'clients.records.unsign_form', 'uses' => 'RecordController@unsignForm'));
  // Post form for unsigning record
  Route::post('{clientId}/records/{recordId}/unsign-form', array('as' => 'clients.records.unsign_form_post', 'uses' => 'RecordController@unsignFormPost'));
  // Page that allows admin to move a record to another client (form)
  Route::get('{clientId}/records/{recordId}/move', array('as' => 'clients.records.move', 'uses' => 'RecordController@move'));
  // Post moving a record from one client to another (only for admin)
  Route::post('{clientId}/records/{recordId}/move/{receiverId}', array('as' => 'clients.records.move_post', 'uses' => 'RecordController@movePost'));

  // Show index of clients that psychologists have access to through cooperation
  Route::get('{clientId}/files/{filename}/download', array('as' => 'clients.files.download', 'uses' => 'FileController@download'));
});
// Clients-resourcecontroller is a bit messy. Some of these methods are specified above also (create & show)
// /clients (GET, index)              - Show an index of all instances of the resource (01.11.20: For one specific user. Works)
// /clients/create (GET, create)      - Show page for creating a new instance of the resource (01.11.20: works, but is also specified in own route)
// /clients (POST, store)             - Post a request to store a new instance of the resource (01.11.20: Works)
// /clients/{ID} (GET, show)          - Show a specific instance of the resource (01.11.20: works, but is also specified in own route)
// /clients/{ID}/edit (GET, edit)     - Show page for editing a specific instance of the resource) (01.11.20: Works)
// /clients/{ID} (PUT PATCH, update)  - Send a PUT / PATCH request to update an instance of the resource (01.11.20: Works)
// /clients/{ID} (DELETE, destroy)    - Send a DELETE request to delete an instance of the resource (01.11.20: No destroy method exists)
Route::resource('clients', 'ClientController');
// /clients/{ID}/files (GET, index)           - Show an index of all instances of the resource (01.11.20: Works)
// /clients/{ID}/files/create (GET, create)   - Show page for creating a new instance of the resource (01.11.20: Works)
// /clients{ID}/files (POST, store)           - Post a request to store a new instance of the resource (01.11.20: Works)
// /clients/{ID}/files/{ID} (GET, show)       - Show a specific instance of the resource (01.11.20: FileController@show does nothing, this is correct)
// /clients/{ID}/files/{ID}/edit (GET, edit)  - Show page for editing a specific instance of the resource)(01.11.20 - FileController@edit does nothing)
// /clients/{ID}/files/{ID} (PUT PATCH, update) - Send a PUT / PATCH request to update an instance of the resource (01.11.20 - FileController@update does nothing)
// /clients/{ID}/files/{ID} (DELETE, destroy)   - Send a DELETE request to delete an instance of the resource (01.11.20 - FileController@destroy does nothing)
Route::resource('clients.files', 'FileController');
// /clients/{ID}/records (GET, index)           - Show an index of all instances of the resource (01.11.20: Does the same as clients/{ID}/records/list above)
// /clients/{ID}/records/create (GET, create)   - Show page for creating a new instance of the resource (01.11.20: Works)
// /clients{ID}/records (POST, store)           - Post a request to store a new instance of the resource (01.11.20: Works)
// /clients/{ID}/records/{ID} (GET, show)       - Show a specific instance of the resource (01.11.20: Works)
// /clients/{ID}/records/{ID}/edit (GET, edit)  - Show page for editing a specific instance of the resource)(01.11.20 - Works)
// /clients/{ID}/records/{ID} (PUT PATCH, update) - Send a PUT / PATCH request to update an instance of the resource (01.11.20: Works)
// /clients/{ID}/records/{ID} (DELETE, destroy)   - Send a DELETE request to delete an instance of the resource (01.11.20 - RecordController@destroy does nothing)
Route::resource('clients.records', 'RecordController');

// Routes for working with templates
// Set a favorite template for a user
Route::post('templates/{templateId}/set-favorite', array('as' => 'templates.set_favorite', 'uses' => 'TemplateController@setFavorite'));
// /templates (GET, index)              - Show an index of all instances of the resource (01.11.20: Works)
// /templates/create (GET, create)      - Show page for creating a new instance of the resource (01.11.20: works)
// /templates (POST, store)             - Post a request to store a new instance of the resource (01.11.20: Works)
// /templates/{ID} (GET, show)          - Show a specific instance of the resource (01.11.20: works)
// /templates/{ID}/edit (GET, edit)     - Show page for editing a specific instance of the resource) (01.11.20: Works)
// /templates/{ID} (PUT PATCH, update)  - Send a PUT / PATCH request to update an instance of the resource (01.11.20: Works)
// /templates/{ID} (DELETE, destroy)    - Send a DELETE request to delete an instance of the resource (01.11.20: Works)
Route::resource('templates', 'TemplateController');

// ADMIN DIRECT FUNCTIONS
// Route to transfer DB. This is not very relevant anymore. It is from years ago when converting the site from
// a Wordpress-site to Laravel
Route::get('dbtransfer/test', array('as' => 'db_transfer_test', 'uses' => 'UserController@transferAll'));
// Route to get WP revisions for an old post (from Wordpress-database, not very relevant anymore)
Route::get('wphistory/get', array('as' => 'wphistory', 'uses' => 'UserController@wpVersionHistory'));
