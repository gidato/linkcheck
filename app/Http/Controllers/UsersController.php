<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function profile(Request $request) {
        return view('users.show',['user' => $request->user()]);
    }

    public function editPassword() {
        return view('users.edit-password');
    }

    public function updatePassword(ChangePasswordRequest $request) {
        $validated = $request->validated();
        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();
        $request->session()->flash('success', 'Password updated!');
        return redirect('profile');
    }

}
