<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(): string
    {
        return 'Hello World';
    }

    public function greeting(): string
    {
        return view('blog.hello')
            ->with('name', 'Key')
            ->with('occupation','Astronaut');
    }
}
