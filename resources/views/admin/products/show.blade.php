@extends('layouts.layout_admin')

@section('ContentAdmin')
    @php
        $isVariantProduct = $product->options->isNotEmpty();
        $variant = $product->variants->first();

        // Images assigned to option values should not appear in the product gallery.
        $optionImageIds = $product->options
            ->flatMap(fn($option) => $option->values->pluck('image_id'))
            ->filter()
            ->unique();

        $productImages = $product->images->whereNotIn('id', $optionImageIds)->sortBy('position')->values();

        $productOptions = $product->options
            ->map(function ($option) {
                return [
                    'key' => 'option-' . $option->id,
                    'id' => $option->id,
                    'name' => $option->option_name,
                    'useImages' => $option->values->contains(fn($value) => $value->image_id),
                    'values' => $option->values
                        ->sortBy('position')
                        ->map(function ($value) {
                            return [
                                'key' => 'value-' . $value->id,
                                'id' => $value->id,
                                'value' => $value->value,
                                'position' => $value->position,
                                'imageId' => $value->image_id,
                                'imageUrl' => $value->image ? asset('storage/' . $value->image->path) : null,
                                'imagePreview' => $value->image ? asset('storage/' . $value->image->path) : null,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        $totalStock = $product->variants->sum('stock');
        $totalSold = $product->sold_count ?? 0;
        $totalVariants = $product->variants->count();
        $minPrice = $product->variants->min('price');
        $maxPrice = $product->variants->max('price');
    @endphp

    <div x-data="{
        productType: @js($isVariantProduct ? 'variant' : 'single'),
        images: @js(
    $productImages
        ->map(
            fn($image) => [
                'id' => $image->id,
                'path' => $image->path,
                'is_primary' => (bool) $image->is_primary,
            ],
        )
        ->values(),
),
        options: @js($productOptions),
        photoOpen: false,
        photoSrc: '',

        openPhoto(src) {
            this.photoSrc = src;
            this.photoOpen = true;
        },

        closePhoto() {
            this.photoOpen = false;
            this.photoSrc = '';
        },

        variants: @js(
    $product->variants
        ->map(function ($variant) {
            return [
                'key' => 'variant-' . $variant->id,
                'id' => $variant->id,
                'optionValueKeys' => $variant->optionValues->pluck('id')->map(fn($id) => 'id:' . $id)->values()->all(),
                'sku' => $variant->sku,
                'price' => $variant->price !== null ? number_format($variant->price, 0, ',', '.') : null,
                'compare_price' => $variant->compare_price !== null ? number_format($variant->compare_price, 0, ',', '.') : null,
                'stock' => $variant->stock,
                'max_purchase_per_order' => $variant->max_purchase_per_order,
                'preorder_limit' => $variant->preorder_limit,
                'weight' => $variant->weight,
                'is_active' => (bool) $variant->is_active,
            ];
        })
        ->values(),
),

        getValueToken(value) {
            return value.id ? `id:${value.id}` : `key:${value.key}`;
        },

        variantLabel(variant) {
            return this.options
                .map(option => {
                    const value = option.values.find(value =>
                        variant.optionValueKeys.includes(this.getValueToken(value))
                    );
                    return value?.value ?? '';
                })
                .filter(Boolean)
                .join(' / ');
        },
    }">
        <div>
            {{-- Breadcrumb --}}
            <div class="flex gap-3 text-slate-500 mb-4">
                <a href="{{ route('admin.products.index') }}" class="hover:text-slate-700">Products</a>
                <i class="bx bx-chevron-right text-2xl"></i>
                <p>Show Product</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 my-4">
                <div class="w-full lg:flex-1">
                    {{-- Product Information --}}
                    <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-5">Product Information</h2>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product Name</label>
                            <div class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-800 bg-white">
                                {{ $product->name }}
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Slug</label>
                            <div
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-800 bg-white break-all">
                                {{ $product->slug ?: '-' }}
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                            <div
                                class="w-full min-h-[132px] rounded-lg border border-slate-300 px-4 py-2.5 text-slate-700 bg-white whitespace-pre-line">
                                {{ $product->description ?: '-' }}</div>
                        </div>

                        <div class="mb-5">
                            <h2 class="text-sm font-semibold text-slate-700 mb-3">
                                Product Specifications <span class="text-xs text-slate-400">/ Optional</span>
                            </h2>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Scale</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-lg bg-white text-sm text-slate-800">
                                    {{ data_get($product->attributes, 'scale') ?: '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Manufacturer</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-lg bg-white text-sm text-slate-800">
                                    {{ data_get($product->attributes, 'manufacturer') ?: '-' }}
                                </div>
                            </div>
                        </div>

                        {{-- Product images --}}
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product Images</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="image in images" :key="image.id">
                                    <div class="relative group">
                                        <button type="button" @click="openPhoto('{{ asset('storage') }}/' + image.path)"
                                            class="block w-full h-28 rounded-lg overflow-hidden bg-slate-200 border-2 text-left cursor-pointer"
                                            :class="image.is_primary ? 'border-blue-500' : 'border-slate-200'"
                                            title="View image">
                                            <img :src="'{{ asset('storage') }}/' + image.path"
                                                class="w-full h-full object-cover" alt="Product image">
                                        </button>
                                        <div x-show="image.is_primary"
                                            class="absolute top-1.5 left-1.5 w-6 h-6 rounded-full bg-amber-400 text-white flex items-center justify-center shadow"
                                            title="Primary photo">
                                            <i class="bx bx-star text-sm"></i>
                                        </div>
                                    </div>
                                </template>

                                @if ($productImages->isEmpty())
                                    <div
                                        class="col-span-2 sm:col-span-4 rounded-lg border border-dashed border-slate-300 p-5 text-sm text-slate-500 text-center">
                                        No product images available.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Single product information --}}
                    <div x-show="productType === 'single'" x-cloak
                        class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
                        <h2 class="font-semibold mb-5 text-slate-800">Single Product</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800 break-all">
                                    {{ $variant?->sku ?: '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Price</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                    {{ $variant?->price !== null ? 'Rp ' . number_format($variant->price, 0, ',', '.') : '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Price before discount <span class="text-xs text-slate-400">/ Optional</span>
                                </label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                    {{ $variant?->compare_price !== null ? 'Rp ' . number_format($variant->compare_price, 0, ',', '.') : '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                    {{ $variant?->stock ?? 0 }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Weight (kg)</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                    {{ $variant?->weight !== null ? $variant->weight : '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Max Purchase per
                                    Order</label>
                                <div
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                    {{ $variant?->max_purchase_per_order ?? 'No limit' }}
                                </div>
                            </div>

                            @if ($product->availability === 'preorder')
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Pre-order Limit</label>
                                    <div
                                        class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-800">
                                        {{ $variant?->preorder_limit ?? '-' }}
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                                <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                    <span class="{{ $variant?->is_active ? 'text-green-600' : 'text-slate-500' }}">
                                        {{ $variant ? ($variant->is_active ? 'Active' : 'Inactive') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Variant options --}}
                    <div x-show="productType === 'variant'" x-cloak class="mt-6">
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <h2 class="font-semibold text-lg mb-5 text-slate-800">Product Options</h2>

                            <div id="options-container">
                                <template x-for="option in options" :key="option.key">
                                    <div class="border border-slate-200 rounded-lg p-4 mb-4 shadow-sm">
                                        <label class="block mb-2 text-sm font-medium text-slate-700">Option Name</label>
                                        <div
                                            class="w-full border border-slate-200 p-2.5 rounded-md bg-white text-slate-800 mb-4">
                                            <span x-text="option.name"></span>
                                        </div>

                                        <label class="block mb-2 text-sm font-medium text-slate-700">Values</label>
                                        <div>
                                            <template x-for="value in option.values" :key="value.key">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <div
                                                        class="w-16 border border-slate-200 p-2 rounded-md bg-white text-slate-600 text-center text-sm">
                                                        <span x-text="value.position"></span>
                                                    </div>
                                                    <div
                                                        class="flex-1 border border-slate-200 p-2 rounded-md bg-white text-slate-800 text-sm min-w-0">
                                                        <span x-text="value.value"></span>
                                                    </div>
                                                    <template x-if="value.imagePreview && option.useImages">
                                                        <button type="button" @click="openPhoto(value.imagePreview)"
                                                            class="shrink-0 w-10 h-10 rounded-md overflow-hidden border border-slate-200 hover:border-blue-400 cursor-pointer"
                                                            title="View option image">
                                                            <img :src="value.imagePreview"
                                                                class="w-full h-full object-cover" alt="Option value image">
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Variant cards use the same field order as Edit Product, but are read-only. --}}
                        <div class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="font-semibold text-lg text-slate-800">Variants</h2>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(variant, variantIndex) in variants" :key="variant.key">
                                    <div class="border border-slate-200 rounded-xl bg-white p-4 sm:p-5 shadow-sm">
                                        <div class="mb-4">
                                            <h3 class="font-semibold text-slate-800"
                                                x-text="variantLabel(variant) || `Variant ${variantIndex + 1}`"></h3>
                                            <p class="text-xs text-slate-400 mt-1">Variant #<span
                                                    x-text="variantIndex + 1"></span></p>
                                        </div>

                                        {{-- First row: SKU, Price, Stock, Weight --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">SKU</label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800 break-all"
                                                    x-text="variant.sku || '-'"></div>
                                            </div>

                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Price</label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800 whitespace-nowrap"
                                                    x-text="variant.price !== null ? `Rp ${variant.price}` : '-' "></div>
                                            </div>

                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Stock</label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800"
                                                    x-text="variant.stock ?? 0"></div>
                                            </div>

                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Weight
                                                    (kg)</label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800"
                                                    x-text="variant.weight !== null && variant.weight !== '' ? variant.weight : '-' ">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Second row: Compare Price, purchase limit, preorder limit, status --}}
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 items-end {{ $product->availability === 'preorder' ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }}">
                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                                    Price before discount <span class="text-slate-400">/ Optional</span>
                                                </label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800 whitespace-nowrap"
                                                    x-text="variant.compare_price !== null ? `Rp ${variant.compare_price}` : '-' ">
                                                </div>
                                            </div>

                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                                    Max Purchase per Order <span class="text-slate-400">/ Optional</span>
                                                </label>
                                                <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800"
                                                    x-text="variant.max_purchase_per_order ?? 'No limit'"></div>
                                            </div>

                                            @if ($product->availability === 'preorder')
                                                <div class="min-w-0">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                                        Pre-order Limit <span class="text-slate-400">/ Optional</span>
                                                    </label>
                                                    <div class="border border-slate-200 rounded-md p-2 w-full min-w-0 text-sm text-slate-800"
                                                        x-text="variant.preorder_limit ?? '-' "></div>
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                                                <div class="flex items-center gap-2 min-h-[38px]">
                                                    <span class="text-sm whitespace-nowrap"
                                                        :class="variant.is_active ? 'text-green-600' : 'text-slate-500'"
                                                        x-text="variant.is_active ? 'Active' : 'Inactive'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                @if ($product->variants->isEmpty())
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                                        No variants available.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Product Overview --}}
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm mt-6">
                        <div class="px-5 py-4 border-b border-slate-200">
                            <h2 class="font-semibold text-slate-800">Product Overview</h2>
                        </div>

                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Sold</label>
                                <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                    {{ number_format($totalSold) }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Stock</label>
                                <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                    {{ number_format($totalStock) }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Variants</label>
                                <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                    {{ number_format($totalVariants) }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Price Range</label>
                                <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                    @if ($minPrice === null)
                                        -
                                    @elseif ($minPrice == $maxPrice)
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp
                                        {{ number_format($maxPrice, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right sidebar --}}
                <div class="w-full lg:w-80">
                    <div class="lg:sticky lg:top-4">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
                            <div class="px-5 py-4 border-b border-slate-200">
                                <h2 class="font-semibold text-slate-800">Product Organization</h2>
                                <p class="text-sm text-slate-500 mt-1">Organize how this product appears in shop.</p>
                            </div>

                            <div class="p-5 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Collection |
                                        @if ($product->collection)
                                            @if ($product->collection->status === 'active')
                                                <span class="text-green-500">
                                                    Active
                                                </span>
                                            @endif
                                            @if ($product->collection->status === 'draft')
                                                <span class="text-yellow-400">
                                                    Draft
                                                </span>
                                            @endif
                                            @if ($product->collection->status === 'inactive')
                                                <span class="text-slate-300">
                                                    Inactive
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-400 text-xs">
                                                No Collection
                                            </span>
                                        @endif
                                    </label>
                                    <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                        {{ $product->collection?->name ?? 'No Collection' }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                                    <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                        {{ $product->category?->name ?? 'No Category' }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Availability</label>
                                    <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                        {{ match ($product->availability) {
                                            'ready' => 'Ready',
                                            'preorder' => 'Pre-Order',
                                            default => 'No Availability',
                                        } }}
                                    </div>
                                </div>

                                @if ($product->availability === 'preorder')
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pre-order
                                            Release</label>
                                        <div
                                            class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm text-slate-700">
                                            {{ $product->preorder_release_label ?: '-' }}
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                                    <div class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm">
                                        {{ ucfirst($product->status ?? 'No Status') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 mt-4">
                            <a href="{{ route('admin.products.index') }}"
                                class="px-4 py-2 bg-white w-full shadow-lg rounded border border-slate-200 hover:bg-slate-50 text-center">
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Image preview modal --}}
        <template x-if="photoOpen">
            <div x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
                @click.self="closePhoto()" @keydown.escape.window="closePhoto()">
                <div class="relative max-w-3xl max-h-[90vh]">
                    <button type="button" @click="closePhoto()"
                        class="absolute -top-10 right-0 text-white hover:text-slate-300" title="Close">
                        <i class="bx bx-x text-3xl"></i>
                    </button>
                    <img :src="photoSrc" class="max-w-full max-h-[85vh] rounded-lg shadow-xl" alt="Image preview">
                </div>
            </div>
        </template>
    </div>
@endsection
