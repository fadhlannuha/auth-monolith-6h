@php
    $selectedCategories = old('category_ids', isset($book) ? $book->categories->pluck('id')->all() : []);
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="title" value="Judul Buku" />
        <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $book->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="isbn" value="ISBN (ISBN-10 / ISBN-13)" />
        <x-text-input id="isbn" name="isbn" class="mt-1 block w-full" :value="old('isbn', $book->isbn ?? '')" required />
        <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="author_id" value="Penulis" />
        <select id="author_id" name="author_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">-- Pilih penulis --</option>
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected((int) old('author_id', $book->author_id ?? 0) === $author->id)>{{ $author->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('author_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="publisher_id" value="Penerbit" />
        <select id="publisher_id" name="publisher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">-- Pilih penerbit --</option>
            @foreach ($publishers as $publisher)
                <option value="{{ $publisher->id }}" @selected((int) old('publisher_id', $book->publisher_id ?? 0) === $publisher->id)>{{ $publisher->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('publisher_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="price" value="Harga" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $book->price ?? 0)" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="published_year" value="Tahun Terbit" />
        <x-text-input id="published_year" name="published_year" type="number" min="1000" max="{{ date('Y') }}" class="mt-1 block w-full" :value="old('published_year', $book->published_year ?? '')" />
        <x-input-error :messages="$errors->get('published_year')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="stock" value="Stok" />
        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $book->stock ?? 0)" required />
        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="low_stock_threshold" value="Batas Stok Rendah" />
        <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" class="mt-1 block w-full" :value="old('low_stock_threshold', $book->low_stock_threshold ?? 3)" required />
        <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $book->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label value="Kategori" />
        <div class="mt-2 grid gap-2 rounded-md border border-gray-200 p-4 sm:grid-cols-2">
            @forelse ($categories as $category)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories)) class="rounded border-gray-300 text-indigo-600">
                    {{ $category->name }}
                </label>
                @foreach ($category->children as $child)
                    <label class="ms-5 flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="category_ids[]" value="{{ $child->id }}" @checked(in_array($child->id, $selectedCategories)) class="rounded border-gray-300 text-indigo-600">
                        {{ $child->name }}
                    </label>
                @endforeach
            @empty
                <p class="text-sm text-gray-500">Belum ada kategori. Tambahkan kategori terlebih dahulu.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('category_ids')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $book->is_active ?? true)) class="rounded border-gray-300 text-indigo-600">
            Buku aktif dan dapat dipinjam
        </label>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
    <a href="{{ route('catalog.books.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>
