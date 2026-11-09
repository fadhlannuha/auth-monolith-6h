<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Publisher;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $query = Publisher::query()->withCount('books');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $publishers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('catalog.publishers.index', compact('publishers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:publishers,name'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));

        Publisher::create($data);

        return redirect()
            ->route('catalog.publishers.index')
            ->with('success', 'Penerbit berhasil ditambahkan.');
    }

    public function destroy(Publisher $publisher)
    {
        if ($publisher->books()->withTrashed()->exists()) {
            return back()->with('error', 'Penerbit masih memiliki buku sehingga tidak dapat dihapus.');
        }

        $publisher->delete();

        return redirect()
            ->route('catalog.publishers.index')
            ->with('success', 'Penerbit berhasil dihapus.');
    }
}
