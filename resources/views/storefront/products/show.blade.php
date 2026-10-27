@extends('layouts.layout_customer')
@section('title', $product->name)
@section('ContentCustomer')
    <main class="p-6 bg-white">

        <div class="max-w-7xl mx-auto ">

            <div class="mb-5">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-slate-600 hover:text-blue-600 hover:-translate-1 transition"><i
                        class="bx bx-caret-big-left text-blue-600 relative top-[2px] text-lg"></i><span>Back to
                        home</span></a>
            </div>

            <div x-data="productPage({
                productPrice: {{ (float) $product->price }},
                comparePrice: @js($product->compare_price !== null ? (float) $product->compare_price : null),
                availability: @js($product->availability),
                preorderClosed: @js($preorderClosed),
                options: @js(
    $product->options
        ->map(function ($option) {
            return [
                'id' => $option->id,
                'name' => $option->option_name,
                'values' => $option->values
                    ->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'image_id' => $value->image_id,
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })
        ->values()
        ->all(),
),
                images: @js(
    $product->images
        ->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => asset('storage/' . $image->path),
            ];
        })
        ->values()
        ->all(),
),
                variants: @js(
    $product->variants
        ->map(function ($variant) {
            return [
                'id' => $variant->id,
                'price' => (float) $variant->price,
                'comparePrice' => $variant->compare_price !== null ? (float) $variant->compare_price : null,
                'stock' => $variant->stock,
                'is_active' => $variant->is_active,
                'sku' => $variant->sku,
                'values' => $variant->optionValues->pluck('id')->values()->all(),
            ];
        })
        ->values()
        ->all(),
),
            })" class="grid md:grid-cols-2 gap-8" @click="closeOptionError()">
                <div>
                    <div class="h-120 bg-gray-100 rounded-lg overflow-hidden">
                        @if ($product->images->count() > 0)
                            <img :src="mainImage" alt="{{ $product->name }}"
                                class="w-full h-full object-contain bg-white">
                        @else
                            <div class="flex w-full h-full items-center justify-center text-slate-400">
                                <i class="bx bx-image text-5xl"></i>
                            </div>
                        @endif
                    </div>

                    @if ($product->images->count() > 0)
                        <div class="mt-3">
                            <div class="flex gap-2 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2">
                                <template x-for="image in visibleImages" :key="image.id">
                                    <button type="button" @click="mainImage = image.url" class="shrink-0 snap-start">
                                        <img :src="image.url" :alt="'product image ' + image.id"
                                            class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80 hover:scale-105 transition">
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex flex-row gap-3 mb-3">
                        <div>
                            @if ($product->availability === 'preorder')
                                <span
                                    class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $preorderClosed ? 'bg-red-100 text-red-700' : 'bg-slate-500 text-white border border-slate-200 shadow  rounded-lg' }}">
                                    {{ $preorderClosed ? 'Pre-Order Closed' : 'Pre-Order' }}
                                </span>
                            @endif
                        </div>
                        <span class="text-slate-700 ">{{ $product->preorder_release_label }}</span>
                    </div>
                    <h1 class="text-xl font-semibold">
                        {{ $product->name }}
                    </h1>

                    <div class="mt-2 flex flex-wrap items-baseline gap-3">
                        {{-- Current selling price --}}
                        <p class="text-2xl text-blue-600 font-bold" x-text="priceDisplay"></p>

                        {{-- Original price before discount --}}
                        <template x-if="hasComparePrice">
                            <p class="text-base text-slate-400 line-through" x-text="comparePriceDisplay"></p>
                        </template>
                    </div>

                    @if ($product->options->count() > 0)
                        <div class="mt-6 space-y-5">

                            <template x-for="(option, optionIndex) in options" :key="option.id">
                                <div>
                                    <p class="font-semibold mb-2" x-text="option.name">
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="value in option.values" :key="value.id">
                                            <button type="button" x-show="hasActiveVariantForValue(optionIndex, value.id)"
                                                @click="selectedValue(optionIndex, value)"
                                                :disabled="!isOptionEnabled(optionIndex) ||
                                                    !isValueAvailable(optionIndex, value.id)"
                                                :class="{
                                                    'border-blue-600 bg-blue-50 text-blue-500 ': selected[option.id] ==
                                                        value.id,

                                                    'border-slate-200 hover:bg-gray-100': selected[option.id] != value
                                                        .id,

                                                    'opacity-50 ':
                                                        !isOptionEnabled(optionIndex),

                                                    'line-through text-slate-400 cursor-not-allowed': isOptionEnabled(
                                                            optionIndex) &&
                                                        !isValueAvailable(optionIndex, value.id)
                                                }"
                                                class="border px-4 py-1 rounded-md transition">
                                                <span x-text="value.value"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif

                    @php
                        $description = $product->description ?? '';
                        $wordCount = str_word_count(strip_tags($description));
                        $hasMore = $wordCount > 100;
                    @endphp
                    <div class="mt-6 flex gap-3">
                        <div class="relative">
                            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                @csrf

                                <button type="submit"
                                    class="border border-slate-200 px-6 py-2 rounded hover:bg-gray-100 flex gap-2 items-center">

                                    <svg class="w-5 h-5 {{ $isWishlist ? 'text-red-500 fill-red-500' : 'text-gray-500 fill-none' }}"
                                        viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke="currentColor" stroke-width="50"
                                            d="M512 898.56l-9.813-4.693C485.546 884.906 85.333 679.68 85.333 384c0-115.626 53.76-202.666 147.2-239.36C325.546 108.373 435.2 132.266 512 203.52c76.8-71.253 186.88-95.146 279.466-58.88C884.906 181.333 938.666 268.373 938.666 384c0 295.68-400.213 501.333-416.853 509.866z" />
                                    </svg>
                                    Wishlist
                                </button>
                            </form>
                            <div x-show="optionError && optionErrorTarget === 'wishlist'" x-transition
                                class="absolute left-1/2 bottom-full mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-800 px-3 py-2 text-xs text-white shadow-lg z-50">
                                <span x-text="optionErrorMessage"></span>

                                <div
                                    class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-800">
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <form action="{{ route('cart.add', $product->id) }}" method="POST" @click.stop
                                @submit.prevent="submitAction($event, 'cart')">
                                @csrf

                                <input type="hidden" name="variant_id" :value="selectedVariant?.id ?? ''">

                                <button type="submit" :disabled="isUnavailableForPurchase"
                                    class="border border-slate-200 px-6 py-2 rounded hover:bg-gray-100 cursor-pointer disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 disabled:hover:bg-slate-100">

                                    <span
                                        x-text="isPreorderClosed ? 'Pre-Order Closed' : (isSoldOut ? 'Sold Out' : '+ Cart')">
                                    </span>
                                </button>
                            </form>
                            <div x-show="optionError && optionErrorTarget === 'cart'" x-transition
                                class="absolute left-1/2 bottom-full mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-800 px-3 py-2 text-xs text-white shadow-lg z-50">
                                <span x-text="optionErrorMessage"></span>

                                <div
                                    class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-800">
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($description)

                        <div class="mt-4 text-gray-600 text-sm">
                            <h1 class="font-bold mb-3">Description :</h1>
                            @if ($hasMore)
                                <p x-show="(!descriptionExpanded)">
                                    {{ Str::words($description, 100, '...') }}
                                </p>
                                {{-- full description --}}
                                <p x-show="descriptionExpanded" class="whitespace-pre-line">{{ $description }}</p>

                                <button type="button" @click="descriptionExpanded = !descriptionExpanded"
                                    class="text-blue-600 text-sm mt-2 hover:underline">

                                    <span x-text="descriptionExpanded ? 'Hide' : 'See more'"></span>
                                </button>
                            @else
                                {{-- If 100 words or fewer, display directly --}}
                                <p class="whitespace-pre-line">{{ $description }}</p>
                            @endif

                        </div>
                    @endif

                    @php
                        $specifications = collect($product->attributes ?? [])->filter(
                            fn($value) => is_scalar($value) && filled($value),
                        );
                    @endphp

                    @if ($specifications->isNotEmpty())
                        <div class="mt-6 text-gray-600 text-sm">
                            <h2 class="font-bold mb-3">Product Specifications</h2>

                            <dl class="border border-slate-200 rounded-lg divide-y divide-slate-200">
                                @foreach ($specifications as $key => $value)
                                    <div class="flex justify-between gap-4 p-3">
                                        <dt class="text-slate-500">
                                            {{ \Illuminate\Support\Str::headline($key) }}
                                        </dt>

                                        <dd class="text-slate-800 font-medium text-right">
                                            {{ $value }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
    <script>
        function productPage(data) {
            return {
                // data
                productPrice: data.productPrice,
                comparePrice: data.comparePrice,
                variants: data.variants,
                availability: data.availability,
                preorderClosed: Boolean(data.preorderClosed),
                options: data.options,
                images: data.images,
                variants: data.variants,

                visibleImageIds: null,
                // STATE
                selected: {},
                optionError: false,
                optionErrorMessage: '',
                optionErrorTarget: null,

                mainImage: data.images.length ?
                    data.images[0].url : '{{ asset('gambar/default.jpg') }}',

                descriptionExpanded: false,

                // VISIBLE IMAGES
                get visibleImages() {
                    // when no filter is applied
                    // // // → show all images
                    if (this.visibleImageIds === null) {
                        return this.images;
                    }

                    // Filter already exists
                    return this.images.filter(image =>
                        this.visibleImageIds.includes(
                            Number(image.id)
                        )
                    );
                },

                //  OPTION ENABLED
                isOptionEnabled(optionIndex) {
                    // The first option can always be selected.
                    if (optionIndex === 0) {
                        return true;
                    }

                    // The next option only becomes active
                    // once all previous options have been selected
                    return this.options.slice(0, optionIndex).every(option => this.selected[option.id]);
                },

                //  SELECT VALUE
                selectedValue(optionIndex, value) {
                    this.optionError = false;
                    this.optionErrorMessage = '';
                    this.optionErrorTarget = null;

                    const option = this.options[optionIndex];

                    // CLICK THE SAME VALUE
                    if (this.selected[option.id] === value.id) {
                        // Deselect
                        delete this.selected[option.id];

                        // Reset all subsequent options
                        for (
                            let i = optionIndex + 1; i < this.options.length; i++
                        ) {
                            delete this.selected[
                                this.options[i].id
                            ];
                        }

                        // Only reset the image if this value is
                        // controlling the image
                        // Return to the first image
                        if (value.image_id) {

                            this.visibleImageIds = null;
                            if (this.images.length > 0) {

                                this.mainImage = this.images[0].url;
                            }
                        }
                        return;
                    }
                    // new value
                    this.selected[option.id] = value.id;

                    //rest all options
                    for (
                        let i = optionIndex + 1; i < this.options.length; i++
                    ) {
                        delete this.selected[
                            this.options[i].id
                        ];
                    }
                    // IMAGE
                    if (value.image_id) {
                        this.visibleImageIds = [Number(value.image_id)];

                        const image = this.images.find(
                            image =>
                            Number(image.id) === Number(value.image_id)
                        );

                        if (image) {
                            this.mainImage = image.url;
                        }
                    }
                    // If the value does not have an image_id,
                    // gallery IS NOT CHANGED.
                    console.log(
                        'selected',
                        this.selected
                    );
                },
                // FIND MATCHING VARIANTS
                get matchingVariants() {
                    const selectedValueIds = Object.values(this.selected);

                    if (selectedValueIds.length === 0) {
                        return this.variants;
                    }

                    return this.variants.filter(variant =>
                        selectedValueIds.every(valueId =>
                            variant.values.includes(Number(valueId))
                        )
                    );
                },

                // PRICE
                get priceDisplay() {
                    // If a variant is selected, display its price.
                    if (this.selectedVariant) {
                        return this.formatPrice(this.selectedVariant.price);
                    }

                    // For products with options, display the starting price.
                    if (this.hasOptions) {
                        // If all options are selected but no matching
                        // available variant exists, display unavailable.
                        if (this.allOptionsSelected) {
                            return 'Unavailable';
                        }

                        const prices = this.variants
                            .filter(variant => variant.is_active)
                            .map(variant => Number(variant.price))
                            .filter(price => Number.isFinite(price));

                        if (prices.length === 0) {
                            return 'Unavailable';
                        }

                        const minPrice = Math.min(...prices);
                        const maxPrice = Math.max(...prices);

                        // If all variants have the same price, show one price.
                        if (minPrice === maxPrice) {
                            return this.formatPrice(minPrice);
                        }

                        // Otherwise, show the starting price.
                        return this.formatPrice(minPrice) + ' - ' + this.formatPrice(maxPrice);
                    }

                    // For a product without variants, use its base price.
                    return this.formatPrice(this.productPrice);
                },
                get isSoldOut() {
                    if (this.availability !== 'ready') {
                        return false;
                    }

                    const activeVariants = this.variants.filter(
                        variant => variant.is_active
                    );

                    return activeVariants.length > 0 &&
                        activeVariants.every(
                            variant => Number(variant.stock) <= 0
                        );
                },

                formatPrice(price) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                },

                // does the product have options?
                get hasOptions() {
                    return this.options.length > 0;
                },
                // have all options been selected?
                get allOptionsSelected() {
                    if (!this.hasOptions) {
                        return true;
                    }

                    return this.options.every(
                        option => this.selected[option.id]
                    );
                },
                // look for the variant that really matches the choice
                get selectedVariant() {
                    // Single product:
                    // use its active variant automatically.
                    if (!this.hasOptions) {
                        return this.variants.find(variant => variant.is_active) ?? null;
                    }

                    // variant product:
                    // all options must be selected first.
                    if (!this.allOptionsSelected) {
                        return null;
                    }

                    const selectedIds = this.options
                        .map(option => Number(this.selected[option.id]))
                        .sort((a, b) => a - b);

                    return this.variants.find(variant => {
                        if (!variant.is_active) {
                            return false;
                        }

                        // ready: stock must be available.
                        // Pre-order: stock of 0 is allowed.
                        if (this.availability === 'ready' && Number(variant.stock) <= 0) {
                            return false;
                        }

                        const variantIds = variant.values
                            .map(Number)
                            .sort((a, b) => a - b);

                        return (
                            variantIds.length === selectedIds.length &&
                            variantIds.every(
                                (id, index) => id === selectedIds[index]
                            )
                        );
                    }) ?? null;
                },

                // called when Cart / Wishlist is pressed
                submitAction(event, target) {
                    if (!this.hasOptions) {
                        if (!this.selectedVariant) {
                            this.optionError = true;
                            this.optionErrorTarget = target;
                            this.optionErrorMessage = 'This product is unavailable.';
                            return;
                        }

                        if (
                            this.availability === 'ready' &&
                            Number(this.selectedVariant.stock) <= 0
                        ) {
                            this.optionError = true;
                            this.optionErrorTarget = target;
                            this.optionErrorMessage = 'Sold out.';
                            return;
                        }

                        event.currentTarget.submit();
                        return;
                    }

                    if (!this.allOptionsSelected) {
                        this.optionError = true;
                        this.optionErrorTarget = target;
                        this.optionErrorMessage = this.getOptionErrorMessage();
                        return;
                    }

                    if (!this.selectedVariant) {
                        this.optionError = true;
                        this.optionErrorTarget = target;
                        this.optionErrorMessage = 'This combination is unavailable.';
                        return;
                    }

                    this.optionError = false;
                    this.optionErrorTarget = null;
                    this.optionErrorMessage = '';

                    event.currentTarget.submit();
                },
                getMissingOptions() {
                    return this.options
                        .filter(option => !this.selected[option.id])
                        .map(option => option.name);
                },

                getOptionErrorMessage() {
                    const missing = this.getMissingOptions();

                    if (missing.length === 1) {
                        return `Please select ${missing[0]} first`;
                    }

                    if (missing.length === 2) {
                        return `Please select ${missing[0]} and ${missing[1]} first`;
                    }

                    return '';
                },

                closeOptionError() {
                    this.optionError = false;
                    this.optionErrorMessage = '';
                    this.optionErrorTarget = null;
                },
                isValueAvailable(optionIndex, valueId) {
                    const selectedOtherValues = this.options
                        .filter((_, index) => index !== optionIndex)
                        .map(option => this.selected[option.id])
                        .filter(Boolean)
                        .map(Number);

                    return this.variants.some(variant => {
                        // Inactive variants are not considered available.
                        if (!variant.is_active) {
                            return false;
                        }

                        // Variant is active but out of stock = unavailable.
                        if (this.availability === 'ready' && Number(variant.stock) <= 0) {
                            return false;
                        }

                        // The variant must have the value being checked.
                        if (!variant.values.includes(Number(valueId))) {
                            return false;
                        }


                        // Must be compatible with other selected options.
                        return selectedOtherValues.every(selectedValue =>
                            variant.values.includes(selectedValue)
                        );
                    });
                },

                hasActiveVariantForValue(optionIndex, valueId) {
                    const selectedOtherValues = this.options
                        .filter((_, index) => index !== optionIndex)
                        .map(option => this.selected[option.id])
                        .filter(Boolean)
                        .map(Number);

                    return this.variants.some(variant => {
                        // Only inactive items are hidden.
                        if (!variant.is_active) {
                            return false;
                        }


                        // Stock is not checked here.
                        // So, active items with 0 stock still appear.
                        if (!variant.values.includes(Number(valueId))) {
                            return false;
                        }

                        return selectedOtherValues.every(selectedValue =>
                            variant.values.includes(selectedValue)
                        );
                    });
                },
                get isPreorderClosed() {
                    return this.availability === 'preorder' &&
                        this.preorderClosed;
                },

                get isUnavailableForPurchase() {
                    if (this.availability === 'preorder') {
                        return this.preorderClosed;
                    }

                    if (this.availability === 'ready') {
                        return this.isSoldOut;
                    }

                    return true;
                },
                get activeComparePrice() {
                    const variant = this.selectedVariant;

                    const value =
                        variant?.comparePrice !== null &&
                        variant?.comparePrice !== undefined ?
                        variant.comparePrice :
                        this.comparePrice;

                    if (value === null || value === undefined || value === '') {
                        return null;
                    }

                    const price = Number(value);

                    return Number.isFinite(price) && price > 0 ?
                        price :
                        null;
                },

                get activeSellingPrice() {
                    const variant = this.selectedVariant;

                    return variant ?
                        Number(variant.price) :
                        Number(this.productPrice);
                },

                get hasComparePrice() {
                    // Wait until a valid variant is selected when the product has options.
                    if (this.hasOptions && !this.selectedVariant) {
                        return false;
                    }
                    const comparePrice = this.activeComparePrice;

                    return comparePrice !== null &&
                        comparePrice > this.activeSellingPrice;
                },

                get comparePriceDisplay() {
                    const price = this.activeComparePrice;

                    if (price === null) {
                        return '';
                    }

                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(price);
                },
            };
        }
    </script>
@endsection
