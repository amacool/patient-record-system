<?php namespace App\Classes\golonka\bbcodeparser\src\Facades;

use Illuminate\Support\Facades\Facade;

class BBCodeParser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bbcode';
    }
}
