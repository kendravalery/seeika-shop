@extends('layouts.layout_customer')
@section('title' ,'Wishlist')
@section('ContentCustomer')
    <div x-data="{
        wishlistDeleteOpen: false,
        wishlistDeleteName: '',
        wishlistDeleteAction: '',

        openWishlistDelete(name, action) {
            this.wishlistDeleteName = name;
            this.wishlistDeleteAction = action;
            this.wishlistDeleteOpen = true;
        },

        closeWishlistDelete() {
            this.wishlistDeleteOpen = false;
            this.wishlistDeleteName = '';
            this.wishlistDeleteAction = '';
        },
    }" class="flex flex-col lg:flex-row gap-5 max-w-7xl mx-auto p-4 sm:p-6">

        @include('customer.components.account_sidebar')

        <main class="flex-1 bg-white p-5 rounded-xl shadow">
            <div class="mb-8 border-b ">
                <h1 class="font-bold text-2xl mb-4">Wishlist</h1>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 ">
                @forelse ($wishlists as $wishlist)
                    @php
                        $product = $wishlist->product;
                    @endphp

                    @if ($product)
                        @php
                            $activeVariants = $product->variants->where('is_active', true);
                            $firstActiveVariant = $activeVariants->first();
                        @endphp

                        <article
                            class="bg-white rounded-xl p-2 shadow-sm hover:shadow-lg hover:-translate-y-1 transition duration-200 group relative h-full flex flex-col"
                            x-data="productPrice({
                                isVariant: {{ $product->options->count() > 0 ? 'true' : 'false' }},
                                prices: @js($activeVariants->pluck('price')->map(fn($price) => (float) $price)->values()),
                                comparePrice: @js($firstActiveVariant?->compare_price !== null ? (float) $firstActiveVariant->compare_price : null)
                            })">
                            @if ($product->status !== 'active')
                                <span
                                    class="absolute top-3 left-3 z-10 rounded-lg bg-red-600 px-2 py-1 text-xs sm:text-sm font-semibold text-white shadow-lg">
                                    Unavailable
                                </span>
                            @elseif ($product->availability === 'preorder')
                                @if ($product->preorder_closed ?? false)
                                    <span
                                        class="absolute top-3 left-3 z-10 rounded-lg bg-red-600 px-2 py-1 text-xs sm:text-sm font-semibold text-white shadow-lg">
                                        Pre-Order Closed
                                    </span>
                                @else
                                    <span
                                        class="absolute top-3 left-3 z-10 rounded-lg border border-slate-200 bg-slate-500 px-2 py-1 text-xs sm:text-sm font-mono text-white shadow-lg">
                                        Pre-Order
                                    </span>
                                @endif
                            @elseif ($activeVariants->isNotEmpty() && $activeVariants->every(fn($variant) => $variant->stock <= 0))
                                <span
                                    class="absolute top-3 left-3 z-10 rounded-lg bg-black px-3 py-1 text-xs sm:text-sm font-mono text-white shadow-lg">
                                    Sold out
                                </span>
                            @endif

                            <a href="{{ route('products.show', $product->slug) }}" class="block">
                                <div class="h-40 sm:h-60 rounded-md bg-gray-100 overflow-hidden">
                                    @if ($product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                    @else
                                        <div class="flex w-full h-full items-center justify-center text-slate-400">
                                            <i class="bx bx-image text-4xl"></i>
                                        </div>
                                    @endif
                                </div>

                                <h4 class="mt-3 font-medium line-clamp-2 text-sm sm:text-base min-h-[2.5rem] sm:h-[3rem]">
                                    {{ $product->name }}
                                </h4>
                                @if ($product->status !== 'active')
                                    <p class="mt-1 text-xs leading-5 text-red-600">
                                        This product is no longer available.
                                    </p>
                                @endif

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
                                    <div class="mt-1 min-h-[2rem] flex items-center justify-between gap-2">
                                        <span class="min-w-0 font-semibold text-blue-600 text-sm sm:text-base"
                                            x-text="displayPrice">
                                        </span>

                                        <span class="text-xs shrink-0 text-slate-500">
                                            {{ $product->sold_count ?? 0 }} x
                                        </span>
                                    </div>
                                </div>
                            </a>

                            <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                @if ($product->status === 'active')
                                    <a href="{{ route('products.show', $product->slug) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm text-white transition hover:bg-blue-700">
                                        View Product
                                        <i class="bx bx-eye text-lg"></i>
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-500">
                                        <i class="bx bx-info-circle text-lg"></i>
                                        Unavailable
                                    </span>
                                @endif

                                <button type="button"
                                    @click="openWishlistDelete(@js($product->name),@js(route('wishlist.delete', $product->id)))"
                                    aria-label="Remove from wishlist" title="Remove from Wishlist"
                                    class="inline-flex items-center justify-center rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 p-2 transition cursor-pointer">
                                    <i class="bx bx-trash text-lg"></i>
                                </button>
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="col-span-full flex items-center justify-center py-20">
                        <p class="font-semibold text-center text-slate-500">
                            Your wishlist is still empty.
                        </p>
                    </div>
                @endforelse
            </div>
            @if ($wishlists->hasPages())
                <div class="mt-6 border-t border-slate-200 pt-4">
                    {{ $wishlists->onEachSide(1)->links() }}
                </div>
            @endif
        </main>

        {{-- remove  Modal --}}
        <div x-show="wishlistDeleteOpen" x-cloak x-transition.opacity @click.self="closeWishlistDelete()"
            @keydown.escape.window="if (wishlistDeleteOpen) closeWishlistDelete()"
            class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 p-4">

            <div x-show="wishlistDeleteOpen" x-transition.scale @click.outside="closeWishlistDelete()" role="dialog"
                aria-modal="true" aria-labelledby="wishlistDeleteTitle"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl">

                <div class="px-6 py-5 text-center">
                    <h2 id="wishlistDeleteTitle" class="text-lg font-semibold text-slate-900">
                        Remove from Wishlist?
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Are you sure you want to remove
                        <span class="font-semibold text-red-500" x-text="wishlistDeleteName">
                        </span>
                        from your wishlist?
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        You can add this product to your wishlist again later.
                    </p>
                </div>

                <div class="flex gap-3 border-t border-slate-200 px-6 py-4">
                    <button type="button" @click="closeWishlistDelete()"
                        class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </button>

                    <form method="POST" :action="wishlistDeleteAction" class="flex-1">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 cursor-pointer">
                            Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
                        ((this.comparePrice - this.minPrice) /
                            this.comparePrice) * 100
                    );
                },

                formatPrice(price) {
                    return 'Rp ' +
                        new Intl.NumberFormat('id-ID').format(price);
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
