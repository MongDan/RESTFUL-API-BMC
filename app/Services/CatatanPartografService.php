<?php

namespace App\Services;

use App\Models\CatatanPartograf;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Persalinan;
use App\Models\Partograf;

class CatatanPartografService
{
    // Buat catatan baru
    public function create(array $data)
{
    $validatedData = CatatanPartograf::validateData($data);

    // Logika bisnis: generate ID
    $validatedData['id'] = $validatedData['id'] ?? now()->format('YmdHis') . Str::random(7);

    // Waktu catat: pakai dari input jika ada, else pakai waktu sekarang
    if (!empty($data['waktu_catat'])) {
        // Parse format ISO dari React Native (2025-11-22T21:00:00) ke format SQL (2025-11-22 21:00:00)
        $validatedData['waktu_catat'] = Carbon::parse($data['waktu_catat'])->format('Y-m-d H:i:s');
    } else {
        $validatedData['waktu_catat'] = now()->toDateTimeString();
    }

    return CatatanPartograf::create($validatedData);
}

    // Ambil semua catatan berdasarkan partograf_id
    public function getAllCatatanPartografPasien(string $noReg)
{
    $persalinanList = Persalinan::where('pasien_no_reg', $noReg)->get();

    if ($persalinanList->isEmpty()) {
        return null;
    }

    $all = collect();

    foreach ($persalinanList as $persalinan) {
        $partograf = $persalinan->partograf;
        if (!$partograf) continue;

        $catatan = CatatanPartograf::with('kontraksi')
            ->where('partograf_id', $partograf->id)
            ->orderBy('waktu_catat', 'asc')
            ->get()
            ->map(function ($item) {

                return [
                    'waktu_catat' => $item->waktu_catat,
                    'pembukaan_servik' => $item->pembukaan_servik,
                    'djj' => $item->djj,
                    'sistolik' => $item->sistolik,
                    'diastolik' => $item->diastolik,
                    'nadi_ibu' => $item->nadi_ibu,
                    'suhu_ibu' => $item->suhu_ibu,
                    'oksitosin' => $item->oksitosin,
                    'total_tetes' => $item->total_tetes,
                    'kontraksi' => $item->kontraksi ?? null,
                ];
            });

        $all = $all->concat($catatan);
    }

    return $all->isEmpty() ? null : $all;
}

public function getByPartografId(string $partografId)
    {
        // 1. Cari Partograf induknya dulu
        $partograf = Partograf::find($partografId);

        if (!$partograf) {
            return collect([]); // Kembalikan collection kosong jika partograf tidak ada
        }

        // 2. Delegasikan pengambilan data ke Model Partograf
        // Sesuai diagram: Partograf -> getDaftarCatatan()
        return $partograf->getDaftarCatatan();
    }
}
