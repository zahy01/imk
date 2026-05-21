<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CatatanPanen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @method static \Illuminate\Database\Eloquent\Builder whereDate(string $column, string $operator = null, mixed $value = null)
 */
class Attendance extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'attendances';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */
        'user_id',

        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        */
        'date',

        /*
        |--------------------------------------------------------------------------
        | WAKTU ABSENSI
        |--------------------------------------------------------------------------
        */
        'check_in',
        'check_out',

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */
        'status',

        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */
        'photo_path',
        'checkout_photo_path',

        /*
        |--------------------------------------------------------------------------
        | LOKASI CHECK IN
        |--------------------------------------------------------------------------
        */
        'checkin_latitude',
        'checkin_longitude',
        'checkin_address',

        /*
        |--------------------------------------------------------------------------
        | LOKASI CHECK OUT
        |--------------------------------------------------------------------------
        */
        'checkout_latitude',
        'checkout_longitude',
        'checkout_address',

        /*
        |--------------------------------------------------------------------------
        | DATA PEKERJA
        |--------------------------------------------------------------------------
        */
        'palm_weight',
        'note',

        /*
        |--------------------------------------------------------------------------
        | LEGACY
        |--------------------------------------------------------------------------
        */
        'photos',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | DATE & TIME
        |--------------------------------------------------------------------------
        */
        'date' => 'date',

        // DISARANKAN DATABASE DATETIME
        'check_in' => 'datetime',
        'check_out' => 'datetime',

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */
        'photos' => 'array',

        /*
        |--------------------------------------------------------------------------
        | GPS
        |--------------------------------------------------------------------------
        */
        'checkin_latitude' => 'decimal:7',
        'checkin_longitude' => 'decimal:7',

        'checkout_latitude' => 'decimal:7',
        'checkout_longitude' => 'decimal:7',

        /*
        |--------------------------------------------------------------------------
        | BERAT SAWIT
        |--------------------------------------------------------------------------
        */
        'palm_weight' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | APPENDS
    |--------------------------------------------------------------------------
    */

    protected $appends = [

        'photo_url',
        'checkout_photo_url',

        'checkin_maps_url',
        'checkout_maps_url',

        'working_hours',
        'status_color',

        'short_checkin_address',
        'short_checkout_address',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Relasi catatan panen
     */
    public function panen()
    {
        return $this->hasOne(
            CatatanPanen::class,
            'id_pegawai',
            'user_id'
        )->whereDate(
            'tanggal',
            optional($this->date)->toDateString()
        );
    }

    /**
     * Relasi alternatif
     */
    public function catatanPanen()
    {
        return $this->hasOne(
            CatatanPanen::class,
            'id_pegawai',
            'user_id'
        )->where(
            'tanggal',
            optional($this->date)->toDateString()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * URL foto check in
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }

    /**
     * URL foto check out
     */
    public function getCheckoutPhotoUrlAttribute()
    {
        return $this->checkout_photo_path
            ? asset('storage/' . $this->checkout_photo_path)
            : null;
    }

    /**
     * Link Google Maps check in
     */
    public function getCheckinMapsUrlAttribute()
    {
        if (
            is_null($this->checkin_latitude) ||
            is_null($this->checkin_longitude)
        ) {
            return null;
        }

        return 'https://maps.google.com/?q=' .
            $this->checkin_latitude . ',' .
            $this->checkin_longitude;
    }

    /**
     * Link Google Maps check out
     */
    public function getCheckoutMapsUrlAttribute()
    {
        if (
            is_null($this->checkout_latitude) ||
            is_null($this->checkout_longitude)
        ) {
            return null;
        }

        return 'https://maps.google.com/?q=' .
            $this->checkout_latitude . ',' .
            $this->checkout_longitude;
    }

    /**
     * Total jam kerja
     */
    public function getWorkingHoursAttribute()
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in);

        $checkOut = Carbon::parse($this->check_out);

        $minutes = $checkIn->diffInMinutes($checkOut);

        $hours = floor($minutes / 60);

        $remainingMinutes = $minutes % 60;

        return $hours . ' jam ' .
               $remainingMinutes . ' menit';
    }

    /**
     * Warna badge status
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {

            'tepat waktu' => 'green',

            'hadir' => 'green',

            'terlambat' => 'yellow',

            default => 'gray',
        };
    }

    /**
     * Short address check in
     */
    public function getShortCheckinAddressAttribute()
    {
        if (!$this->checkin_address) {
            return '-';
        }

        return explode(
            ',',
            $this->checkin_address
        )[0];
    }

    /**
     * Short address check out
     */
    public function getShortCheckoutAddressAttribute()
    {
        if (!$this->checkout_address) {
            return '-';
        }

        return explode(
            ',',
            $this->checkout_address
        )[0];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Sudah check in
     */
    public function hasCheckedIn()
    {
        return !is_null($this->check_in);
    }

    /**
     * Sudah check out
     */
    public function hasCheckedOut()
    {
        return !is_null($this->check_out);
    }

    /**
     * Memiliki lokasi check in
     */
    public function hasCheckinLocation()
    {
        return !is_null($this->checkin_latitude) &&
               !is_null($this->checkin_longitude);
    }

    /**
     * Memiliki lokasi check out
     */
    public function hasCheckoutLocation()
    {
        return !is_null($this->checkout_latitude) &&
               !is_null($this->checkout_longitude);
    }

    /**
     * Apakah terlambat
     */
    public function isLate()
    {
        return $this->status === 'terlambat';
    }

    /**
     * Apakah tepat waktu
     */
    public function isOnTime()
    {
        return in_array(
            $this->status,
            ['hadir', 'tepat waktu']
        );
    }
}
