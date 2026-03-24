<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function update(Request $request)
    {
        $settings = $request->except('_token');
        
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Sozlamalar muvaffaqiyatli saqlandi']);
    }

    public function getGroup($group)
    {
        return Setting::where('group', $group)->pluck('value', 'key');
    }
}
