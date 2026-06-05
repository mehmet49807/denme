<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->q, fn ($q) => $q->where('username', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%"))
            ->when($request->gender, fn ($q) => $q->where('gender', $request->gender))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'string', 'max:100'],
            'email'      => ['sometimes', 'email', "unique:users,email,{$user->id}"],
            'phone'      => ['sometimes', 'string', 'max:30'],
            'city'       => ['sometimes', 'string', 'max:80'],
            'district'   => ['sometimes', 'string', 'max:80'],
        ]);

        $user->update($data); // username stays read-only - never updated.

        return back()->with('status', 'Kullanıcı güncellendi.');
    }

    public function ban(User $user): RedirectResponse
    {
        $user->update(['status' => $user->status === 'banned' ? 'active' : 'banned']);

        return back()->with('status', 'Kullanıcı durumu güncellendi.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Kullanıcı silindi.');
    }
}
