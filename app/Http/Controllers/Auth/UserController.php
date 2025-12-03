<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);


        return view('manage-user', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,worker',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => Str::title($request->fullName),
                'email' => $request->email,
                'role' => $request->role,
                'password' => $request->password,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);

            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete user'
            ], 500);
        }
    }
}
