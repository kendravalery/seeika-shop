<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    //   Display a listing of the resource
    public function index(Request $request)
    {
        $q = $request->input('q', '');
        $type = $request->input('type');
        $status = $request->input('status');

        $collections = Collection::search($q)
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->query(function ($query) {
                $query->withCount('products');
            })
            ->paginate(5)
            ->withQueryString();

        return view('admin.collections.index', compact('collections'));
    }

    //  Show the form for creating a new resource.
    public function create()
    {
        $collections = Collection::all();
        return view('admin.collections.create', compact('collections'));
    }

    //   Store a newly created resource in storag
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:collections,slug'],
            'type' => ['required', 'in:original,collaboration,seasonal,event'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,inactive'],

            'banner' => ['nullable', 'image', 'max:10240'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);
        $storedFiles = [];

        try {
            if ($request->hasFile('banner')) {
                $validated['banner'] = $request->file('banner')->store('collections/banners', 'public');
                $storedFiles[] = $validated['banner'];
            }

            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->file('thumbnail')->store('collections/thumbnails', 'public');
                $storedFiles[] = $validated['thumbnail'];
            }

            Collection::create($validated);
            return redirect()->route('admin.collections.index')->with('success', 'Collection created successfully.');
        } catch (\Throwable $e) {
            if (!empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }
            throw $e;
        }
    }

    //  Display the specified resource
    public function show(string $id)
    {
        //
    }

    //  Show the form for editing the specified resource
    public function edit(string $id)
    {
        $collection = Collection::findOrFail($id);

        return view('admin.collections.edit', compact('collection'));
    }

    //   Update the specified resource in storage
    public function update(Request $request, string $id)
    {
        $collection = Collection::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('collections', 'slug')->ignore($collection)],
            'type' => ['required', 'in:original,collaboration,seasonal,event'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,inactive'],

            'banner' => ['nullable', 'image', 'max:10240'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],

            'remove_banner' => ['nullable', 'boolean'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ]);

        $storedFiles = [];
        $filesToDelete = [];

        unset($validated['banner'], $validated['thumbnail'], $validated['remove_banner'], $validated['remove_thumbnail']);

        try {
            // banner
            if ($request->hasFile('banner')) {
                $newBanner = $request->file('banner')->store('collections/banners', 'public');
                $validated['banner'] = $newBanner;
                $storedFiles[] = $newBanner;

                if ($collection->banner) {
                    $filesToDelete[] = $collection->banner;
                }
            } elseif ($request->boolean('remove_banner')) {
                if ($collection->banner) {
                    $filesToDelete[] = $collection->banner;
                }
                $validated['banner'] = null;
            }

            if ($request->hasFile('thumbnail')) {
                $newThumbnail = $request->file('thumbnail')->store('collections/thumbnails', 'public');
                $validated['thumbnail'] = $newThumbnail;
                $storedFiles[] = $newThumbnail;

                if ($collection->thumbnail) {
                    $filesToDelete[] = $collection->thumbnail;
                }
            } elseif ($request->boolean('remove_thumbnail')) {
                if ($collection->thumbnail) {
                    $filesToDelete[] = $collection->thumbnail;
                }
                $validated['thumbnail'] = null;
            }

            $collection->update($validated);

            //   Database update succeeded.
            //   Now remove old files.
            if (!empty($filesToDelete)) {
                Storage::disk('public')->delete($filesToDelete);
            }

            return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
        } catch (\Throwable $e) {
            if (!empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }
            throw $e;
        }
    }

    public function destroy(string $id)
    {
        $collection = Collection::findOrFail($id);

        $filesToDelete = array_filter([$collection->banner, $collection->thumbnail]);

        $collection->delete();

        if (!empty($filesToDelete)) {
            Storage::disk('public')->delete($filesToDelete);
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }
}
