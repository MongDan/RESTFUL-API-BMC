<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PersalinanService;
use App\Models\Persalinan;

class PersalinanController extends Controller
{
    protected $service;

    public function __construct(PersalinanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = $request->auth_user;

        if (isset($user->no_reg)) {
            $persalinan = $this->service->listByPasien($user->no_reg);
        } else {
            $persalinan = Persalinan::with('pasien')->get();
        }

        return response()->json($persalinan);
    }

   public function ubahStatus(Request $request, $id)
{
    $persalinan = Persalinan::findOrFail($id);

    $status = $request->status;

    // --- FIELD BAYI YANG HANYA BOLEH DIISI SAAT STATUS = SELESAI ---
    $fieldsBayi = [
        'tanggal_jam_waktu_bayi_lahir',
        'berat_badan',
        'panjang_badan',
        'lingkar_dada',
        'lingkar_kepala',
        'jenis_kelamin'
    ];

    // ❗ JIKA STATUS BUKAN "SELESAI", LARANG INPUT FIELD BAYI
    if ($status !== 'selesai') {
        foreach ($fieldsBayi as $field) {
            if ($request->filled($field)) {
                return response()->json([
                    'message' => "Field '{$field}' tidak boleh diisi sebelum status selesai."
                ], 422);
            }
        }
    }

    // ===============================
    // STATUS AKTIF
    // ===============================
    if ($status === 'aktif') {

        $validated = $request->validate([
            'status' => 'required',
            'tanggal_jam_rawat' => 'required|date',
            'tanggal_jam_mules' => 'required|date',
            'ketuban_pecah' => 'required|boolean',
            'tanggal_jam_ketuban_pecah' => 'nullable|date',
        ]);

        if ($validated['ketuban_pecah'] == false && $request->filled('tanggal_jam_ketuban_pecah')) {
            return response()->json(['message' => 'tanggal jam ketuban pecah tidak boleh diisi jika ketuban belum pecah'], 422);
        }

        if ($validated['ketuban_pecah'] == true && !$request->filled('tanggal_jam_ketuban_pecah')) {
            return response()->json(['message' => 'tanggal jam ketuban pecah wajib diisi jika ketuban sudah pecah'], 422);
        }

        $dataBayi = null;
    }

    // ===============================
    // STATUS SELESAI
    // ===============================
    else if ($status === 'selesai') {

        $validated = $request->validate([
            'tanggal_jam_waktu_bayi_lahir' => 'required|date',
            'berat_badan' => 'required|numeric',
            'panjang_badan' => 'required|numeric',
            'lingkar_dada' => 'required|numeric',
            'lingkar_kepala' => 'required|numeric',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        ]);

        $dataBayi = $validated;
    }

    // ===============================
    // STATUS LAIN
    // ===============================
    else {

        $request->validate([
            'status' => 'required|in:aktif,tidak_aktif,selesai,rujukan'
        ]);

        $dataBayi = null;
    }

    // SIMPAN KE SERVICE
    $result = $this->service->ubahStatus(
        $persalinan,
        $status,
        $dataBayi
    );

    return response()->json([
        'message' => 'Status persalinan berhasil diubah',
        'data' => $result
    ]);
}
}
