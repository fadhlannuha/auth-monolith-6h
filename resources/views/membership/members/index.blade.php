<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Anggota Perpustakaan</h2>
                <p class="mt-1 text-sm text-gray-500">Membership Context &mdash; anggota, role, dan keanggotaan.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('membership.roles.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Role Anggota</a>
                <a href="{{ route('membership.members.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Daftarkan Anggota</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash-message />

            @if (request()->boolean('trashed'))
                <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Menampilkan anggota yang sudah di-soft delete.
                    <a href="{{ route('membership.members.index') }}" class="font-semibold underline">Kembali ke data aktif</a>
                </div>
            @endif

            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('membership.members.index') }}" class="grid gap-3 md:grid-cols-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, atau email"
                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                    <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua status</option>
                        @foreach (['active' => 'Aktif', 'borrowing' => 'Meminjam', 'suspended' => 'Ditangguhkan', 'inactive' => 'Nonaktif'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-900">Filter</button>
                        <a href="{{ route('membership.members.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Reset</a>
                    </div>
                </form>

                @if (auth()->user()->role === 'admin')
                    <div class="mt-3 text-sm">
                        @if (request()->boolean('trashed'))
                            <a href="{{ route('membership.members.index') }}" class="text-indigo-600 hover:underline">Data aktif</a>
                        @else
                            <a href="{{ route('membership.members.index', ['trashed' => 1]) }}" class="text-amber-600 hover:underline">Sampah</a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Anggota</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Pinjaman Aktif</th>
                                <th class="px-4 py-3">Keanggotaan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($members as $member)
                                @php($activeMembership = $member->memberships->firstWhere('status', 'active'))
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('membership.members.show', $member) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $member->name }}</a>
                                        <div class="text-xs text-gray-500">{{ $member->member_code }} &middot; {{ $member->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $member->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        @php($badge = match ($member->status) {
                                            'active' => 'bg-green-100 text-green-700',
                                            'borrowing' => 'bg-blue-100 text-blue-700',
                                            'suspended' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        })
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $badge }}">{{ $member->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $member->active_loans }} / {{ $member->max_loans }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($activeMembership)
                                            {{ ucfirst($activeMembership->type) }}
                                            <div class="text-xs text-gray-500">s.d. {{ $activeMembership->ended_at?->format('d M Y') }}</div>
                                        @else
                                            <span class="text-red-600">Tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('membership.members.show', $member) }}" class="text-indigo-600 hover:underline">Lihat</a>
                                            @if ($member->trashed())
                                                @if (auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('membership.members.restore', $member) }}">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:underline">Pulihkan</button>
                                                    </form>
                                                @endif
                                            @else
                                                <a href="{{ route('membership.members.edit', $member) }}" class="text-gray-600 hover:underline">Edit</a>
                                                <form method="POST" action="{{ route('membership.members.destroy', $member) }}" onsubmit="return confirm('Soft delete anggota ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada anggota.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-4 py-3">{{ $members->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
