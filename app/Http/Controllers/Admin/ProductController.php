<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    //  show all product
    public function index(Request $request)
    {
        // Determine the sorting column and direction.
        // Newest is the default when no sort is selected.
        [$sortColumn, $sortDirection] = match ($request->input('sort', '')) {
            '' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'price_asc' => ['min_price', 'asc'],
            'price_desc' => ['min_price', 'desc'],
            'stock_asc' => ['total_stock', 'asc'],
            'stock_desc' => ['total_stock', 'desc'],
            'sold_desc' => ['sold_count', 'desc'],
            default => ['created_at', 'desc'],
        };

        $products = Product::search($request->input('q', ''))
            ->when($request->filled('status'), function ($search) use ($request) {
                $search->where('status', $request->status);
            })
            ->when($request->filled('category_id'), function ($search) use ($request) {
                $search->where('category_id', $request->category_id);
            })
            ->when($request->filled('collection_id'), function ($search) use ($request) {
                $search->where('collection_id', $request->collection_id);
            })
            ->when($request->filled('product_type'), function ($search) use ($request) {
                $search->where('product_type', $request->product_type);
            })
            ->when($request->filled('availability'), function ($search) use ($request) {
                $search->where('availability', $request->availability);
            })

            // Always apply sorting, including the default Newest order.
            ->orderBy($sortColumn, $sortDirection)

            ->query(function ($query) {
                $query->with([
                    'images' => function ($query) {
                        $query->orderByDesc('is_primary')->orderBy('position');
                    },
                    'category',
                    'collection',
                    'variants',
                    'options',
                ]);
            })
            ->paginate(12)
            ->withQueryString();

        $categories = Category::whereNotNull('parent_id')->orderBy('name')->get();

        $collections = Collection::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'collections'));
    }

    public function create()
    {
        $categoryChildrens = Category::whereNotNull('parent_id')->get();

        $collections = Collection::all();

        $products = Product::all();
        return view('admin.products.create', compact('collections', 'categoryChildrens'));
    }

    public function store(Request $request)
    {
        // Remove thousand separators from price inputs
        $request->merge([
            'price' => filled($request->price) ? str_replace('.', '', $request->price) : null,

            'compare_price' => filled($request->compare_price) ? str_replace('.', '', $request->compare_price) : null,
            'slug' => Str::slug($request->input('slug', '')),
        ]);
        // Default empty single-product stock to 0
        $request->merge([
            'stock' => filled($request->input('stock')) ? $request->input('stock') : 0,
        ]);

        // Format variant prices and default empty stock to 0
        if ($request->has('variants')) {
            $variants = $request->input('variants', []);

            foreach ($variants as $index => $variant) {
                if (isset($variant['price'])) {
                    $variants[$index]['price'] = str_replace('.', '', $variant['price']);
                }

                $variants[$index]['stock'] = filled($variant['stock'] ?? null) ? $variant['stock'] : 0;
            }

            $request->merge([
                'variants' => $variants,
            ]);
        }

        $validated = $request->validate(
            [
                'product_type' => 'required|in:single,variant',

                'category_id' => 'required|exists:categories,id',
                'collection_id' => 'nullable|exists:collections,id',

                'name' => 'required',
                'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
                'description' => 'nullable|string',

                'compare_price' => 'nullable|numeric|min:0',
                'max_purchase_per_order' => 'nullable|integer|min:1',
                'preorder_limit' => 'nullable|integer|min:1',

                'sku' => ['nullable', 'required_if:product_type,single', 'string', Rule::unique('product_variants', 'sku')],
                'price' => ['nullable', 'required_if:product_type,single', 'numeric', 'min:0'],
                'stock' => ['nullable', 'required_if:product_type,single', 'integer', 'min:0'],
                'weight' => ['nullable', 'required_if:product_type,single', 'numeric', 'min:0'],

                'availability' => 'required|in:ready,preorder',
                'preorder_release_label' => 'nullable|string|max:255',
                'status' => 'required|in:draft,active,inactive',

                'attributes' => ['nullable', 'array'],
                'attributes.scale' => ['nullable', 'string', 'max:255'],
                'attributes.manufacturer' => ['nullable', 'string', 'max:255'],

                // main product image
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpg,webp,png|max:5000',

                // varinat specific
                'options' => 'required_if:product_type,variant|array|min:1|max:2',
                'options.*.name' => 'required_if:product_type,variant|string',
                'options.*.values' => 'required_if:product_type,variant|array|min:1',
                'options.*.values.*' => 'required_if:product_type,variant|string',

                // options image  / only to recover data
                'options.*.use_images' => 'nullable|boolean',
                // image option
                'option_images' => 'nullable|array',
                'option_images.*' => 'nullable|array',
                'option_images.*.*' => 'image|mimes:jpg,png,webp|max:5000',

                // generated Variants
                'variants' => 'required_if:product_type,variant|array|min:1',
                'variants.*.values' => 'required_if:product_type,variant|array|min:1',
                'variants.*.values.*' => 'required_if:product_type,variant|string',
                'variants.*.sku' => ['required_if:product_type,variant', 'string', 'distinct', Rule::unique('product_variants', 'sku')],
                'variants.*.price' => 'required_if:product_type,variant|numeric|min:0',
                'variants.*.stock' => 'required_if:product_type,variant|integer|min:0',
                'variants.*.weight' => 'required_if:product_type,variant|numeric|min:0',
                'variants.*.is_active' => 'required_if:product_type,variant|boolean',
            ],
            [
                'name.required' => 'Product name is required.',
                'category_id.required' => 'Please select a category.',

                'variants.*.sku.required_if' => 'SKU is required.',
                'variants.*.sku.distinct' => 'SKU cannot be duplicated between variants.',
                'variants.*.sku.unique' => 'This SKU is already being used.',

                'variants.*.price.required_if' => 'Variant price is required.',
                'variants.*.price.numeric' => 'Variant price must be a number.',

                'variants.*.stock.required_if' => 'Variant stock is required.',
                'variants.*.stock.integer' => 'Variant stock must be a whole number.',

                'variants.*.weight.required_if' => 'Variant weight is required.',
                'variants.*.weight.numeric' => 'Variant weight must be a number.',
            ],
        );
        $attributes = collect($request->input('attributes', []))
            ->only(['scale', 'manufacturer'])
            ->filter(fn($value) => filled($value))
            ->all();

        if ($request->product_type === 'variant') {
            $hasActiveVariant = collect($validated['variants'])->contains(fn($variant) => (int) $variant['is_active'] === 1);

            if (!$hasActiveVariant) {
                throw ValidationException::withMessages([
                    'variants' => 'At least one variant must be active.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $attributes) {
            $user = $request->user();

            //CREATE PRODUCT
            $product = Product::create([
                'user_id' => $user->id,
                'collection_id' => $request->collection_id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'attributes' => $attributes ?: null,
                'availability' => $request->availability,
                'preorder_release_label' => $request->availability === 'preorder' ? $request->preorder_release_label : null,
                'status' => $request->status,
            ]);

            // PRODUCT IMAGES (main product galery)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'is_primary' => $index === 0,
                        'position' => $index,
                    ]);
                }
            }

            // SINGLE PRODUCT
            if ($request->product_type === 'single') {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $request->sku,
                    'price' => $request->price,
                    'compare_price' => $request->compare_price,
                    'stock' => $request->stock,
                    'max_purchase_per_order' => $request->max_purchase_per_order,
                    'preorder_limit' => $request->availability === 'preorder' ? $request->preorder_limit : null,
                    'weight' => $request->weight,
                    'is_active' => true,
                ]);
            }

            // VARIANT PRODUCT
            if ($request->product_type === 'variant') {
                $optionValueMap = [];

                // create options + values
                foreach ($request->options as $optionIndex => $optionData) {
                    $option = ProductOption::create([
                        'product_id' => $product->id,
                        'option_name' => $optionData['name'],
                        'position' => $optionIndex,
                    ]);

                    foreach ($optionData['values'] as $valueIndex => $value) {
                        $imageId = null;

                        // option image  == (options[0][values][0] = RED | option_images[0][0] = red.jpg) ==
                        if (isset($request->file('option_images')[$optionIndex][$valueIndex])) {
                            $image = $request->file("option_images.$optionIndex.$valueIndex");

                            $path = $image->store('products', 'public');

                            $productImage = ProductImage::create([
                                'product_id' => $product->id,
                                'path' => $path,
                                'is_primary' => false,
                                'position' => null,
                            ]);
                            $imageId = $productImage->id;
                        }
                        $optionValue = ProductOptionValue::create([
                            'product_option_id' => $option->id,
                            'value' => $value,
                            'position' => $valueIndex,
                            'image_id' => $imageId,
                        ]);

                        // save id by name value
                        $optionValueMap[$optionIndex][$value] = $optionValue->id;
                    }
                }

                // CREATE VARIANTS
                foreach ($request->variants as $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'compare_price' => $request->compare_price,
                        'stock' => $variantData['stock'],
                        'max_purchase_per_order' => $request->max_purchase_per_order,
                        'preorder_limit' => $request->availability === 'preorder' ? $request->preorder_limit : null,
                        'weight' => $variantData['weight'],
                        'is_active' => (int) $variantData['is_active'] === 1,
                    ]);

                    // CONNECT VARIANT -> OPTIONS VALUES
                    foreach ($variantData['values'] as $optionIndex => $value) {
                        $optionValueId = $optionValueMap[$optionIndex][$value] ?? null;

                        if (!$optionValueId) {
                            continue;
                        }

                        $variant->optionValues()->attach($optionValueId);
                    }
                }
            }
        });
        return redirect()->route('admin.products.index')->with('success', 'Product successfully added');
    }

    public function edit($id)
    {
        $product = Product::with('images', 'category', 'collection', 'options.values.image', 'variants.optionValues.image')->findOrFail($id);

        $categoryChildrens = Category::whereNotNull('parent_id')->get();

        $collections = Collection::all();

        return view('admin.products.edit', compact('product', 'categoryChildrens', 'collections'));
    }

    public function update(Request $request, Product $product)
    {
        // convert formated indonesian currency values into plain numbers
        // Convert formatted Indonesian currency values into plain numbers
        $normalizeMoney = function ($value) {
            if ($value === null || $value === '') {
                return null;
            }

            return preg_replace('/\D/', '', (string) $value);
        };

        $request->merge([
            'compare_price' => $normalizeMoney($request->input('compare_price')),
            'price' => $normalizeMoney($request->input('price')),
            'sku' => trim((string) $request->input('sku')),
            'variants' => collect($request->input('variants', []))
                ->map(function ($variant) use ($normalizeMoney) {
                    $variant['sku'] = trim((string) ($variant['sku'] ?? ''));
                    $variant['price'] = $normalizeMoney($variant['price'] ?? null);
                    $variant['compare_price'] = $normalizeMoney($variant['compare_price'] ?? null);
                    $variant['stock'] = filled($variant['stock'] ?? null) ? $variant['stock'] : 0;

                    return $variant;
                })
                ->values()
                ->all(),
            'stock' => filled($request->input('stock')) ? $request->input('stock') : 0,
        ]);
        $isVariantProduct = $product->options()->exists();

        // validations
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'description' => 'nullable|string',

            // Optional product specifications
            'attributes' => 'nullable|array',
            'attributes.scale' => 'nullable|string|max:255',
            'attributes.manufacturer' => 'nullable|string|max:255',

            'collection_id' => 'nullable|exists:collections,id',
            'category_id' => 'required|exists:categories,id',
            'compare_price' => 'nullable|numeric|min:0',
            'availability' => 'required|in:ready,preorder',
            'preorder_release_label' => 'nullable|string|max:255',
            'status' => 'required|in:draft,active,inactive',

            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,webp,png|max:5000',

            'removed_images' => 'nullable|array',
            'removed_images.*' => 'integer',
            'primary_image_id' => 'nullable|integer',
            'primary_new_index' => 'nullable|integer|min:0',
        ];

        if ($isVariantProduct) {
            $rules = array_merge($rules, [
                'options' => 'required|array|min:1|max:2',
                'options.*.id' => 'nullable|integer',
                'options.*.name' => 'required|string',
                'options.*.use_images' => 'nullable|boolean',

                'options.*.values' => 'required|array|min:1',
                'options.*.values.*.id' => 'nullable|integer',
                'options.*.values.*.key' => 'required|string',
                'options.*.values.*.image_id' => 'nullable|integer',
                'options.*.values.*.value' => 'required|string',

                'option_images' => 'nullable|array',
                'option_images.*' => 'nullable|array',
                'option_images.*.*' => 'image|mimes:jpg,png,webp|max:5000',

                'variants' => 'required|array|min:1',
                'variants.*.id' => 'nullable|integer',
                'variants.*.option_value_keys' => 'required|array|min:1',
                'variants.*.option_value_keys.*' => 'required|string',
                'variants.*.sku' => 'required|string',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.compare_price' => 'nullable|numeric|min:0',
                'variants.*.stock' => 'required|integer|min:0',
                'variants.*.weight' => 'required|numeric|min:0',
                'variants.*.max_purchase_per_order' => 'nullable|integer|min:1',
                'variants.*.preorder_limit' => 'nullable|integer|min:1',
                'variants.*.is_active' => 'required|boolean',

                'removed_option_ids' => 'nullable|array',
                'removed_option_ids.*' => 'integer',

                'removed_value_ids' => 'nullable|array',
                'removed_value_ids.*' => 'integer',

                'removed_variant_ids' => 'nullable|array',
                'removed_variant_ids.*' => 'integer',
            ]);
        } else {
            $rules = array_merge($rules, [
                'sku' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'weight' => 'required|numeric|min:0',
                'max_purchase_per_order' => 'nullable|integer|min:1',
                'preorder_limit' => 'nullable|integer|min:1',
            ]);
        }

        $validated = $request->validate($rules);

        if ($isVariantProduct) {
            $hasActiveVariant = collect($validated['variants'])->contains(fn($variant) => (bool) $variant['is_active']);

            if (!$hasActiveVariant) {
                throw ValidationException::withMessages([
                    'variants' => 'At least one variant must be active.',
                ]);
            }
        }

        // dd([
        //     'primary_image_id' => $request->input('primary_image_id'),
        //     'primary_new_index' => $request->input('primary_new_index'),
        // ]);

        // variables used for image cleanup after the transaction.
        $newUploadedPaths = [];
        $removedMainImageIds = [];
        $oldOptionImageIds = [];

        try {
            DB::transaction(function () use ($request, $product, $validated, $isVariantProduct, &$newUploadedPaths, &$removedMainImageIds, &$oldOptionImageIds) {
                // update product
                $product->update([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'attributes' => array_filter($validated['attributes'] ?? [], fn($value) => filled($value)) ?: null,
                    'category_id' => $validated['category_id'],
                    'collection_id' => $validated['collection_id'] ?? null,
                    'availability' => $validated['availability'],
                    // Save the release label only for pre-orders
                    'preorder_release_label' => $validated['availability'] === 'preorder' ? $validated['preorder_release_label'] ?? null : null,
                    'status' => $validated['status'],
                ]);

                // main product images
                $removedImageIds = collect($validated['removed_images'] ?? [])
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($removedImageIds->isNotEmpty()) {
                    $removedImages = ProductImage::where('product_id', $product->id)->whereIn('id', $removedImageIds)->get();
                    $removedMainImageIds = $removedImages->pluck('id')->map(fn($id) => (int) $id)->values()->all();

                    // do not rmeove an image here yet
                    // some images may stil be sued bt option values
                }
                // variant product
                if ($isVariantProduct) {
                    $currentOptions = $product->options()->with('values')->get()->keyBy('id');
                    $currentVariants = $product->variants()->get()->keyBy('id');

                    $submittedOptions = collect($validated['options']);

                    $submittedOptionIds = $submittedOptions->pluck('id')->filter()->map(fn($id) => (int) $id)->values();
                    $removedOptionIds = collect($validated['removed_option_ids'] ?? [])
                        ->map(fn($id) => (int) $id)
                        ->unique()
                        ->values();

                    // maksure every existing option is either submitted, or explicitly removed.
                    $knownOptionIds = $submittedOptionIds->merge($removedOptionIds)->unique()->values();

                    $missingOptionIds = $currentOptions->keys()->diff($knownOptionIds);

                    $unknownOptionIds = $knownOptionIds->diff($currentOptions->keys());

                    if ($missingOptionIds->isNotEmpty() || $unknownOptionIds->isNotEmpty()) {
                        throw ValidationException::withMessages([
                            'options' => 'Invalid product options were submitted.',
                        ]);
                    }

                    // only one option can use image
                    $imageOptionCount = $submittedOptions->filter(fn($option) => !empty($option['use_images']))->count();

                    if ($imageOptionCount > 1) {
                        throw ValidationException::withMessages([
                            'options' => 'Only one option can use images.',
                        ]);
                    }
                    // Prepare and validate option values that will be removed.
                    $requestedRemovedValueIds = collect($validated['removed_value_ids'] ?? [])
                        ->map(fn($id) => (int) $id)
                        ->unique()
                        ->values();

                    $currentValueIds = $currentOptions->flatMap(fn($option) => $option->values)->pluck('id')->map(fn($id) => (int) $id)->unique()->values();

                    $submittedValueIds = $submittedOptions->flatMap(fn($option) => collect($option['values'] ?? []))->pluck('id')->filter()->map(fn($id) => (int) $id)->unique()->values();

                    if ($requestedRemovedValueIds->diff($currentValueIds)->isNotEmpty()) {
                        throw ValidationException::withMessages([
                            'options' => 'Invalid option values were submitted for removal.',
                        ]);
                    }

                    if ($requestedRemovedValueIds->intersect($submittedValueIds)->isNotEmpty()) {
                        throw ValidationException::withMessages([
                            'options' => 'An option value cannot be active and removed at the same time.',
                        ]);
                    }

                    // Removing an option also removes all of its values.
                    $valuesFromRemovedOptions = $currentOptions->only($removedOptionIds->all())->flatMap(fn($option) => $option->values)->pluck('id')->map(fn($id) => (int) $id);

                    $removedValueIds = $requestedRemovedValueIds->merge($valuesFromRemovedOptions)->unique()->values();
                    //  makesure existing values submitted under an option, actually belong to that option
                    foreach ($submittedOptions as $optionData) {
                        // dd($validated['options']);  //
                        $optionId = !empty($optionData['id']) ? (int) $optionData['id'] : null;

                        $existingOption = $optionId ? $currentOptions->get($optionId) : null;

                        $existingValueIds = $existingOption ? $existingOption->values->pluck('id')->map(fn($id) => (int) $id) : collect();

                        foreach ($optionData['values'] as $valueData) {
                            if (!empty($valueData['id'])) {
                                $valueId = (int) $valueData['id'];

                                if (!$existingOption || !$existingValueIds->contains($valueId)) {
                                    throw ValidationException::withMessages([
                                        'options' => 'An option value does not belong to the selected option.',
                                    ]);
                                }
                            }
                        }
                    }

                    // update / create options values
                    $valueTokenMap = [];
                    $valueOptionMap = [];
                    $activeOptionIds = [];

                    foreach ($submittedOptions as $optionIndex => $optionData) {
                        $optionId = !empty($optionData['id']) ? (int) $optionData['id'] : null;

                        if ($optionId) {
                            $option = $currentOptions->get($optionId);

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'options' => 'Invalid option Id',
                                ]);
                            }
                            $option->update([
                                'option_name' => trim($optionData['name']),
                                'position' => $optionIndex,
                            ]);
                        } else {
                            $option = ProductOption::create([
                                'product_id' => $product->id,
                                'option_name' => trim($optionData['name']),
                                'position' => $optionIndex,
                            ]);
                        }
                        $activeOptionIds[] = $option->id;

                        $useImages = !empty($optionData['use_images']);

                        foreach ($optionData['values'] as $valueIndex => $valueData) {
                            $valueId = !empty($valueData['id']) ? (int) $valueData['id'] : null;

                            if ($valueId) {
                                $value = $option->values()->whereKey($valueId)->first();

                                if (!$value) {
                                    throw ValidationException::withMessages([
                                        'options' => 'Invalid option value ID.',
                                    ]);
                                }
                            } else {
                                $value = new ProductOptionValue();
                                $value->product_option_id = $option->id;
                            }

                            $oldImageId = $value->image_id;

                            $value->value = trim($valueData['value']);
                            $value->position = $valueIndex;

                            $uploadedImage = data_get($request->file('option_images'), "{$optionIndex}.{$valueIndex}");

                            if (!$useImages) {
                                if ($oldImageId) {
                                    $oldOptionImageIds[] = (int) $oldImageId;
                                }
                                $value->image_id = null;
                            } elseif ($uploadedImage) {
                                $path = $uploadedImage->store('products', 'public');
                                $newUploadedPaths[] = $path;

                                $optionImage = ProductImage::create([
                                    'product_id' => $product->id,
                                    'path' => $path,
                                    'is_primary' => false,
                                    'position' => ProductImage::where('product_id', $product->id)->max('position') + 1,
                                ]);
                                if ($oldImageId) {
                                    $oldOptionImageIds[] = (int) $oldImageId;
                                }
                                $value->image_id = $optionImage->id;
                            }
                            $value->save();

                            // map both token formats

                            // existing values use:
                            // id 123

                            // new values use:
                            // key:random-uuid
                            $valueTokenMap['id:' . $value->id] = $value->id;

                            if (!empty($valueData['key'])) {
                                $valueTokenMap['key:' . $valueData['key']] = $value->id;
                            }

                            $valueOptionMap[$value->id] = $option->id;
                        }
                    }

                    // delete removed variants first
                    $removedVariantIds = collect($validated['removed_variant_ids'] ?? [])
                        ->map(fn($id) => (int) $id)
                        ->values();

                    $submittedVariantIds = collect($validated['variants'])->pluck('id')->filter()->map(fn($id) => (int) $id)->values();
                    $knownVariantIds = $submittedVariantIds->merge($removedVariantIds)->unique()->values();

                    $missingVariantIds = $currentVariants->keys()->diff($knownVariantIds);

                    $unknownVariantIds = $knownVariantIds->diff($currentVariants->keys());

                    if ($missingVariantIds->isNotEmpty() || $unknownVariantIds->isNotEmpty()) {
                        throw ValidationException::withMessages([
                            'variants' => 'Invalid product variants were submitted.',
                        ]);
                    }

                    if ($submittedVariantIds->intersect($removedVariantIds)->isNotEmpty()) {
                        throw ValidationException::withMessages([
                            'variants' => 'A variant cannot be updated and removed at the same time.',
                        ]);
                    }
                    foreach ($removedVariantIds as $variantId) {
                        DB::table('product_variant_option_values')->where('product_variant_id', $variantId)->delete();

                        $variant = $currentVariants->get($variantId);
                        if ($variant) {
                            $variant->delete();
                        }
                    }

                    // validate sku uniqueness
                    $submittedSkus = collect($validated['variants'])->pluck('sku')->map(fn($sku) => mb_strtolower(trim($sku)))->values();

                    if ($submittedSkus->count() !== $submittedSkus->unique()->count()) {
                        throw ValidationException::withMessages([
                            'variants' => 'Variant SKU values must be unique',
                        ]);
                    }

                    $variantIdsBeingUpdated = $submittedVariantIds->all();

                    $skuConflict = ProductVariant::withTrashed()
                        ->whereIn('sku', collect($validated['variants'])->pluck('sku')->all())
                        ->when(!empty($variantIdsBeingUpdated), fn($query) => $query->whereNotIn('id', $variantIdsBeingUpdated))
                        ->exists();

                    if ($skuConflict) {
                        throw ValidationException::withMessages([
                            'variants' => 'One or more variant SKU values are already in use',
                        ]);
                    }

                    // update / create variants
                    foreach ($validated['variants'] as $variantData) {
                        $variantId = !empty($variantData['id']) ? (int) $variantData['id'] : null;

                        $resolvedValueIds = [];
                        $resolvedOptionIds = [];

                        foreach ($variantData['option_value_keys'] as $token) {
                            if (!isset($valueTokenMap[$token])) {
                                throw ValidationException::withMessages([
                                    'variants' => ' variant references an invalid option value.',
                                ]);
                            }

                            $valueId = $valueTokenMap[$token];
                            $resolvedValueIds[] = $valueId;
                            $resolvedOptionIds[] = $valueOptionMap[$valueId];
                        }

                        $resolvedValueIds = array_values(array_unique($resolvedValueIds));
                        $resolvedOptionIds = array_values(array_unique($resolvedOptionIds));

                        $activeOptionIds = array_map('intval', $activeOptionIds);
                        $resolvedOptionIds = array_map('intval', $resolvedOptionIds);

                        $missingOptionIds = array_diff($activeOptionIds, $resolvedOptionIds);
                        $extraOptionIds = array_diff($resolvedOptionIds, $activeOptionIds);

                        if (count($resolvedValueIds) !== count($activeOptionIds) || !empty($missingOptionIds) || !empty($extraOptionIds)) {
                            throw ValidationException::withMessages([
                                'variants' => 'Every variant must contain exactly one value from each product option.',
                            ]);
                        }

                        if ($variantId) {
                            $variant = $currentVariants->get($variantId);

                            if (!$variant) {
                                throw ValidationException::withMessages([
                                    'variants' => 'Invalid variant ID',
                                ]);
                            }

                            $variant->update([
                                'sku' => trim($variantData['sku']),
                                'price' => $variantData['price'],
                                'compare_price' => $variantData['compare_price'] ?? null,
                                'stock' => $variantData['stock'],
                                'weight' => $variantData['weight'],
                                'max_purchase_per_order' => $variantData['max_purchase_per_order'] ?? null,
                                'preorder_limit' => $validated['availability'] === 'preorder' ? $variantData['preorder_limit'] ?? null : null,
                                'is_active' => (bool) $variantData['is_active'],
                            ]);
                        } else {
                            $variant = ProductVariant::create([
                                'product_id' => $product->id,
                                'sku' => trim($variantData['sku']),
                                'price' => $variantData['price'],
                                'compare_price' => $variantData['compare_price'] ?? null,
                                'stock' => $variantData['stock'],
                                'weight' => $variantData['weight'],
                                'max_purchase_per_order' => $variantData['max_purchase_per_order'] ?? null,
                                'preorder_limit' => $validated['availability'] === 'preorder' ? $variantData['preorder_limit'] ?? null : null,
                                'is_active' => (bool) $variantData['is_active'],
                            ]);
                        }

                        $variant->optionValues()->sync($resolvedValueIds);
                    }

                    // remove option values
                    if ($removedValueIds->isNotEmpty()) {
                        $removedValues = ProductOptionValue::whereIn('id', $removedValueIds)->get();

                        foreach ($removedValues as $removedValue) {
                            if ($removedValue->image_id) {
                                $oldOptionImageIds[] = (int) $removedValue->image_id;
                            }
                        }

                        DB::table('product_variant_option_values')->whereIn('product_option_value_id', $removedValueIds->all())->delete();

                        ProductOptionValue::whereIn('id', $removedValueIds)->delete();
                    }

                    // remove options
                    if ($removedOptionIds->isNotEmpty()) {
                        DB::table('product_variant_option_values')
                            ->whereIn('product_option_value_id', ProductOptionValue::whereIn('product_option_id', $removedOptionIds)->pluck('id')->all())
                            ->delete();
                        ProductOptionValue::whereIn('product_option_id', $removedOptionIds)->delete();

                        ProductOption::where('product_id', $product->id)->whereIn('id', $removedOptionIds)->delete();
                    }
                } else {
                    // Single product
                    $variant = $product->variants()->first();

                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'sku' => 'The product does not have a variant.',
                        ]);
                    }

                    $sku = trim($validated['sku']);

                    $skuConflict = ProductVariant::withTrashed()->where('sku', $sku)->where('id', '!=', $variant->id)->exists();

                    if ($skuConflict) {
                        throw ValidationException::withMessages([
                            'sku' => 'This SKU is already in use.',
                        ]);
                    }

                    $variant->update([
                        'sku' => $sku,
                        'price' => $validated['price'],
                        'compare_price' => $validated['compare_price'] ?? null,
                        'stock' => $validated['stock'],
                        'weight' => $validated['weight'],
                        'max_purchase_per_order' => $validated['max_purchase_per_order'] ?? null,
                        'preorder_limit' => $validated['availability'] === 'preorder' ? $validated['preorder_limit'] ?? null : null,
                        'is_active' => true,
                    ]);
                }
                // save new main product images
                $newImages = $request->file('images', []);

                $newImageModels = [];

                if (!empty($newImages)) {
                    $nextPosition = ((int) ProductImage::where('product_id', $product->id)->max('position')) + 1;

                    foreach ($newImages as $file) {
                        $path = $file->store('products', 'public');

                        $newUploadedPaths[] = $path;

                        $newImageModels[] = ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'is_primary' => false,
                            'position' => $nextPosition++,
                        ]);
                    }
                }
                // update primary product image
                ProductImage::where('product_id', $product->id)->update([
                    'is_primary' => false,
                ]);

                $primaryImageId = $validated['primary_image_id'] ?? null;
                $primaryNewIndex = $validated['primary_new_index'] ?? null;

                if ($primaryImageId) {
                    $primaryImage = ProductImage::where('product_id', $product->id)->whereKey((int) $primaryImageId)->first();

                    if (!$primaryImage) {
                        throw ValidationException::withMessages([
                            'primary_image_id' => 'Invalid primary image.',
                        ]);
                    }

                    $primaryImage->update([
                        'is_primary' => true,
                    ]);
                } elseif ($primaryNewIndex !== null) {
                    $primaryNewIndex = (int) $primaryNewIndex;

                    if (!isset($newImageModels[$primaryNewIndex])) {
                        throw ValidationException::withMessages([
                            'primary_new_index' => 'Invalid primary image selection.',
                        ]);
                    }

                    $newImageModels[$primaryNewIndex]->update([
                        'is_primary' => true,
                    ]);
                }

                // remove selected  main images from the database
                // their physical files are cleaned after the transaction
                // update primary product image
            });
        } catch (\Throwable $e) {
            // Remove newly uploaded files when the database transaction fails
            foreach ($newUploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
        // clean up old image files after the transaction succeeds
        $candidateOptionImageIds = collect($oldOptionImageIds)->filter()->map(fn($id) => (int) $id)->unique()->values();

        $currentOptionImageIds = ProductOptionValue::whereHas(
            'option',
            fn($query) => $query->where(
                'product_id',

                $product->id,
            ),
        )
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->map(fn($id) => (int) $id);

        // Main images removed from the gallery can be deleted
        // when they are no longer used by option values.
        $mainImagesToDelete = collect($removedMainImageIds)->diff($currentOptionImageIds)->unique()->values();

        //  old option images can be deleted when they are no longer
        //  used  by any option value
        $optionImagesToDelete = $candidateOptionImageIds->diff($currentOptionImageIds)->unique()->values();

        $imagesToDelete = $mainImagesToDelete->merge($optionImagesToDelete)->unique()->values();

        if ($imagesToDelete->isNotEmpty()) {
            $images = ProductImage::whereIn('id', $imagesToDelete)->get();

            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->load('images');

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function show($id)
    {
        $product = Product::with('images', 'category', 'collection', 'options.values.image', 'variants.optionValues.image')->findOrFail($id);

        $categoryChildrens = Category::whereNotNull('parent_id')->get();

        $collections = Collection::all();
        return view('admin.products.show', compact('product', 'categoryChildrens', 'collections'));
    }
}
