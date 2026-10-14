<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WireController extends Controller
{
    public function index()
    {
        return view('wires');
    }
}
