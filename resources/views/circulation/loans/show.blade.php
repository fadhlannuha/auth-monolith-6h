<x-app-layout>
    @php($previewFine = $loan->returned_at === null ? $loan->lateFine() : null)

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $loan->loan_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $loan->member_name }} ({{ $loan->member_code }}) &middot;
                    {{ $loan->borrowed_at?->format('d M Y H:i') }}
                </p>
            </div>
            <div class="flex gap-2">
                @if ($loan->trashed() && auth()->user()->role === 'admin')
                    <form method="POST" action="{{ route('circulation.loans.restore', $loan) }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">Pulihkan Peminjaman</button>
                    </form>
                @endif
                <a href="{{ route('circulation.loans.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                                <div class="mt-1">
                                    @if ($loan->returned_at)
                                        <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Dikembalikan</span>
                                    @elseif ($loan->isOverdue())
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Terlambat</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">Dipinjam</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jatuh Tempo</div>
                                <div class="mt-1 text-sm text-gray-800">{{ $loan->due_at?->format('d M Y') }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Denda</div>
                                <div class="mt-1 text-sm text-gray-800">Rp {{ number_format((float) $loan->fine_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        @if ($loan->notes)
                            <div class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-700">{{ $loan->notes }}</div>
                        @endif
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Detail Peminjaman</h3>
                            <p class="text-sm text-gray-500">Data buku disimpan sebagai snapshot (judul dan ISBN).</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-6 py-3">Buku</th>
                                        <th class="px-6 py-3">ISBN</th>
                                        <th class="px-6 py-3">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($loan->items as $item)
                                        <tr>
                                            <td class="px-6 py-3 font-medium text-gray-900">{{ $item->book_title }}</td>
                                            <td class="px-6 py-3 text-gray-700">{{ $item->book_isbn }}</td>
                                            <td class="px-6 py-3 text-gray-700">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($loan->returnRecord)
                        <div class="rounded-lg bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">Pengembalian</h3>
                            <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Dikembalikan</dt>
                                    <dd class="mt-1 text-sm text-gray-800">{{ $loan->returnRecord->returned_at?->format('d M Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hari Terlambat</dt>
                                    <dd class="mt-1 text-sm text-gray-800">{{ $loan->returnRecord->days_late }} hari</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Harian</dt>
                                    <dd class="mt-1 text-sm text-gray-800">Rp {{ number_format((float) $loan->returnRecord->daily_rate, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Denda</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">Rp {{ number_format((float) $loan->returnRecord->fine_amount, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                            @if ($loan->returnRecord->notes)
                                <p class="mt-3 rounded-md bg-gray-50 p-3 text-sm text-gray-700">{{ $loan->returnRecord->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    @if ($loan->returned_at === null)
                        <div class="rounded-lg bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900">Kembalikan Buku</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Perkiraan denda saat ini:
                                <span class="font-semibold text-gray-900">{{ $previewFine }}</span>
                                ({{ $previewFine->getDaysLate() }} hari terlambat)
                            </p>
                            <form method="POST" action="{{ route('circulation.loans.return', $loan) }}" class="mt-4 space-y-3">
                                @csrf
                                <div>
                                    <x-input-label for="return_notes" value="Catatan Pengembalian" />
                                    <textarea id="return_notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                                <x-primary-button>Proses Pengembalian</x-primary-button>
                            </form>
                        </div>
                    @endif

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">Log Domain Event</h3>
                        <p class="mt-1 text-sm text-gray-500">Ditulis listener <code>RecordBookBorrowed</code>.</p>
                        <ul class="mt-3 space-y-3 text-sm">
                            @forelse ($loan->logs as $log)
                                <li class="rounded-md border border-gray-200 p-3">
                                    <div class="font-medium text-gray-800">{{ $log->message }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $log->created_at?->format('d M Y H:i:s') }}</div>
                                </li>
                            @empty
                                <li class="text-gray-500">Belum ada log untuk peminjaman ini.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
