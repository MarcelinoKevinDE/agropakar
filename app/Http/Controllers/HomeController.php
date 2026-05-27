<?php

namespace App\Http\Controllers;

use App\Models\Artikel; // Pastikan model Artikel sudah ada

class HomeController extends Controller
{
    public function index()
    {
        // Ambil artikel agar variabel $artikels tersedia di dashboard
        $artikels = Artikel::latest()->take(3)->get(); 
        
        return view('dashboard', compact('artikels'));
    }
}