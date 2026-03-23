<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;

class StorefrontController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        return view('storefront', compact('templates'));
    }
}
