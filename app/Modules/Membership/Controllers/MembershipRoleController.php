<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Membership\Models\MembershipRole;

class MembershipRoleController extends Controller
{
    public function index()
    {
        $roles = MembershipRole::query()->withCount('members')->orderBy('name')->get();

        return view('membership.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:membership_roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        MembershipRole::create($data);

        return redirect()
            ->route('membership.roles.index')
            ->with('success', 'Role anggota berhasil ditambahkan.');
    }

    public function destroy(MembershipRole $role)
    {
        if ($role->members()->exists()) {
            return back()->with('error', 'Role masih digunakan oleh anggota.');
        }

        $role->delete();

        return redirect()
            ->route('membership.roles.index')
            ->with('success', 'Role anggota berhasil dihapus.');
    }
}
