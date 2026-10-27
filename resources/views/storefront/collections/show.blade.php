@extends('layouts.layout_customer')
@section('title', $title)

@section('ContentCustomer')
    <main x-data="{
        mobileFilterOpen: false,
        sortOpen: false,
        mobileAvailability: @js($availability)
    }">
        @if ($banner)
            <div class="w-full border-b-5 border-b-slate-400">
                <img src="{{ asset('storage/' . $banner) }}" alt="{{ $title }}"
                    class="w-full max-h-[300px] object-cover">
            </div>
        @endif
        <div class="shadow px-4 py-2 lg:py-5 border-b border-b-slate-200 bg-white">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $title }}
                </h1>
            </div>
        </div>

        {{-- filters and products --}}
        <section class="w-full max-w-7xl mx-auto px-4 py-4 lg:p-4">
            <div class="flex flex-col lg:flex-row gap-4">

                <aside class="hidden lg:block w-72 shrink-0 p-4 bg-white border border-slate-200 shadow rounded-lg h-fit">

                    <p class="font-semibold text-gray-600 text-xl mb-4">
                        Filters
                    </p>

                    <div class="flex items-center justify-between mb-3">
                        <p class="font-medium text-gray-700">
                            Availability
                        </p>

                        <a href="{{ request()->fullUrlWithQuery([
                            'availability' => null,
                            'page' => null,
                        ]) }}"
                            class="text-blue-600 text-sm hover:text-blue-700">
                            Reset
                        </a>
                    </div>

                    <form action="{{ url()->current() }}" method="GET">
                        <input type="hidden" name="sort" value="{{ $sort }}">

                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="availability" value="all"
                                class="p-2 text-sm text-center rounded-full cursor-pointer transition {{ $availability === 'all' ? 'bg-blue-100 text-blue-600 border border-blue-600' : 'border border-slate-300 hover:bg-blue-50 hover:border-blue-600' }}">
                                All items
                            </button>

                            <button type="submit" name="availability" value="ready"
                                class="p-2 text-sm text-center rounded-full cursor-pointer transition {{ $availability === 'ready' ? 'bg-blue-100 text-blue-600 border border-blue-600' : 'border border-slate-300 hover:bg-blue-50 hover:border-blue-600' }}">
                                Ready stock
                            </button>

                            <button type="submit" name="availability" value="preorder"
                                class="col-span-2 p-2 text-sm text-center rounded-full cursor-pointer transition {{ $availability === 'preorder' ? 'bg-blue-100 text-blue-600 border border-blue-600' : 'border border-slate-300 hover:bg-blue-50 hover:border-blue-600' }}">
                                Pre-order
                            </button>
                        </div>
                    </form>
                </aside>
                <section class="w-full flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-sm text-gray-600">
                            {{ $products->total() }}
                            {{ $products->total() === 1 ? 'product' : 'products' }}
                        </p>
                        <div class="flex flex-row gap-2">

                            <button type="button" x-on:click="mobileFilterOpen = true"
                                class="lg:hidden inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-800 hover:bg-slate-50 transition">
                                <i class="bx bx-filter text-lg"></i>
                                Filter
                            </button>

                            <div class="relative">
                                <button type="button" @click="sortOpen = !sortOpen"
                                    class="border border-slate-200 px-4 py-2 rounded-lg shadow-sm text-sm flex items-center gap-1 bg-white">
                                    <i class="bx bx-sort"></i>
                                    <span>Sort</span>
                                    <i class="bx bx-chevron-down"></i>
                                </button>

                                <div x-show="sortOpen" x-cloak @click.outside="sortOpen = false"
                                    class="absolute right-0 top-full mt-2 z-30 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-2">

                                    @php
                                        $sorts = [
                                            'newest' => 'Newest',
                                            'price_low' => 'Price: Low to High',
                                            'price_high' => 'Price: High to Low',
                                        ];
                                    @endphp

                                    @foreach ($sorts as $value => $label)
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'sort' => $value,
                                            'page' => null,
                                        ]) }}"
                                            class="block px-4 py-2 text-sm hover:bg-slate-50 {{ $sort === $value ? 'bg-slate-50 font-medium text-blue-600' : 'text-slate-700' }}">
                                            {{ $label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr class="border-slate-200">
                    @if ($products->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 sm:grid-cols-3 2xl:grid-cols-4 gap-4 py-4">
                            @foreach ($products as $product)
                                @php
                                    $activeVariants = $product->variants->where('is_active', true);
                                    $firstActiveVariant = $activeVariants->first();
                                @endphp
                                <a href="{{ route('products.show', $product->slug) }}">
                                    <article
                                        class="bg-white rounded-xl p-2 shadow-sm hover:shadow-lg hover:-translate-y-1 transition duration-200 group relative h-full flex flex-col"
                                        x-data="productPrice({
                                            isVariant: {{ $product->options->count() > 0 ? 'true' : 'false' }},
                                            prices: @js($product->variants->where('is_active', true)->pluck('price')->map(fn($price) => (float) $price)->values()),
                                            comparePrice: @js($firstActiveVariant?->compare_price !== null ? (float) $firstActiveVariant->compare_price : null)
                                        })">

                                        @if ($product->availability === 'preorder')
                                            @if ($product->preorder_closed ?? false)
                                                <span
                                                    class="absolute top-1 left-1 z-10 bg-red-600 text-white font-mono rounded-lg text-sm shadow-lg px-2 py-1">
                                                    Pre-Order Closed
                                                </span>
                                            @else
                                                <span
                                                    class="absolute top-1 left-1 z-10  font-mono rounded-lg text-sm border border-slate-200 shadow-lg px-2 py-1 text-white bg-slate-500 ">
                                                    Pre-Order
                                                </span>
                                            @endif
                                        @elseif ($activeVariants->isNotEmpty() && $activeVariants->every(fn($variant) => $variant->stock <= 0))
                                            <span
                                                class="absolute top-1 left-1 z-10 font-mono rounded-lg text-sm shadow-lg px-4 py-1 bg-black text-white">
                                                Sold out
                                            </span>
                                        @endif

                                        <div class="h-40 sm:h-60 rounded-md bg-gray-100 overflow-hidden">
                                            @if ($product->images->count() > 0)
                                                <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                                    alt=""
                                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                            @else
                                                <div class="flex w-full h-full items-center justify-center text-slate-400">
                                                    <i class="bx bx-image text-2xl"></i>
                                                </div>
                                            @endif

                                        </div>

                                        <h4
                                            class="mt-3 font-medium line-clamp-2 text-sm sm:text-base min-h-[2.5rem] sm:h-[3rem]">
                                            {{ $product->name }}
                                        </h4>

                                        <div class="flex flex-col mt-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <template x-if="hasDiscount">
                                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                        <span class="text-xs text-gray-400 line-through"
                                                            x-text="formatPrice(comparePrice)">
                                                        </span>

                                                        <span class="text-xs text-red-600 font-medium"
                                                            x-text="discountPercentage + '% OFF'">
                                                        </span>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- reserve the same space for discount information --}}
                                            <div class="mt-1 min-h-[2rem] flex items-center justify-between">
                                                <span class="min-w-0 font-semibold text-blue-600" x-text="displayPrice">
                                                </span>
                                                <span class="text-xs shrink-0">
                                                    {{ $product->sold_count ?? 0 }} x
                                                </span>
                                            </div>
                                        </div>
                                    </article>
                                </a>
                            @endforeach

                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center min-h-[30vh] text-center">
                            <h2 class="text-slate-500 text-xl font-semibold">
                                No products found
                            </h2>

                            <p class="text-sm text-gray-400 mt-2">
                                Try selecting a different filter.
                            </p>
                        </div>
                    @endif
                    @if ($products->hasPages())
                        <div class="mt-6">
                            {{ $products->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </section>

        {{-- mobile --}}
        <div x-cloak x-show="mobileFilterOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-on:click.self="mobileFilterOpen = false"
            x-on:keydown.escape.window="mobileFilterOpen = false" class="fixed inset-0 z-[100] bg-black/40 lg:hidden">
            <div x-bind:class="mobileFilterOpen ? 'translate-y-0' : 'translate-y-full'" x-on:click.stop
                class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto bg-white rounded-t-2xl shadow-2xl transform transition-transform duration-300 ease-out">
                <div
                    class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-gray-900">
                        Filters
                    </h2>
                    <button type="button" x-on:click="mobileFilterOpen = false"
                        class="p-2 text-gray-500 hover:text-gray-900" aria-label="Close filters">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
                <form action="{{ url()->current() }}" method="GET">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-medium text-gray-700">
                                Availability
                            </h3>

                            <a href="{{ request()->fullUrlWithQuery([
                                'availability' => null,
                                'page' => null,
                            ]) }}"
                                class="text-blue-600 text-sm hover:text-blue-700">
                                Reset
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['all' => 'All items', 'ready' => 'Ready stock', 'preorder' => 'Pre-order'] as $value => $label)
                                <label
                                    x-bind:class="mobileAvailability === '{{ $value }}' ?
                                        'bg-blue-100 text-blue-600 border-blue-600' :
                                        'border-slate-300 hover:bg-blue-50 hover:border-blue-600'"
                                    class="border p-3 text-sm text-center rounded-lg cursor-pointer transition {{ $value === 'preorder' ? 'col-span-2' : '' }}">
                                    <input type="radio" name="availability" value="{{ $value }}"
                                        x-model="mobileAvailability" class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sticky bottom-0 bg-white border-t border-slate-200 p-5">
                        <button type="submit"
                            class="w-full py-3 bg-slate-900 text-white font-semibold rounded-lg hover:bg-slate-700 transition">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        function productPrice(data) {
            return {
                isVariant: data.isVariant,
                prices: data.prices,
                comparePrice: data.comparePrice,

                get minPrice() {
                    return this.prices.length ?
                        Math.min(...this.prices) :
                        null;
                },

                get hasDifferentPrices() {
                    return new Set(this.prices).size > 1;
                },

                get hasDiscount() {
                    return this.comparePrice !== null &&
                        this.minPrice !== null &&
                        this.comparePrice > this.minPrice;
                },

                get discountPercentage() {
                    if (!this.hasDiscount) return 0;

                    return Math.round(
                        ((this.comparePrice - this.minPrice) / this.comparePrice) * 100
                    );
                },

                formatPrice(price) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                },

                get displayPrice() {
                    if (this.minPrice === null) {
                        return 'Price not available';
                    }

                    if (this.isVariant && this.hasDifferentPrices) {
                        return 'From ' + this.formatPrice(this.minPrice);
                    }

                    return this.formatPrice(this.minPrice);
                }
            };
        }
    </script>
@endsection
