<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SPAController extends Controller
{
    public static function SPAView()
    {
        return view("spa", ["date" => date("Y-m-d")]);
    }

    public function __invoke()
    {
        return self::SPAView();
    }
}
