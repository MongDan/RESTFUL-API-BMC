<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CatatanPartografService;
use App\Models\CatatanPartograf;
use App\Models\Partograf;
use App\Models\Persalinan;

class CatatanPartografController extends Controller
{
    protected $service;

    public function __construct(CatatanPartografService $service)
    {
        $this->service = $service;
    }

    public function buatCatatanPartograf(Request $request, $id)
    {
        $data = $request->all();

        $data['partograf_id'] = $id;

        $catatan = $this->service->create($data);

        return response()->json([
            'message' => 'Catatan partograf berhasil dibuat',
            'data' => $catatan
        ], 201);
    }

    public function getCatatanByPartograf($id)
    {
        $catatan = $this->service->getByPartografId($id);

        if ($catatan->isEmpty()) {
            return response()->json(['message' => 'Belum ada catatan untuk partograf ini'], 404);
        }

        return response()->json([
            'data' => $catatan
        ]);
    }

  public function getAllCatatanPartografPasien($noReg)
{
    $persalinanList = Persalinan::where('pasien_no_reg', $noReg)->get();

    if ($persalinanList->isEmpty()) {
        return response()->json([
            'message' => 'Pasien belum memiliki persalinan'
        ], 404);
    }

    $allCatatan = collect();

    foreach ($persalinanList as $persalinan) {
        // Ambil partograf
        $partograf = $persalinan->partograf;
        if ($partograf) {
            // Ambil semua catatan partograf terkait partograf ini
            $catatan = CatatanPartograf::with('kontraksi')
                ->where('partograf_id', $partograf->id)
                ->orderBy('waktu_catat', 'asc') // urut dari awal
                ->get();

            $allCatatan = $allCatatan->concat($catatan);
        }
    }

    if ($allCatatan->isEmpty()) {
        return response()->json([
            'message' => 'Belum ada catatan partograf untuk pasien ini'
        ], 404);
    }

    return response()->json([
        'message' => 'Semua catatan partograf pasien ditemukan',
        'data' => $allCatatan
    ], 200);
}



}
