<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index()
    {
        return view('opportunities');
    }
}
