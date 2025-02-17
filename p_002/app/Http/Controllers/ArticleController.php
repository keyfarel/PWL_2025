<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index($id): string
    {
        return 'Halaman Artikel dengan Id ' . $id;
    }
}
