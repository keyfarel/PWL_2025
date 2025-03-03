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
//
//        $user = UserModel::where('username', 'manager9')->firstOrFail();
//        return view('user', ['data' => $user]);

//        praktikum 2.3
//
//        $user = UserModel::where('level_id', 2)->count();
//        return view('user_count', ['data' => $user]);

//        praktikum 2.4
//
//        $user = UserModel::create([
//            'username' => 'manager',
//            'nama' => 'Manager',
//            'password' => Hash::make('12345'),
//            'level_id' => 2
//        ]);
//        $user->save();
//
//        return view('user', ['data' => $user]);

//        praktikum 2.5
//
        $user = UserModel::create([
            'username' => 'manager55',
            'nama' => 'Manager55',
            'password' => Hash::make('12345'),
            'level_id' => 2,
        ]);

        $user->username = 'manager12';

        $user->save();

        $user->wasChanged(); // true
        $user->wasChanged('username'); // true
        $user->wasChanged(['username', 'level_id']); // true
        $user->wasChanged('nama'); // false

        dd($user->wasChanged(['nama', 'username'])); // true

    }
}
