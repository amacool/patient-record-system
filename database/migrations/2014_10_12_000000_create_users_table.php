<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('orgnr');
            $table->integer('seats');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oldid');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->integer('phone');
            $table->integer('role');
            $table->integer('company_id')->unsigned()->nullable()->index();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('set null');
            $table->integer('created_by')->unsigned()->nullable()->index();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            // AUTHY
            $table->string('authy_id')->nullable();
            $table->string('country_code');
            $table->integer('tfa');
            $table->integer('favtemplate');
            $table->string('standardtitle');
            // END AUTHY
            $table->date('paymentmissing');
            $table->date('suspended');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clients', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('oldid');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('born');
            $table->string('ssn');
            $table->string('civil_status');
            $table->string('work_status');
            $table->string('medication');
            $table->string('street_address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('phone');
            $table->string('closest_relative');
            $table->string('closest_relative_phone');
            $table->string('children');
            $table->string('gp');
            $table->string('individual_plan');
            $table->string('other_info');
            $table->integer('active');
            $table->timestamps();
        });

        Schema::create('categories', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('title');
        });

        Schema::create('diagnoses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('title');
        });

        Schema::create('templates', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('category_id')->unsigned()->nullable()->index();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
            $table->integer('created_by')->unsigned()->nullable()->index();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('records', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('oldid');
            $table->integer('category_id')->unsigned()->nullable()->index();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
            $table->integer('created_by')->unsigned()->nullable()->index();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->timestamp('app_date');
            $table->timestamp('signed_date');
            $table->integer('signed_by')->unsigned()->nullable()->index();
            $table->foreign('signed_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('accessrights', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('given_by')->unsigned()->nullable()->index();
            $table->foreign('given_by')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('revoked_by')->unsigned()->nullable()->index();
            $table->foreign('revoked_by')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('no action');
            $table->string('reason');
            $table->dateTime('datetime');
        });

        Schema::create('transfers', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('transferred_by')->unsigned()->nullable()->index();
            $table->foreign('transferred_by')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('transferred_to')->unsigned()->nullable()->index();
            $table->foreign('transferred_to')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('no action');
            $table->string('reason');
            $table->dateTime('datetime');
        });

        Schema::create('readrecordlog', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('read_by')->unsigned()->nullable()->index();
            $table->foreign('read_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
            $table->integer('record_id')->unsigned()->nullable()->index();
            $table->foreign('record_id')
                ->references('id')
                ->on('records')
                ->onDelete('cascade');
            $table->timestamp('timestamp');
        });

        Schema::create('changerecordlog', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('created_by')->unsigned()->nullable()->index();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('changed_by')->unsigned()->nullable()->index();
            $table->foreign('changed_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('no action');
            $table->integer('record_id')->unsigned()->nullable()->index();
            $table->foreign('record_id')
                ->references('id')
                ->on('records')
                ->onDelete('cascade');
            $table->string('formertitle');
            $table->string('newtitle');
            $table->text('formercontent');
            $table->text('newcontent');
            $table->timestamp('formerapp_date');
            $table->timestamp('newapp_date');
            $table->timestamp('timestamp');
        });

        Schema::create('signlog', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('no action');
            $table->integer('record_id')->unsigned()->nullable()->index();
            $table->foreign('record_id')
                ->references('id')
                ->on('records')
                ->onDelete('cascade');
            $table->integer('signed_by')->unsigned()->nullable()->index();
            $table->foreign('signed_by')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('unsigned_by')->unsigned()->nullable()->index();
            $table->foreign('unsigned_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamp('timestamp');
        });

        Schema::create('files', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action');
            $table->integer('client_id')->unsigned()->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
            $table->string('file');
            $table->string('description');
            $table->integer('deleted')->unsigned()->nullable()->index();
            $table->timestamps();
        });

        Schema::create('logins', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('ip');
            $table->string('success');
            $table->string('combocorrect');
            $table->string('tokencorrect');
            $table->timestamps();
        });

        Schema::create('logouts', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('ip');
            $table->string('manual');
            $table->string('auto');
            $table->timestamps();
        });

        Schema::create('awaitingupload', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('client_id')->unsigned()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
            $table->integer('oldclient_id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('olduser_id');
            $table->string('filename');
            $table->string('awaiting');
        });

        Schema::create('awaitingdiagnoses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('client_id')->unsigned()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
            $table->integer('oldclient_id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('olduser_id');
            $table->string('title');
            $table->text('content');
            $table->string('awaiting');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
