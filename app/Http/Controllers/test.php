<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class test extends Controller
{
    public function test()
    {
        return response()->json(['message' => 'Test successful']);
    } 
}
