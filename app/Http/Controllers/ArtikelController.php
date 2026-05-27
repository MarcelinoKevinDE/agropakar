<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artikel; // Pastikan Anda sudah membuat model Artikel

class ArtikelController extends Controller
{
    public function index()
    {
        $artikels = Artikel::all(); // Mengambil semua data artikel
        return view('artikel', compact('artikels'));
    }
}