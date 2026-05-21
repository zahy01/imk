<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatroliSecurity extends Model
{
    protected $table = 'patroli_security';

    protected $fillable = [
        'user_id',
        'nama_area',
        'keterangan',
        'foto',
        'waktu_patroli',
    ];

    protected $casts = [
        'waktu_patroli' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
