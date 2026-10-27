@extends('layouts.layout_customer')
@section('ContentCustomer')
    <div class="max-w-7xl mx-auto p-4 sm:p-6 py-5 lg:py-5 md:py-5">
        @if (session('success'))
            <script>
                alert("{{ session('success') }}");
            </script>
        @endif

        {{-- carousel banner infinite loop --}}
        <div class="mb-5 p-2">
            <div class="relative w-full overflow-hidden rounded-xl ">
                <div id="carousel" class="flex gap-4 transition-transform duration-500 ease-in-out items-center">

                    @php
                        $cloneCount = $banners->count();
                    @endphp

                    {{-- Rear Clone --}}
                    @if ($banners->count() > 0)
                        @for ($i = $banners->count() - $cloneCount; $i < $banners->count(); $i++)
                            @if (isset($banners[$i]))
                                @php
                                    $banner = $banners[$i];
                                    $link = null;

                                    if ($banner->product_id && $banner->product) {
                                        $link = route('products.show', $banner->product->slug);
                                    } elseif ($banner->link) {
                                        $link = $banner->link;
                                    }
                                @endphp
                                <div class="slide-item flex-none w-[75%] sm:w-[65%] md:w-[55%] lg:w-[35%]">
                                    <div class="w-full aspect-[18/9] bg-gray-200 rounded-xl overflow-hidden">
                                        @if ($link)
                                            <a href="{{ $link }}">
                                        @endif
                                        <img src="{{ asset('storage/' . $banner->image) }}"
                                            class="w-full h-full object-cover hover:scale-105 transition duration-300">

                                        @if ($link)
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endfor
                    @endif

                    {{-- main Homepage Carousel --}}
                    @foreach ($banners as $banner)
                        @php
                            $link = null;

                            if ($banner->product_id && $banner->product) {
                                $link = route('products.show', $banner->product->slug);
                            } elseif ($banner->link) {
                                $link = $banner->link;
                            }
                        @endphp
                        <div class="flex-none w-[75%] sm:w-[65%] md:w-[55%] lg:w-[35%]">
                            <div class="w-full aspect-[16/9] bg-gray-200 rounded-xl overflow-hidden">
                                @if ($link)
                                    <a href="{{ $link }}">
                                @endif
                                <img src="{{ asset('storage/' . $banner->image) }}"
                                    class="w-full h-full object-cover hover:scale-105 transition duration-300">
                                @if ($link)
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Front Clone --}}
                    @if ($banners->count() > 0)
                        @for ($i = 0; $i < $cloneCount; $i++)
                            @if (isset($banners[$i]))
                                @php
                                    $banner = $banners[$i];
                                    if ($banner->product_id && $banner->product) {
                                        $link = route('products.show', $banner->product->slug);
                                    } elseif ($banner->link) {
                                        $link = $banner->link;
                                    }
                                @endphp
                                <div class="slide-item flex-none w-[75%] sm:w-[65%] md:w-[55%] lg:w-[35%]">
                                    <div class="w-full aspect-[16/9] bg-gray-200 rounded-xl overflow-hidden">
                                        @if ($link)
                                            <a href="{{ $link }}">
                                        @endif
                                        <img src="{{ asset('storage/' . $banner->image) }}"
                                            class="w-full h-full object-cover hover:scale-105 transition duration-300">

                                        @if ($link)
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endfor
                    @endif
                </div>
                <button onclick=" prevSlide()"
                    class="absolute left-2 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full  bg-white opacity-50 hover:opacity-100  text-blue-600/30 hover:text-blue-600 hover:bg-white cursor-pointer">
                    <i class="bx bx-chevron-left text-4xl"></i>
                </button>
                <button onclick="nextSlide()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 h-12 w-12 flex items-center justify-center rounded-full bg-white opacity-50 hover:opacity-100  text-blue-600/30 hover:text-blue-600  hover:bg-white cursor-pointer">
                    <i class="bx bx-chevron-right text-4xl "></i>
                </button>

            </div>
        </div>

        @if ($categories->count() > 0)
            <div class="flex gap-2 overflow-x-auto mb-6 pb-2">
                @foreach ($categories as $category)
                    <button class="px-2 py-1 bg-white border rounded-full text-sm  ">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center text-center">
                <h1 class="font-semibold text-red-500 text-sm">Category not available...</h1>
            </div>
        @endif

        <!-- Products Grid / new products-->
        @if ($newProducts->count() > 0)
            <div class="flex items-center justify-between border-b border-slate-200">
                <p class="mb-2 text-2xl font-bold">
                    New items
                    <i class="bx bx-announcement relative right-1"></i>
                </p>

                @if ($newProducts->count() > 5)
                    <a href="{{ route('storefront.collections.show', ['slug' => 'new-items']) }}"
                        class="flex items-center text-sm hover:text-blue-600 shrink-0">See More
                        <i class="bx bx-chevron-right text-xl"></i>
                    </a>
                @endif
            </div>

            <div class="mb-5">
                {{-- if mobile horizontal scroll if desktop  grid --}}
                <div
                    class="flex gap-4 overflow-x-auto overscroll-x-contain pb-4 snap-x snap-mandatory sm:grid sm:grid-cols-3 sm:overflow-visible sm:gap-3 sm:pb-0 lg:grid-cols-5">

                    @foreach ($newProducts->take(5) as $product)
                        @php
                            $activeVariants = $product->variants->where('is_active', true);
                            $firstActiveVariant = $activeVariants->first();
                        @endphp

                        <a href="{{ route('products.show', $product->slug) }}"
                            class="w-[calc(50%-0.5rem)] shrink-0 snap-start sm:w-auto sm:shrink">
                            <article
                                class="bg-white rounded-xl p-2 shadow-sm hover:shadow-lg hover:-translate-y-1 transition duration-200 group relative h-full flex flex-col"
                                x-data="productPrice({
                                    isVariant: {{ $product->options->count() > 0 ? 'true' : 'false' }},
                                    prices: @js($activeVariants->pluck('price')->map(fn($price) => (float) $price)->values()),
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
                                            class="absolute top-1 left-1 z-10 font-mono rounded-lg text-sm border border-slate-200 shadow-lg px-2 py-1 text-white bg-slate-500">
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
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt=""
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                    @else
                                        <div class="flex w-full h-full items-center justify-center text-slate-400">
                                            <i class="bx bx-image text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <h4 class="mt-3 font-medium line-clamp-2 text-sm sm:text-base min-h-[2.5rem] sm:h-[3rem]">
                                    {{ $product->name }}
                                </h4>
                                <div class="flex flex-col mt-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <template x-if="hasDiscount">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                <span class="text-xs text-gray-400 line-through"
                                                    x-text="formatPrice(comparePrice)"></span>

                                                <span class="text-xs text-red-600 font-medium"
                                                    x-text="discountPercentage + '% OFF'"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="mt-1 min-h-[2rem] flex items-center justify-between">
                                        <span class="min-w-0 font-semibold text-blue-600" x-text="displayPrice"></span>

                                        <span class="text-xs shrink-0">
                                            {{ $product->sold ?? 0 }} x
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Products Grid all Products -->
        @if ($products->count() > 0)
            <p class="mb-2 text-2xl font-bold border-b border-slate-200 my-16">All</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 sm:gap-3 gap-4">
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
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt=""
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                @else
                                    <div class="flex w-full h-full items-center justify-center text-slate-400">
                                        <i class="bx bx-image text-2xl"></i>
                                    </div>
                                @endif

                            </div>

                            <h4 class="mt-3 font-medium line-clamp-2 text-sm sm:text-base min-h-[2.5rem] sm:h-[3rem]">
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
                                        {{ $product->sold ?? 0 }} x
                                    </span>
                                </div>
                            </div>
                        </article>
                    </a>
                @endforeach

            </div>
        @else
            <div class="flex flex-col items-center justify-center min-h-[30vh] text-center ">
                <h1 class="text-red-500 text-xl font-semibold">
                    Item not available...</h1>
                <p class="text-gray-400 text-sm mt-2">
                    Try another category or check back later.
                </p>
            </div>
        @endif

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
        document.addEventListener('DOMContentLoaded', function() {

            const carousel = document.getElementById('carousel');

            if (!carousel) return;

            const bannerCount = {{ $banners->count() }};

            if (bannerCount <= 1) return;

            const cloneCount = bannerCount;
            const slides = carousel.children;

            let currentIndex = bannerCount;
            let isAnimating = false;
            let autoSlide;

            function getSlideWidth() {
                const gap = 16;

                return slides[0].offsetWidth + gap;
            }

            function updateCarousel(animate = true) {
                carousel.style.transition = animate ?
                    'transform 0.5s ease-in-out' : 'none';

                carousel.style.transform = `translateX(-${currentIndex * getSlideWidth()}px)`;
            }
            updateCarousel(false);


            function nextSlide() {
                if (isAnimating) return;
                isAnimating = true;

                currentIndex++;
                updateCarousel(true);

            }

            function prevSlide() {
                if (isAnimating) return;
                isAnimating = true;

                currentIndex--;
                updateCarousel(true);
            }

            carousel.addEventListener('transitionend', function() {
                if (currentIndex >= cloneCount + bannerCount) {
                    currentIndex = cloneCount;
                    updateCarousel(false);
                } else if (currentIndex < cloneCount) {
                    currentIndex = cloneCount + bannerCount - 1;

                    updateCarousel(false);
                }
                isAnimating = false;
            });

            window.nextSlide = nextSlide;
            window.prevSlide = prevSlide;

            function startAutoSlide() {
                autoSlide = setInterval(() => {
                    nextSlide();
                }, 4000)
            }
            startAutoSlide();

            window.addEventListener('resize', function() {
                updateCarousel(false);
            })

        });
    </script>
@endsection
