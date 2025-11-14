<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CatatanPartografService;

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
}
