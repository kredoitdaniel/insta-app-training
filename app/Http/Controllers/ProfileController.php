<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function show($id)
    {
        $user = $this->user->findOrFail($id);

        return view('users.profile.show')
                ->with('user', $user);
    }


    public function edit()
    {
        $user = $this->user->findOrFail(Auth::user()->id);

        return view('users.profile.edit')
                ->with('user', $user);
    }


    public function update(Request $request)
    {
        $request->validate([
            'name'          => 'required|min:1|max:50',
            'email'         => 'required|email|max:50|unique:users,email,' . Auth::user()->id,
            'avatar'        => 'mimes:jpg,jpeg,gif,png|max:1048',
            'introduction'  => 'max:100'
        ]);

        $user               = $this->user->findOrFail(Auth::user()->id);
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->introduction = $request->introduction;

        if ($request->avatar){
            $user->avatar = 'data:image/' . $request->avatar->extension() . ';base64,' . base64_encode(file_get_contents($request->avatar));
        }

        # Save
        $user->save();

        return redirect()->route('profile.show', Auth::user()->id);
    }


    public function followers($id)
    {
        $user = $this->user->findOrFail($id);

        return view('users.profile.followers')
                ->with('user', $user);
    }


    public function following($id)
    {
        $user = $this->user->findOrFail($id);

        return view('users.profile.following')
                ->with('user', $user);
    }


    public function updatePassword(Request $request)
    {
        if (!Hash::check($request->current_password, Auth::user()->password)){
            return redirect()->back()
                    ->with('current_password_error', 'That\'s not your current password. Try again.')
                    ->with('error_password', 'Unable to change your password.');
        }

        if ($request->current_password === $request->new_password){
            return redirect()->back()
                    ->with('new_password_error', 'New Password cannot be the same as your current password. Try again.')
                    ->with('error_password', 'Unable to change your password.');
        }

        $request->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]
        ]);

        $user               = $this->user->findOrFail(Auth::user()->id);     
        $user->password     = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()
                ->with('success_password', 'Password changed successfully.');
    }
}
