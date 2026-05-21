<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KinerjaCleaning extends Model
{
    protected $fillable = [
        'user_id',
        'area',
        'keterangan',
        'foto',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
