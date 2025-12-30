<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function index()
    {
        return view('docs.index');
    }

    public function privacy()
    {
        return view('docs.privacy');
    }

    public function terms()
    {
        return view('docs.terms');
    }
}
