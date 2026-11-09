<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;
use Modules\Membership\Models\MembershipRole;
use Modules\Membership\Requests\StoreMemberRequest;
use Modules\Membership\Requests\UpdateMemberRequest;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query()->with(['roles', 'memberships'])->withCount('roles');

        if ($request->boolean('trashed')) {
            abort_unless($request->user()?->role === 'admin', 403);
            $query->onlyTrashed();
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $members = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('membership.members.index', compact('members'));
    }

    public function create()
    {
        return view('membership.members.create', [
            'roles' => MembershipRole::orderBy('name')->get(),
        ]);
    }

    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        $membershipType = $data['membership_type'];
        unset($data['role_ids'], $data['membership_type']);

        DB::transaction(function () use ($data, $roleIds, $membershipType) {
            $nextId = (Member::withTrashed()->max('id') ?? 0) + 1;

            $member = Member::create(array_merge($data, [
                'member_code' => 'MBR-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
                'status' => 'active',
                'active_loans' => 0,
                'joined_at' => now(),
            ]));

            Membership::create([
                'member_id' => $member->id,
                'membership_number' => 'KTA-'.Str::upper(Str::random(10)),
                'type' => $membershipType,
                'status' => 'active',
                'started_at' => now(),
                'ended_at' => now()->addYear(),
            ]);

            if ($roleIds !== []) {
                $member->roles()->attach($roleIds, ['assigned_at' => now()]);
            }
        });

        return redirect()
            ->route('membership.members.index')
            ->with('success', 'Anggota baru berhasil didaftarkan.');
    }

    public function show(Member $member)
    {
        $member->load(['roles', 'memberships']);

        return view('membership.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('membership.members.edit', [
            'member' => $member->load(['roles', 'memberships']),
            'roles' => MembershipRole::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        $membershipType = $data['membership_type'];
        unset($data['role_ids'], $data['membership_type']);

        DB::transaction(function () use ($member, $data, $roleIds, $membershipType) {
            $member->update($data);
            $member->roles()->sync($roleIds);

            $activeMembership = $member->memberships()
                ->where('status', 'active')
                ->latest('started_at')
                ->first();

            if ($activeMembership) {
                $activeMembership->update(['type' => $membershipType]);
            }
        });

        return redirect()
            ->route('membership.members.show', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        if ($member->active_loans > 0) {
            return back()->with('error', 'Anggota masih memiliki peminjaman aktif.');
        }

        $member->delete();

        return redirect()
            ->route('membership.members.index')
            ->with('success', 'Anggota dipindahkan ke sampah (soft delete).');
    }

    public function restore(Member $member)
    {
        $member->restore();

        return redirect()
            ->route('membership.members.index')
            ->with('success', 'Anggota berhasil dipulihkan.');
    }
}
