<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $member->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $member->member_code }} &middot; {{ $member->email }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('circulation.loans.index', ['member' => $member->id]) }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Riwayat Pinjaman</a>
                <a href="{{ route('membership.members.edit', $member) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Edit</a>
                <a href="{{ route('membership.members.index') }}" class="rounded-md bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900">Profil Anggota</h3>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $member->status }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pinjaman Aktif</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $member->active_loans }} / {{ $member->max_loans }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $member->phone ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Terdaftar Sejak</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $member->joined_at?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Alamat</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $member->address ?: '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-900">Role Anggota</h4>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($member->roles as $role)
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                    {{ $role->name }} ({{ $role->pivot->assigned_at }})
                                </span>
                            @empty
                                <span class="text-sm text-gray-500">Belum ada role.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Keanggotaan</h3>
                    <ul class="mt-3 space-y-3 text-sm">
                        @forelse ($member->memberships as $membership)
                            <li class="rounded-md border border-gray-200 p-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">{{ ucfirst($membership->type) }}</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $membership->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $membership->status }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">{{ $membership->membership_number }}</div>
                                <div class="text-xs text-gray-500">{{ $membership->started_at?->format('d M Y') }} &ndash; {{ $membership->ended_at?->format('d M Y') }}</div>
                            </li>
                        @empty
                            <li class="text-gray-500">Belum ada data keanggotaan.</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('membership.members.destroy', $member) }}" class="mt-6" onsubmit="return confirm('Soft delete anggota ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Soft delete anggota</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
