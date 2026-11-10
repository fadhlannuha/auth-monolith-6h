<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penerbit</h2>
            <a href="{{ route('catalog.books.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali ke Katalog</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Tambah Penerbit</h3>
                    <form method="POST" action="{{ route('catalog.publishers.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Nama Penerbit" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Telepon" />
                            <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone')" />
                        </div>
                        <div>
                            <x-input-label for="address" value="Alamat" />
                            <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address') }}</textarea>
                        </div>
                        <x-primary-button>Simpan Penerbit</x-primary-button>
                    </form>
                </div>

                <div class="rounded-lg bg-white shadow-sm lg:col-span-2">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari penerbit"
                                   class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white">Cari</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nama</th>
                                    <th class="px-5 py-3">Kontak</th>
                                    <th class="px-5 py-3">Buku</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($publishers as $publisher)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 font-medium text-gray-900">{{ $publisher->name }}</td>
                                        <td class="px-5 py-3 text-gray-700">
                                            {{ $publisher->phone ?: '-' }}
                                            <div class="max-w-xs truncate text-xs text-gray-500">{{ $publisher->address }}</div>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700">{{ $publisher->books_count }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('catalog.publishers.destroy', $publisher) }}" onsubmit="return confirm('Hapus penerbit ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada penerbit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-3">{{ $publishers->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
