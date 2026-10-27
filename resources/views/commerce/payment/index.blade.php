@extends('layouts.layout_customer')

@section('ContentCustomer')
    <div class="max-w-7xl mx-auto p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-5">
                    <a href="{{ route('checkout') }}">
                        <i class="bx bx-caret-big-left text-blue-600 relative top-[6px] text-3xl"></i>
                    </a>
                    Shipping Details
                </h1>
                <div class="bg-white p-4 rounded-xl mb-2 shadow">
                    {{--  add when implemented gateway payment --}}
                </div>

                <h1 class="text-2xl font-bold text-gray-500 mb-2">Order Details</h1>
                @foreach ($cartItems as $item)
                    @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;

                        // Get the image from the selected variant's option values.
                        $variantImage = $variant?->optionValues?->first(fn($optionValue) => $optionValue->image?->path)
                            ?->image?->path;

                        // Use the product image as a fallback.
                        $imagePath = $variantImage ?? $product?->images?->first()?->path;

                        $lineTotal = $variant ? $variant->price * $item->quantity : 0;
                    @endphp

                    <div class="mb-4 overflow-hidden rounded-lg border border-gray-200 shadow">
                        <div class="bg-blue-200 p-2">
                            Estimated arrival:
                            {{ date('F') }} - {{ date('F', strtotime('+1 month')) }}
                            <span class="text-xs">(dummy)</span>
                        </div>

                        <div class="bg-white p-3">
                            <div class="flex items-start justify-between gap-3">

                                <div class="flex min-w-0 flex-1 gap-3">

                                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                        @if ($imagePath)
                                            <img src="{{ asset('storage/' . $imagePath) }}"
                                                alt="{{ $product?->name ?? 'Product image' }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                                                <i class="bx bx-image text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="font-medium">
                                            {{ $product?->name ?? 'Product unavailable' }}
                                        </p>

                                        <div class="mt-1 flex flex-wrap items-center gap-2"
                                        {{-- Regarding the `@if ($item->cartStatus === 'sold_out')` section—this is actually just a scenario for handling unusual conditions --}}
                                            @if ($item->cartStatus === 'sold_out') <span class="text-xs font-semibold text-red-600">
                                                    Sold Out
                                                </span>
                                            @elseif ($item->cartStatus === 'unavailable')
                                                <span class="text-xs font-semibold text-red-600">
                                                      Unavailable
                                                </span>

                                                <span class="text-xs text-gray-500">
                                                    {{ $item->cartMessage }}
                                                </span>
                                            @elseif ($product?->availability === 'preorder')
                                                <span class="text-xs font-semibold text-blue-600">
                                                    Pre-Order
                                                </span>
                                            @elseif ($product?->availability === 'ready')
                                                <span class="text-xs font-semibold text-green-600">
                                                    Ready Stock
                                                </span> @endif
                                            </div>

                                            @if ($variant && $variant->optionValues->isNotEmpty())
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $variant->optionValues->pluck('value')->join(' / ') }}
                                                </p>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        @if ($variant)
                                            <p class="text-xs mt-1 line-through text-end">
                                                {{ Number_format($variant->compare_price, 0, ',', '.') }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600">
                                                {{ $item->quantity }} × Rp
                                                {{ number_format($variant->price, 0, ',', '.') }}
                                            </p>
                                        @endif
                                        @if ($variant)
                                            <p class="whitespace-nowrap text-lg font-bold text-blue-600">
                                                Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                @endforeach
            </div>
            <div class="lg:w-[400px]  w-full ">
                <div class="lg:sticky lg:top-40">
                    <div class="p-3 bg-white rounded-lg shadow">
                        <div class="flex flex-row gap-2">
                            <input type="text" class="border border-slate-200 w-full rounded-lg outline-none py-2 px-4"
                                placeholder="Discount or gift card">
                            <button type="button"
                                class="border border-slate-200 rounded-lg p-2 bg-gray-100 text-sm">Apply</button>
                        </div>
                    </div>

                    <div class="mt-5 bg-white p-4 rounded shadow">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Sub-Total</span>
                                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Shipping cost</span>
                                <span>Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                                <span class="font-semibold">Total</span>
                                <span id="total-price" class="font-bold text-xl text-blue-600">
                                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Pay button will be connected after the order route is implemented. --}}
                        <button type="button" disabled
                            class="mt-4 bg-slate-300 text-white px-6 py-2 rounded cursor-not-allowed w-full md:flex hidden justify-center items-center">
                            Pay
                        </button>
                    </div>
                </div>
                {{-- MOBILE STICKY Continue to payment --}}
                <div
                    class="fixed bottom-0 left-0 right-0 bg-white border-t shadow p-4 flex justify-between items-center md:hidden z-50 ">

                    <div class="flex flex-col">
                        <span>Sub-Total :</span>
                        <span class="text-blue-600 text-lg">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>

                    <button type="button" disabled class="bg-slate-300 text-white px-4 py-2 rounded cursor-pointer">
                        Pay
                    </button>

                </div>
            </div>
        </div>
    </div>
@endsection
