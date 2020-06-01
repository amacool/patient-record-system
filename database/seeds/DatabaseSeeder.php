<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds. updated.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            'id' => '1',
            'name' => 'Psykologbasen',
            'seats' => '10',
        ]);

        DB::table('users')->insert([
            'id' => '1',
            'name' => 'Espen Johnsen',
            'email' => 'esjohnse@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => '1',
            'phone' => '95779416',
            'country_code' => '47',
            'role' => '2',
            'tfa' => '0',
        ]);

        DB::table('users')->insert([
            'id' => '2',
            'name' => 'Marion Nilsen',
            'email' => 'marionnilsen86@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => '1',
            'phone' => '92289128',
            'country_code' => '47',
            'role' => '0',
            'tfa' => '0',
        ]);

        DB::table('categories')->insert([
            'id' => '1',
            'title' => 'Journal note',
        ]);

        DB::table('categories')->insert([
            'id' => '2',
            'title' => 'Treatment plan',
        ]);

        DB::table('categories')->insert([
            'id' => '3',
            'title' => 'Report',
        ]);

        DB::table('templates')->insert([
            'id' => '4',
            'title' => 'Empty template',
        ]);


        Model::unguard();

        // $this->call(UserTableSeeder::class);

        Model::reguard();
    }
}
