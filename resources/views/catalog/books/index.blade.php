<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Katalog Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Catalog Context &mdash; pengelolaan buku, kategori, penulis, dan penerbit.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('catalog.categories.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kategori</a>
                <a href="{{ route('catalog.authors.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Penulis</a>
                <a href="{{ route('catalog.publishers.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Penerbit</a>
                <a href="{{ route('catalog.books.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Tambah Buku</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash-message />

            @if (request()->boolean('trashed'))
                <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Menampilkan data buku yang sudah di-soft delete.
                    <a href="{{ route('catalog.books.index') }}" class="font-semibold underline">Kembali ke data aktif</a>
                </div>
            @endif

            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('catalog.books.index') }}" class="grid gap-3 md:grid-cols-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau ISBN"
                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                    <select name="category" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                            @foreach ($category->children as $child)
                                <option value="{{ $child->id }}" @selected((int) request('category') === $child->id)>&mdash; {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="available" value="1" @checked(request()->boolean('available')) class="rounded border-gray-300 text-indigo-600">
                        Hanya tersedia
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-900">Filter</button>
                        <a href="{{ route('catalog.books.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Reset</a>
                    </div>
                </form>

                @if (auth()->user()->role === 'admin')
                    <div class="mt-3 text-sm">
                        @if (request()->boolean('trashed'))
                            <a href="{{ route('catalog.books.index') }}" class="text-indigo-600 hover:underline">Data aktif</a>
                        @else
                            <a href="{{ route('catalog.books.index', ['trashed' => 1]) }}" class="text-amber-600 hover:underline">Sampah ({{ $trashedCount }})</a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Buku</th>
                                <th class="px-4 py-3">Penulis / Penerbit</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Stok</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($books as $book)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('catalog.books.show', $book) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $book->title }}</a>
                                        <div class="text-xs text-gray-500">ISBN {{ $book->isbn }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $book->author?->name ?? '-' }}
                                        <div class="text-xs text-gray-500">{{ $book->publisher?->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $book->categories->pluck('name')->join(', ') ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $book->stock <= $book->low_stock_threshold ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $book->stock }} tersedia
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($book->trashed())
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700">Soft deleted</span>
                                        @elseif ($book->is_active)
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">Aktif</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('catalog.books.show', $book) }}" class="text-indigo-600 hover:underline">Lihat</a>
                                            @if ($book->trashed())
                                                @if (auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('catalog.books.restore', $book) }}">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:underline">Pulihkan</button>
                                                    </form>
                                                @endif
                                            @else
                                                <a href="{{ route('catalog.books.edit', $book) }}" class="text-gray-600 hover:underline">Edit</a>
                                                <form method="POST" action="{{ route('catalog.books.destroy', $book) }}" onsubmit="return confirm('Soft delete buku ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada buku yang cocok dengan filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $books->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
