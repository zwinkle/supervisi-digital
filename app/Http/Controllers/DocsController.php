<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    /**
     * Menampilkan halaman indeks dokumentasi (jika ada).
     */
    public function index()
    {
        return view('docs.index');
    }

    /**
     * Menampilkan halaman Kebijakan Privasi.
     */
    public function privacy()
    {
        return view('docs.privacy');
    }

    /**
     * Menampilkan halaman Syarat dan Ketentuan.
     */
    public function terms()
    {
        return view('docs.terms');
    }
}
