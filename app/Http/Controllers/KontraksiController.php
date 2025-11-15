<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KontraksiService;

class KontraksiController extends Controller
{
    protected $service;

    public function __construct(KontraksiService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request, $catatanPartografId)
    {
        $validated = $request->validate([
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date',
        ]);

        $kontraksi = $this->service->create($catatanPartografId, $validated);

        return response()->json([
            'message' => 'Kontraksi dicatat',
            'data' => [
                'id' => $kontraksi->id,
                'durasi' => $kontraksi->getDurasi(),
            ]
        ]);
    }
}
