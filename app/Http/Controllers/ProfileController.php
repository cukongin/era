<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => [
                'nullable',
                'min:8',             // Minimum 8 characters
                'regex:/[a-z]/',      // Must contain at least one lowercase letter
                'regex:/[0-9]/',      // Must contain at least one digit
                'confirmed'
            ]
        ], [
            'password.min' => 'Password minimal 8 karakter!',
            'password.regex' => 'Password harus kombinasi huruf dan angka!'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}
