<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Persalinan extends Model
{
    use HasFactory;

    protected $table = 'persalinan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'pasien_no_reg',
        'tanggal_jam_rawat',
        'tanggal_jam_mules',
        'ketuban_pecah',
        'tanggal_jam_ketuban_pecah',
        'status',
        'tanggal_jam_waktu_bayi_lahir',
    ];

    protected $casts = [
        'ketuban_pecah' => 'boolean',
        'tanggal_jam_rawat' => 'datetime',
        'tanggal_jam_mules' => 'datetime',
        'tanggal_jam_ketuban_pecah' => 'datetime',
        'tanggal_jam_waktu_bayi_lahir' => 'datetime',

    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_no_reg', 'no_reg');
    }

    /**
     * 🔹 Method ubahStatus()
     * Memastikan status valid sesuai ENUM di DB
     */
    public function ubahStatus(string $status, $waktu_bayi_lahir = null)
{
    $allowed = ['aktif', 'tidak_aktif', 'selesai', 'rujukan'];
    if (!in_array($status, $allowed)) {
        throw new InvalidArgumentException("Status tidak valid. Pilihan: " . implode(', ', $allowed));
    }

    $this->status = $status;

    // Jika status selesai → isi tanggal waktu bayi lahir
    if ($status === 'selesai' && $waktu_bayi_lahir) {
        $this->tanggal_jam_waktu_bayi_lahir = $waktu_bayi_lahir;
    }

    $this->save();

    return $this;
}

    public function partograf()
{
    return $this->hasOne(Partograf::class, 'persalinan_id', 'id');
}

}
