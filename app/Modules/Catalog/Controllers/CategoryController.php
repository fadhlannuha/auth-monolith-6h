<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->with(['parent', 'children'])
            ->withCount(['books', 'children']);

        if ($request->boolean('trashed')) {
            abort_unless($request->user()?->role === 'admin', 403);
            $query->onlyTrashed();
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->get();
        $parents = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('catalog.categories.index', compact('categories', 'parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        Category::create($data);

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori dipindahkan ke sampah (soft delete).');
    }

    public function restore(Category $category)
    {
        $category->restore();

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori berhasil dipulihkan.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
