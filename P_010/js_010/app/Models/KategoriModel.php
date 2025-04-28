<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriModel extends Model
{
    use HasFactory;

    protected $table = 'm_kategori';

    protected $primaryKey = 'kategori_id';

    public $timestamps = true;

    protected $fillable = [
        'kategori_kode',
        'kategori_nama',
    ];

    public function barang()
    {
        return $this->hasMany(BarangModel::class, 'kategori_id', 'kategori_id');
    }
}
