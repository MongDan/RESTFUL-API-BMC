<?php

namespace App\Services;

use App\Models\Pesan;
use Illuminate\Support\Str;

class PesanService
{
    // Kirim pesan
    public function kirimPesan(string $isi, string $pengirimId, string $penerimaId): Pesan
    {
        return Pesan::create([
            'id' => (string) Str::uuid(),
            'isi_pesan' => $isi,
            'waktu_kirim' => now(),
            'pengirim_id' => $pengirimId,
            'penerima_id' => $penerimaId,
        ]);
    }

    // Ambil semua pesan antara bidan dan pasien tertentu
    public function ambilPesan(string $bidanId, string $pasienId)
    {
        $pesans = Pesan::where(function($q) use ($bidanId, $pasienId) {
            $q->where('pengirim_id', $bidanId)
              ->where('penerima_id', $pasienId);
        })->orWhere(function($q) use ($bidanId, $pasienId) {
            $q->where('pengirim_id', $pasienId)
              ->where('penerima_id', $bidanId);
        })->orderBy('waktu_kirim')
          ->get();

        return $pesans->map(function($pesan) {
            return [
                'id' => $pesan->id,
                'isi_pesan' => $pesan->isi_pesan,
                'waktu_kirim' => $pesan->waktu_kirim,
                'pengirim_nama' => $pesan->pengirim()?->nama,
                'penerima_nama' => $pesan->penerima()?->nama,
            ];
        });
    }
}
