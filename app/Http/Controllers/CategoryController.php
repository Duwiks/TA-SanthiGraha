<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('category_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100|unique:categories',
            'description' => 'nullable|string',
        ], [
            'category_name.required' => 'Nama Kategori wajib diisi.',
            'category_name.unique' => 'Nama Kategori sudah ada.',
        ]);

        Category::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name,' . $id,
            'description' => 'nullable|string',
        ], [
            'category_name.required' => 'Nama Kategori wajib diisi.',
            'category_name.unique' => 'Nama Kategori sudah ada.',
        ]);

        $category->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diupdate!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Optional logic: Check if category is in use by transactions before deleting
        // if ($category->transactions()->exists()) {
        //     return redirect()->route('categories.index')->with('error', 'Kategori tidak dapat dihapus karena sedang digunakan.');
        // }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
