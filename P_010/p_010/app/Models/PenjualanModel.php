<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanModel extends Model
{
    use HasFactory;

    protected $table = 't_penjualan';

    protected $primaryKey = 'penjualan_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'pembeli',
        'penjualan_kode',
        'penjualan_tanggal',
        'total_harga',
        'image',
    ];

    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? asset("storage/images/penjualan/{$this->penjualan_kode}/{$value}")
                : null,
            set: fn($value) => $value,
        );
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetailModel::class, 'penjualan_id', 'penjualan_id');
    }
}
