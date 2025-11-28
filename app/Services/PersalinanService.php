<?php

namespace App\Services;

use App\Models\Persalinan;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PersalinanService
{
    
   public function ubahStatus(Persalinan $persalinan, string $status, $waktu_bayi_lahir = null): Persalinan
{
    try {
        return $persalinan->ubahStatus($status, $waktu_bayi_lahir);
    } catch (InvalidArgumentException $e) {
        throw ValidationException::withMessages(['status' => $e->getMessage()]);
    }
}
    public function listByPasien(string $pasienNoReg)
    {
        return Persalinan::where('pasien_no_reg', $pasienNoReg)
            ->orderByDesc('tanggal_jam_rawat')
            ->get();
    }
}
