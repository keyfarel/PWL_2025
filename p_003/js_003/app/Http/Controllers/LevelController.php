<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{


    public function index()
    {
//        DB::insert('insert into m_level (level_kode, level_nama, created_at) values (?, ?, ?)', [
//            'CUs',
//            'Pelanggan',
//            now()
//        ]);
//
//        return 'Insert data baru berhasil';

//        $rows = DB::update('update m_level set level_nama = ? where level_kode = ?', [
//            'Customer',
//            'CUs']);
//        return 'Update data berhasil';

//        $rows = DB::delete('delete from m_level where level_kode = ?', ['CUs']);
//        return 'Delete data berhasil, jumlah data yang dihapus ' . $rows;

        $data = collect(DB::select('select * from m_level'));
        return view('level', ['data' => $data]);
    }
}
