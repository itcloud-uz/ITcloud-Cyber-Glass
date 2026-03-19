<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required',
            'passport_number' => 'nullable',
            'is_face_id_enabled' => 'boolean'
        ]);

        $data['password'] = Hash::make($data['password']);

        // Agar fayl kelgan bo'lsa
        if ($request->hasFile('face_id_photo')) {
            $path = $request->file('face_id_photo')->store('face_ids', 'public');
            $data['face_id_photo_path'] = $path;
        }

        $employee = User::create($data);

        return response()->json(['status' => 'success', 'employee' => $employee]);
    }
}
