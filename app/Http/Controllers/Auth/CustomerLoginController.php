<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerLoginController extends Controller
{
    public function registerForm()
    {
        return view('auth.register');
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);
        $rolecustomer = Role::where('name', 'customer')->first();
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $rolecustomer->id,
        ]);

        return redirect()->route('login')->with('success', 'Register Berhasil,Silahkan Login');
    }

    public function loginForm()
    {
        return view('auth.login');
    }
    // Peroses login
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            if (Auth::user()->role->name === 'admin') {
                // This is a preventive measure to stop admins from accessing customer accounts.
                Auth::logout();
                return back();
            }
            return redirect('/');
        }
        return back()->withErrors([
            'email' => 'Incorrect password or email',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
