<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partograf extends Model
{
    use HasFactory;

    protected $table = 'partograf';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'persalinan_id',

    ];

    // Relasi ke persalinan (satu partograf dimiliki oleh satu persalinan)
    public function persalinan()
    {
        return $this->belongsTo(Persalinan::class, 'persalinan_id', 'id');
    }
}
