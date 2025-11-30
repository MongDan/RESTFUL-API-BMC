<?php

namespace App\Services;

use App\Models\Bidan;
use App\Models\KontenEdukasi;
use Illuminate\Validation\ValidationException;

class KontenEdukasiService
{

    public function buatKonten(Bidan $bidan, array $data): KontenEdukasi
    {
        // 1. Validasi Input (Logic Bisnis)
        $judul = $data['judul_konten'] ?? null;
        $isi = $data['isi_konten'] ?? null;

        if (!$judul || !$isi) {
            throw ValidationException::withMessages([
                'judul_konten' => ['Judul konten wajib diisi.'],
                'isi_konten' => ['Isi konten wajib diisi.'],
            ]);
        }

        // 2. Panggil Behavior Model Bidan (Sesuai Diagram)
        return $bidan->buatKonten([
            'judul_konten' => $judul,
            'isi_konten' => $isi
        ]);
    }

    public function hapusKonten(Bidan $bidan, string $kontenId): void
    {
        // Langsung delegasi ke Model Bidan
        $bidan->hapusKonten($kontenId);
    }

    public function listKontenUntukPasien()
    {
        // bisa ditambah filter publish / kategori nanti
        return KontenEdukasi::orderBy('created_at', 'desc')->get();
    }

    public function listKontenUntukBidan(Bidan $bidan)
    {
        return KontenEdukasi::where('bidan_id', $bidan->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function detailKontenEduksi(string $kontenId): KontenEdukasi
    {
        // Cari konten berdasarkan ID
        $konten = KontenEdukasi::where('id', $kontenId)->first();

        if (!$konten) {
            throw ValidationException::withMessages([
                'konten' => ['Konten edukasi tidak ditemukan.'],
            ]);
        }

        return $konten;
    }

}
