<?php

namespace App\Services;

use App\Models\CatatanPartograf;
use Illuminate\Support\Str;

class CatatanPartografService
{
    // Buat catatan baru
    public function create(array $data)
    {
        $validatedData = CatatanPartograf::validateData($data);

        // Logika bisnis: generate ID & waktu catat
        $validatedData['id'] = $validatedData['id'] ?? now()->format('YmdHis') . Str::random(7);
        $validatedData['waktu_catat'] = $validatedData['waktu_catat'] ?? now()->toDateTimeString();

        return CatatanPartograf::create($validatedData);
    }

    // Ambil semua catatan berdasarkan partograf_id
    public function getByPartografId(string $partografId)
    {
        return CatatanPartograf::with('kontraksi')
            ->where('partograf_id', $partografId)
            ->orderBy('waktu_catat', 'asc')
            ->get();
    }
}
