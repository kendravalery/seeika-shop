<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::latest()->get();

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();

        return view('admin.banners.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image'],
            'product_id' => ['nullable', 'exists:products,id'],
            'link' => ['nullable', 'url'],
            'is_active' => ['required', 'boolean'],
        ]);

        $hasProduct = filled($validated['product_id'] ?? null);
        $hasLink = filled($validated['link'] ?? null);

        if ($hasProduct === $hasLink) {
            throw ValidationException::withMessages([
                'product_id' => 'Choose either a product or a link, but not both.',
                'link' => 'Choose either a product or a link, but not both.',
            ]);
        }

        $path = $request->file('image')->store('banners', 'public');

        if (!$path) {
            return back()
                ->withErrors(['image' => 'The banner image could not be saved.'])
                ->withInput();
        }

        $banner = new Banner();
        $banner->image = $path;
        $banner->product_id = $validated['product_id'] ?? null;
        $banner->link = $validated['link'] ?? null;
        $banner->is_active = $validated['is_active'];
        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $banner = Banner::findOrFail($id);
        $products = Product::all();

        return view('admin.banners.edit', compact('banner', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'image' => ['nullable', 'image'],
            'product_id' => ['nullable', 'exists:products,id'],
            'link' => ['nullable', 'url'],
            'is_active' => ['required', 'boolean'],
        ]);

        $hasProduct = filled($validated['product_id'] ?? null);
        $hasLink = filled($validated['link'] ?? null);

        if ($hasProduct === $hasLink) {
            throw ValidationException::withMessages([
                'product_id' => 'Choose either a product or a link, but not both.',
                'link' => 'Choose either a product or a link, but not both.',
            ]);
        }

        $newPath = null;

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('banners', 'public');

            if (!$newPath) {
                return back()
                    ->withErrors(['image' => 'The new banner image could not be saved.'])
                    ->withInput();
            }
        }

        $oldPath = $banner->image;

        $banner->product_id = $validated['product_id'] ?? null;
        $banner->link = $validated['link'] ?? null;
        $banner->is_active = $validated['is_active'];

        if ($newPath) {
            $banner->image = $newPath;
        }

        try {
            $banner->save();
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);
        $imagePath = $banner->image;

        $banner->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    /**
     * Toggle the banner's active status.
     */

    public function toggle(string $id)
    {
        $banner = Banner::findOrFail($id);

        $banner->is_active = !$banner->is_active;
        $banner->save();

        return back()->with('success', 'Banner status updated successfully.');
    }
}
