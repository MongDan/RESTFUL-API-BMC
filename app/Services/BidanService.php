<?php

namespace App\Services;

use App\Models\Bidan;
use App\Models\Pasien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;


class BidanService
{
    /**
     * 🔹 Login Bidan
     */
     public function login(array $credentials): ?Bidan
     {
        return Bidan::login($credentials['username'], $credentials['password']);
     }


    /**
     * 🔹 Bidan membuat pasien baru.
     */
   public function tambahPasien(array $data, Bidan $bidan): Pasien
    {
        // Gabungkan bidan_id lewat behavior model
        $data = $bidan->tambahPasien($data);

        // Username otomatis = nama
        $username = $data['nama'];

        // Cek duplikasi no_reg
        if (Pasien::where('no_reg', $data['no_reg'])->exists()) {
            throw ValidationException::withMessages([
                'no_reg' => 'Nomor registrasi sudah terdaftar.',
            ]);
        }

        // Cek duplikasi username
        if (Pasien::where('username', $username)->exists()) {
            throw ValidationException::withMessages([
                'username' => 'Username sudah terdaftar.',
            ]);
        }

        // Password default = username
        $password = $data['password'] ?? $username;

        // Create pasien
        return Pasien::create([
            'no_reg'   => $data['no_reg'],
            'username' => $username,
            'nama'     => $data['nama'],
            'password' => Hash::make($password),
            'alamat'   => $data['alamat'],
            'umur'     => $data['umur'],
            'gravida'  => $data['gravida'],
            'paritas'  => $data['paritas'],
            'abortus'  => $data['abortus'],
            'bidan_id' => $data['bidan_id'],
        ]);
    }

    //Lihat datapasien
    public function lihatDaftarPasien(Bidan $bidan)
    {
        return $bidan->lihatDaftarPasien();
    }

   public function mulaiPersalinan(Request $request, Bidan $bidan, Pasien $pasien)
{
    return $bidan->mulaiPersalinan($request, $pasien);
}

}
