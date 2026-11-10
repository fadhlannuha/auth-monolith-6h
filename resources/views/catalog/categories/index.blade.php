<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kategori Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Relasi self-referencing: kategori induk dan sub-kategori.</p>
            </div>
            <a href="{{ route('catalog.books.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali ke Katalog</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            <x-flash-message />

            @if (request()->boolean('trashed'))
                <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Menampilkan kategori yang sudah di-soft delete.
                    <a href="{{ route('catalog.categories.index') }}" class="font-semibold underline">Kembali</a>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Tambah Kategori</h3>
                    <form method="POST" action="{{ route('catalog.categories.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Nama Kategori" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="parent_id" value="Kategori Induk (opsional)" />
                            <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Tanpa induk --</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected((int) old('parent_id') === $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="description" value="Deskripsi" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>
                        <x-primary-button>Simpan Kategori</x-primary-button>
                    </form>
                </div>

                <div class="rounded-lg bg-white shadow-sm lg:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori"
                                   class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white">Cari</button>
                        </form>
                        @if (auth()->user()->role === 'admin')
                            @if (request()->boolean('trashed'))
                                <a href="{{ route('catalog.categories.index') }}" class="text-sm text-indigo-600 hover:underline">Data aktif</a>
                            @else
                                <a href="{{ route('catalog.categories.index', ['trashed' => 1]) }}" class="text-sm text-amber-600 hover:underline">Sampah</a>
                            @endif
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nama</th>
                                    <th class="px-5 py-3">Induk</th>
                                    <th class="px-5 py-3">Sub</th>
                                    <th class="px-5 py-3">Buku</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($categories as $category)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                                            <div class="text-xs text-gray-500">{{ $category->slug }}</div>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700">{{ $category->parent?->name ?? '-' }}</td>
                                        <td class="px-5 py-3 text-gray-700">{{ $category->children_count }}</td>
                                        <td class="px-5 py-3 text-gray-700">{{ $category->books_count }}</td>
                                        <td class="px-5 py-3 text-right">
                                            @if ($category->trashed())
                                                @if (auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('catalog.categories.restore', $category) }}">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:underline">Pulihkan</button>
                                                    </form>
                                                @endif
                                            @else
                                                <form method="POST" action="{{ route('catalog.categories.destroy', $category) }}" onsubmit="return confirm('Soft delete kategori ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada kategori.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
