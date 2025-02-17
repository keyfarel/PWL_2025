<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index($name = null): string
    {
        return 'Nama saya ' . $name;
    }
}
