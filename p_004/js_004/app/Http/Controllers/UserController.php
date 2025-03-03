<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {

//        praktikum 1

//        $data = ['level_id' => 2,
//            'username' => 'manager_tiga',
//            'nama' => 'Manager 3',
//            'password' => Hash::make('12345')
//        ];
//        UserModel::create($data);
//
//        $user = UserModel::all();
//        return view('user', ['data' => $user]);

//        praktikum 2.1
//
//        $user = UserModel::findOr(29, ['username', 'nama'], function (){
//            abort(404);
//        });
//        return view('user', ['data' => $user]);

//        praktikum 2.2

        $user = UserModel::where('username', 'manager9')->firstOrFail();
        return view('user', ['data' => $user]);
    }
}
