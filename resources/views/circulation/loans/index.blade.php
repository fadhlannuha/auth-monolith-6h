<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Circulation Context &mdash; peminjaman, pengembalian, dan denda.</p>
            </div>
            <a href="{{ route('circulation.loans.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Buat Peminjaman</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-blue-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Peminjaman Aktif</div>
                    <div class="mt-1 text-2xl font-bold text-blue-900">{{ $stats['active'] }}</div>
                </div>
                <div class="rounded-lg bg-red-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-red-700">Terlambat</div>
                    <div class="mt-1 text-2xl font-bold text-red-900">{{ $stats['overdue'] }}</div>
                </div>
                <div class="rounded-lg bg-green-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-green-700">Sudah Kembali</div>
                    <div class="mt-1 text-2xl font-bold text-green-900">{{ $stats['returned'] }}</div>
                </div>
                <div class="rounded-lg bg-indigo-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Log Domain Event</div>
                    <div class="mt-1 text-2xl font-bold text-indigo-900">{{ $stats['logs'] }}</div>
                </div>
            </div>

            @if (request()->boolean('trashed'))
                <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Menampilkan peminjaman yang sudah di-soft delete.
                    <a href="{{ route('circulation.loans.index') }}" class="font-semibold underline">Kembali ke data aktif</a>
                </div>
            @endif

            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('circulation.loans.index') }}" class="grid gap-3 md:grid-cols-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor, nama, atau kode anggota"
                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                    <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua status</option>
                        @foreach (['borrowed' => 'Dipinjam', 'overdue' => 'Terlambat', 'returned' => 'Dikembalikan'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-900">Filter</button>
                        <a href="{{ route('circulation.loans.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Reset</a>
                    </div>
                </form>

                @if (auth()->user()->role === 'admin')
                    <div class="mt-3 text-sm">
                        @if (request()->boolean('trashed'))
                            <a href="{{ route('circulation.loans.index') }}" class="text-indigo-600 hover:underline">Data aktif</a>
                        @else
                            <a href="{{ route('circulation.loans.index', ['trashed' => 1]) }}" class="text-amber-600 hover:underline">Sampah</a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Peminjaman</th>
                                <th class="px-4 py-3">Anggota</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Buku</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Denda</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($loans as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('circulation.loans.show', $loan) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $loan->loan_number }}</a>
                                        <div class="text-xs text-gray-500">{{ $loan->borrowed_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $loan->member_name }}
                                        <div class="text-xs text-gray-500">{{ $loan->member_code }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        Jatuh tempo {{ $loan->due_at?->format('d M Y') }}
                                        @if ($loan->returned_at)
                                            <div class="text-xs text-green-600">Kembali {{ $loan->returned_at->format('d M Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $loan->items_count }} judul / {{ $loan->items->sum('quantity') }} buku</td>
                                    <td class="px-4 py-3">
                                        @if ($loan->returned_at)
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Dikembalikan</span>
                                        @elseif ($loan->isOverdue())
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Terlambat</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">Dipinjam</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">Rp {{ number_format((float) $loan->fine_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('circulation.loans.show', $loan) }}" class="text-indigo-600 hover:underline">Lihat</a>
                                            @if ($loan->trashed())
                                                @if (auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('circulation.loans.restore', $loan) }}">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:underline">Pulihkan</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Belum ada peminjaman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-4 py-3">{{ $loans->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
