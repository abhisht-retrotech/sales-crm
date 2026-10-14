<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseDealController extends Controller
{
    public function index()
    {
        return view('purchase_deals');
    }
}
