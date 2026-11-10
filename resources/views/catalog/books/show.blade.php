<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $book->title }}</h2>
                <p class="mt-1 text-sm text-gray-500">ISBN {{ $book->isbn }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('catalog.books.edit', $book) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Edit</a>
                <a href="{{ route('catalog.books.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Buku</h3>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Penulis</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $book->author?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Penerbit</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $book->publisher?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Harga</dt>
                            <dd class="mt-1 text-sm text-gray-800">Rp {{ number_format((float) $book->price, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tahun Terbit</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $book->published_year ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Stok</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $book->stock }} (batas rendah {{ $book->low_stock_threshold }})</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $book->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-900">Deskripsi</h4>
                        <p class="mt-2 text-sm leading-6 text-gray-700">{{ $book->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Kategori</h3>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        @forelse ($book->categories as $category)
                            <li class="rounded-md bg-gray-50 px-3 py-2">
                                {{ $category->name }}
                                <span class="block text-xs text-gray-500">Ditambahkan {{ $category->pivot->added_at }}</span>
                            </li>
                        @empty
                            <li class="text-gray-500">Belum ada kategori.</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('catalog.books.destroy', $book) }}" class="mt-6" onsubmit="return confirm('Soft delete buku ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Soft delete buku</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
