<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokModel extends Model
{
    use HasFactory;

    protected $table = 't_stok';

    protected $primaryKey = 'stok_id';

    public $timestamps = true;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'barang_id',
        'stok_tanggal',
        'stok_jumlah',
    ];

    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id', 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id', 'barang_id');
    }
}
