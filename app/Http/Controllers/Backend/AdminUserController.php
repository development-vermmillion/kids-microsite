<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('backend.admins.index', ['admins' => User::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('backend.admins.form', ['admin' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create($data);

        return redirect()->route('admin.admins.index')->with('success', 'Admin added. They can sign in at /admin now.');
    }

    public function edit(User $admin): View
    {
        return view('backend.admins.form', compact('admin'));
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated.');
    }

    public function destroy(Request $request, User $admin): RedirectResponse
    {
        if ($admin->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account while signed in.');
        }

        if (User::count() <= 1) {
            return back()->with('error', 'Keep at least one admin account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin removed.');
    }
}
