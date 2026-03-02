<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSettingsController extends Controller
{
    public function get($key)
    {
        $setting = SystemSetting::where('key', $key)->first();
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json($setting->value);
    }

    public function update(Request $request, $key)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $setting = SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $request->value]
        );

        return response()->json($setting->value);
    }

    public function getAll()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }
}
