<?php

namespace App\Http\Traits;

trait SanitizeTrait
{
    static public function traitMethod($value)
    {
        return strip_tags($value);
    }

    static public function sanitizeRequest($request)
    {
        $result = $request;
        foreach($request as $key => $val) {
            $result[$key] = strip_tags($val);
        }
        return $result;
    }
}