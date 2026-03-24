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

        if ($request->hasFile('face_id_photo')) {
            $path = $request->file('face_id_photo')->store('face_ids', 'public');
            $data['face_id_photo_path'] = $path;
        }

        $employee = User::create($data);

        return response()->json(['status' => 'success', 'employee' => $employee]);
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required',
            'passport_number' => 'nullable',
            'password' => 'nullable|min:6'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('face_id_photo')) {
            if ($employee->face_id_photo_path) {
                Storage::disk('public')->delete($employee->face_id_photo_path);
            }
            $path = $request->file('face_id_photo')->store('face_ids', 'public');
            $data['face_id_photo_path'] = $path;
        }

        $employee->update($data);

        return response()->json(['status' => 'success', 'employee' => $employee]);
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        
        // Don't delete self
        if (auth()->id() == $id) {
            return response()->json(['status' => 'error', 'message' => 'O\'zingizni o\'chira olmaysiz'], 403);
        }

        if ($employee->face_id_photo_path) {
            Storage::disk('public')->delete($employee->face_id_photo_path);
        }

        $employee->delete();

        return response()->json(['status' => 'success']);
    }
}
