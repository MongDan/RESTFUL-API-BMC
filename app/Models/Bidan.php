<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Bidan extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'bidan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
        'nama',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Relasi ke pasien (satu bidan bisa punya banyak pasien)
    public function pasien()
    {
        return $this->hasMany(Pasien::class, 'bidan_id', 'id');
    }

    public static function login(string $username, string $password): ?self
{
    $bidan = self::where('username', $username)->first();

    if (!$bidan || !Hash::check($password, $bidan->password)) {
        return null; // jangan lempar ValidationException
    }

    return $bidan;
}


    // --- Metode wajib JWT ---
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function lihatDaftarPasien()
    {
        return $this->pasien()->get([
            'no_reg',
            'nama',
            'umur',
            'alamat',
            'gravida',
            'paritas',
            'abortus'
        ]);
    }


    public function mulaiPersalinan(Pasien $pasien)
{
    if ($pasien->bidan_id !== $this->id) {
        throw ValidationException::withMessages([
            'pasien' => 'Pasien ini bukan pasien Anda.'
        ]);
    }

    // Cek existing persalinan
    $existing = Persalinan::where('pasien_no_reg', $pasien->no_reg)
        ->where('status', 'aktif')
        ->first();

    if ($existing) {
        throw ValidationException::withMessages([
            'persalinan' => 'Pasien ini sudah memiliki persalinan aktif.'
        ]);
    }

    // Generate ID persalinan
    $lastPersalinan = Persalinan::orderBy('id', 'desc')->first();
    $nextNumber = $lastPersalinan
        ? intval(preg_replace('/\D/', '', $lastPersalinan->id)) + 1
        : 1;

    $id = 'Persalinan' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    // Buat persalinan baru
    $persalinanBaru = Persalinan::create([
        'id' => $id,
        'pasien_no_reg' => $pasien->no_reg,
        'tanggal_jam_rawat' => now(),
        'tanggal_jam_mules' => null,
        'ketuban_pecah' => false,
        'status' => 'aktif',
    ]);

   $lastPartograf = Partograf::where('persalinan_id', 'like', 'Partograf%')->orderBy('id', 'desc')->first();

    $nextNumber = 1;
    if ($lastPartograf) {
    // Ambil 2 digit urutan dari ID terakhir
        preg_match('/Partograf(\d{2})/', $lastPartograf->id, $matches);
    if (isset($matches[1])) {
        $nextNumber = intval($matches[1]) + 1;
    }
}

    $partografId = 'Partograf' 
    . str_pad($nextNumber, 2, '0', STR_PAD_LEFT) 
    . $pasien->no_reg 
    . date('y'); 

    $partograf = Partograf::create([
        'id' => $partografId,
        'persalinan_id' => $persalinanBaru->id,
    ]);

    return [
        'persalinan' => $persalinanBaru,
        'partograf' => $partograf
    ];
}


    public function kirimPesan(Pasien $pasien, string $isiPesan)
    {
        if ($pasien->bidan_id !== $this->id) {
            throw ValidationException::withMessages([
                'pasien' => 'Pasien ini bukan pasien Anda.'
            ]);
        }

        return Pesan::create([
            'bidan_id' => $this->id,
            'pasien_id' => $pasien->id,
            'isiPesan' => $isiPesan,
            'pengirim' => 'bidan',
            'waktuKirim' => now(),
        ]);
    }
    
    public function getJWTCustomClaims()
    {
        return []; // Tidak ada tambahan claim
    }
}
