<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penulis</h2>
            <a href="{{ route('catalog.books.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali ke Katalog</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Tambah Penulis</h3>
                    <form method="POST" action="{{ route('catalog.authors.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Nama Penulis" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="bio" value="Biografi Singkat" />
                            <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('bio') }}</textarea>
                        </div>
                        <x-primary-button>Simpan Penulis</x-primary-button>
                    </form>
                </div>

                <div class="rounded-lg bg-white shadow-sm lg:col-span-2">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari penulis"
                                   class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white">Cari</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nama</th>
                                    <th class="px-5 py-3">Jumlah Buku</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($authors as $author)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <span class="font-medium text-gray-900">{{ $author->name }}</span>
                                            <div class="max-w-md truncate text-xs text-gray-500">{{ $author->bio }}</div>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700">{{ $author->books_count }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('catalog.authors.destroy', $author) }}" onsubmit="return confirm('Hapus penulis ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-10 text-center text-gray-500">Belum ada penulis.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-3">{{ $authors->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
