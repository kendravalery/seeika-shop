<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    //   Display a listing of the resource
    public function index()
    {
        $categories = Category::with([
            'children' => function ($query) {
                $query->withCount('products');
            },
        ])
            ->withCount('products')
            ->whereNull('parent_id')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    //  Show the form for creating a new resource
    //  Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'parent_id' => ['required', 'exists:categories,id'],
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'is_system' => false,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parents'));
    }

    //   Show the form for editing the specified resource

    public function edit(Category $category)
    {
        if ($category->is_system) {
            return back()->with('error', 'System categories cannot be edited.');
        }

        $parents = Category::whereNull('parent_id')->where('is_system', true)->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    //   Update the specified resource in storage

    public function update(Request $request, Category $category)
    {
        if ($category->is_system) {
            return back()->with('error', 'System categories cannot');
        }
        $request->validate([
            'name' => ['required', 'max:255'],
            'parent_id' => 'required',
            Rule::exists('categories', 'id')->where('parent_id', null)->where('is_system', true),
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    //  Remove the specified resource from storage

    public function destroy(Category $category)
    {
        if ($category->is_system) {
            return back()->with('error', 'System categories cannot be deleted.');
        }
        if ($category->children()->exists()) {
            return back()->with('error', 'This category still has sub-categories.');
        }
        if ($category->products()->exists()) {
            return back()->with('error', 'This category is still used by products.');
        }
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
