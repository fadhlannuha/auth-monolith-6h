<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Author;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::query()->withCount('books');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $authors = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('catalog.authors.index', compact('authors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:authors,name'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $slug = Str::slug($data['name']);
        $data['slug'] = $slug.'-'.Str::lower(Str::random(4));

        Author::create($data);

        return redirect()
            ->route('catalog.authors.index')
            ->with('success', 'Penulis berhasil ditambahkan.');
    }

    public function destroy(Author $author)
    {
        if ($author->books()->withTrashed()->exists()) {
            return back()->with('error', 'Penulis masih memiliki buku sehingga tidak dapat dihapus.');
        }

        $author->delete();

        return redirect()
            ->route('catalog.authors.index')
            ->with('success', 'Penulis berhasil dihapus.');
    }
}
