<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Kontraksi extends Model
{
    use HasFactory;

    protected $table = 'kontraksi';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'catatan_partograf_id',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    //Relasi: banyak kontraksi → satu catatan partograf
    public function catatanPartograf()
    {
        return $this->belongsTo(CatatanPartograf::class, 'catatan_partograf_id', 'id');
    }

    //Hitung durasi dalam detik
    public function getDurasi(): int
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return 0;
        }

        return Carbon::parse($this->waktu_mulai)
            ->diffInSeconds(Carbon::parse($this->waktu_selesai));
    }
}
