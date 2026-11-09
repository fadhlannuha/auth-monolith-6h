<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Publisher;
use Modules\Catalog\Requests\StoreBookRequest;
use Modules\Catalog\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()
            ->with(['author', 'publisher', 'categories'])
            ->withCount('categories');

        if ($request->boolean('trashed')) {
            abort_unless($request->user()?->role === 'admin', 403);
            $query->onlyTrashed();
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($categoryId = $request->integer('category')) {
            $query->inCategory($categoryId);
        }

        if ($request->boolean('available')) {
            $query->available();
        }

        $books = $query->orderBy('title')->paginate(10)->withQueryString();

        $categories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $trashedCount = Book::onlyTrashed()->count();

        return view('catalog.books.index', compact('books', 'categories', 'trashedCount'));
    }

    public function create()
    {
        return view('catalog.books.create', [
            'authors' => Author::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'categories' => Category::with('children')->whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($data, $categoryIds) {
            $book = Book::create($data);

            if ($categoryIds !== []) {
                $book->categories()->attach($categoryIds, ['added_at' => now()]);
            }
        });

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Book $book)
    {
        $book->load(['author', 'publisher', 'categories']);

        return view('catalog.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        return view('catalog.books.edit', [
            'book' => $book->load('categories'),
            'authors' => Author::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'categories' => Category::with('children')->whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($book, $data, $categoryIds) {
            $book->update($data);
            $book->categories()->sync($categoryIds);
        });

        return redirect()
            ->route('catalog.books.show', $book)
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku dipindahkan ke sampah (soft delete).');
    }

    public function restore(Book $book)
    {
        $book->restore();

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku berhasil dipulihkan.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $counter = 2;

        while (Book::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
