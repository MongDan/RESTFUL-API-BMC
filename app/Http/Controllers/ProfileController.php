<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    // 🔍 Lihat Profil
   public function show(Request $request)
    {
        $data = [];

        // 1. Cek apakah user login sebagai Bidan
        if ($user = auth('bidan')->user()) {
            $data = [
                'id' => $user->id, // Pastikan ini nama kolom ID bidan (misal: id atau kode_bidan)
                'username' => $user->username,
            ];
        } 
        // 2. Cek apakah user login sebagai Pasien
        elseif ($user = auth('pasien')->user()) {
            $data = [
                'no_reg' => $user->no_reg,
                'username' => $user->username,
                'alamat' => $user->alamat,
                'umur' => $user->umur,
            ];
        } 
        // 3. Jika tidak ditemukan di kedua guard
        else {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau user tidak ditemukan',
            ], 401);
        }

        // 4. Return respon sukses
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }


    public function update(Request $request)
{
    $user = auth('bidan')->user() ?? auth('pasien')->user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User tidak ditemukan atau token tidak valid',
        ], 401);
    }

    if ($user instanceof \App\Models\Bidan) {
        $validated = $request->validate([
            'username' => 'sometimes|string|max:25|unique:bidan,username,' . $user->id . ',id',
        ]);
    } else {
        $validated = $request->validate([
            'username' => 'sometimes|string|max:25|unique:pasien,username,' . $user->no_reg . ',no_reg',
            'alamat'   => 'sometimes|string|max:25',
            'umur'     => 'sometimes|numeric',
        ]);
    }

    if (empty($validated)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada data yang diubah',
        ], 400);
    }

    // Update data
    $user->update($validated);

    if ($user instanceof \App\Models\Pasien) {
        $data = [
            'no_reg' => $user->no_reg,
            'username' => $user->username,
            'alamat' => $user->alamat,
            'umur' => $user->umur,
        ];
    } else {
        $data = $user;
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Profil berhasil diperbarui',
        'data' => $data
    ]);
}


public function updatePassword(Request $request)
{
    // Ambil user dari token JWT (dari cookie / header)
    try {
        $user = auth('bidan')->user() ?? auth('pasien')->user();
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token tidak valid atau user tidak ditemukan',
        ], 401);
    }

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User tidak ditemukan',
        ], 404);
    }

    // ✅ Validasi input
    try {
    $request->validate([
        'password_lama' => 'required|string',
        'password_baru' => 'required|string|min:6|confirmed',
    ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
       $messages = collect($e->errors())->flatten()->first();
        return response()->json([
            'status' => 'error',
            'message' => $messages,
        ], 422);
    }

    // 🔍 Cek password lama
    if (!Hash::check($request->password_lama, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Password lama salah',
        ], 400);
    }

    $user->password = Hash::make($request->password_baru);
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Password berhasil diubah',
    ]);
}
}
