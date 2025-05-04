<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserModel extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'm_user';
    protected $primaryKey = 'user_id';
    protected $with = ['level'];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $fillable = [
        'level_id',
        'username',
        'nama',
        'password',
        'photo',
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LevelModel::class, 'level_id', 'level_id');
    }

    public function stok()
    {
        return $this->hasMany(StokModel::class, 'user_id', 'user_id');
    }

    public function penjualan()
    {
        return $this->hasMany(PenjualanModel::class, 'user_id', 'user_id');
    }

    public function photo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset('storage/images/profiles/' . $value) : null,
            set: fn($value) => $value,
        );
    }

    public function getRole(): string
    {
        return $this->level->level_kode;
    }

    public function getRoleName(): string
    {
        return $this->level->level_nama;
    }

    public function hasRole($role): bool
    {
        return $this->level->level_kode == $role;
    }
}
