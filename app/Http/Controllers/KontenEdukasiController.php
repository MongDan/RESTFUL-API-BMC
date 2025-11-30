<?php

namespace App\Http\Controllers;

use App\Models\Bidan;
use App\Services\KontenEdukasiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KontenEdukasiController extends Controller
{
    private KontenEdukasiService $service;

    public function __construct(KontenEdukasiService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/konten-edukasi
     * List konten edukasi untuk pasien (tanpa auth bidan).
     */
    public function index()
    {
        $konten = $this->service->listKontenUntukPasien();

        return response()->json([
            'status' => 'success',
            'data'   => $konten,
        ]);
    }

    /**
     * POST /api/konten-edukasi
     * Buat konten edukasi (hanya bidan, butuh JWT).
     * Body: { "judulKonten": "...", "isiKonten": "..." }
     */
    // app/Http/Controllers/KontenEdukasiController.php

    public function store(Request $request)
    {
        $request->validate([
            'judul_konten' => 'required|string|max:255',
            'isi_konten'   => 'required|string',
        ]);

        // Ambil Objek Bidan Full (bukan cuma ID)
        $bidan = $request->auth_user; 

        try {
            // Kirim Objek Bidan ke Service
            $konten = $this->service->buatKonten($bidan, $request->only('judul_konten', 'isi_konten'));

            return response()->json([
                'status' => 'success',
                'message' => 'Konten edukasi berhasil dibuat.',
                'data' => $konten,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * DELETE /api/konten-edukasi/{id}
     * Hapus konten edukasi milik bidan.
     */
    public function destroy(Request $request, string $id) // Tambah Request $request
    {
        $bidan = $request->auth_user; // Ambil dari middleware

        try {
            $this->service->hapusKonten($bidan, $id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Konten edukasi berhasil dihapus.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menghapus konten.',
            ], 500);
        }
    }
    // app/Http/Controllers/Api/KontenEdukasiController.php

    public function show(string $id)
    {
        try {
            $konten = $this->service->detailKontenEduksi($id);

            return response()->json([
                'status' => 'success',
                'data'   => $konten,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat mengambil konten.',
            ], 500);
        }
    }

}
