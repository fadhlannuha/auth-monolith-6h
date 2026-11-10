<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Peminjaman</h2>
                <p class="mt-1 text-sm text-gray-500">Transaksi atomik: stok dikurangi, record dibuat, status anggota diperbarui.</p>
            </div>
            <a href="{{ route('circulation.loans.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($members === [] || $books === [])
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        @if ($members === [])
                            Belum ada anggota yang memenuhi syarat (aktif, keanggotaan valid, dan belum mencapai batas pinjaman).
                        @endif
                        @if ($books === [])
                            Belum ada buku dengan stok tersedia.
                        @endif
                    </div>
                @else
                    <form method="POST" action="{{ route('circulation.loans.store') }}" x-data='{ items: @json(old("items", [["book_id" => "", "quantity" => 1]])) }'>
                        @csrf

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="member_id" value="Anggota" />
                                <select id="member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Pilih anggota --</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member['id'] }}" @selected((int) old('member_id') === $member['id'])>
                                            {{ $member['member_code'] }} &mdash; {{ $member['name'] }} ({{ $member['active_loans'] }}/{{ $member['max_loans'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="loan_days" value="Durasi Peminjaman (hari)" />
                                <x-text-input id="loan_days" name="loan_days" type="number" min="1" max="30" class="mt-1 block w-full" :value="old('loan_days', 14)" required />
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between">
                                    <x-input-label value="Buku yang Dipinjam" />
                                    <button type="button" @click="items.push({ book_id: '', quantity: 1 })"
                                            class="rounded-md bg-gray-100 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200">
                                        + Tambah Baris
                                    </button>
                                </div>

                                <div class="mt-2 space-y-3">
                                    <template x-for="(item, index) in items" :key="index">
                                        <div class="flex flex-col gap-2 rounded-md border border-gray-200 p-3 sm:flex-row">
                                            <select :name="'items[' + index + '][book_id]'" x-model="item.book_id"
                                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Pilih buku --</option>
                                                @foreach ($books as $book)
                                                    <option value="{{ $book['id'] }}">{{ $book['title'] }} (stok {{ $book['stock'] }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity"
                                                   min="1" max="10" placeholder="Jumlah"
                                                   class="block w-28 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <button type="button" @click="if (items.length > 1) items.splice(index, 1)"
                                                    class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-600 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" value="Catatan (opsional)" />
                                <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <x-primary-button>Proses Peminjaman</x-primary-button>
                            <a href="{{ route('circulation.loans.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
