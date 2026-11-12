<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->orderBy('id')->paginate(15),
            'pages' => config('page_access.pages'),
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'managedUser' => null,
            'pages' => config('page_access.pages'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'accessible_pages' => ['sometimes', 'array'],
            'accessible_pages.*' => ['string', Rule::in(array_keys(config('page_access.pages')))],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_admin' => false,
            'accessible_pages' => $validated['accessible_pages'] ?? [],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'managedUser' => $user,
            'pages' => config('page_access.pages'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'accessible_pages' => ['sometimes', 'array'],
            'accessible_pages.*' => ['string', Rule::in(array_keys(config('page_access.pages')))],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        if (! $user->is_admin) {
            $user->accessible_pages = $validated['accessible_pages'] ?? [];
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
