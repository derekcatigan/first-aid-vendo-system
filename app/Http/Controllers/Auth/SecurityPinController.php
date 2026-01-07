<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecurityPinController extends Controller
{
    // Admin: update PIN
    public function store(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6'
        ]);

        SecurityPin::truncate(); // only ONE active PIN

        SecurityPin::create([
            'pin_hash' => Hash::make($request->pin)
        ]);

        return response()->json([
            'message' => 'Security PIN updated successfully'
        ]);
    }

    // API: Arduino validation
    public function validatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6'
        ]);

        $record = SecurityPin::latest()->first();

        if (!$record || !Hash::check($request->pin, $record->pin_hash)) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 401);
        }

        return response()->json([
            'message' => 'PIN verified'
        ]);
    }
}
