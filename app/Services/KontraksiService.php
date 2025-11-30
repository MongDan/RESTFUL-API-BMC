<?php

namespace App\Services;

use App\Models\Kontraksi;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KontraksiService
{
    public function create(string $catatanPartografId, array $data): Kontraksi
    {
        if (empty($data['waktu_mulai']) || empty($data['waktu_selesai'])) {
            throw ValidationException::withMessages([
                'waktu' => 'waktu_mulai dan waktu_selesai wajib diisi.'
            ]);
        }

        $kontraksi = Kontraksi::create([
            'id' => Str::uuid()->toString(),
            'catatan_partograf_id' => $catatanPartografId,
            'waktu_mulai' => $data['waktu_mulai'],
            'waktu_selesai' => $data['waktu_selesai'],
        ]);

        return $kontraksi;
    }
}
