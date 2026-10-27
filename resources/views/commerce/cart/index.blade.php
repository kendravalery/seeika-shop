@extends('layouts.layout_customer')
@section('ContentCustomer')
    <div x-data="{
        show: @js(session()->has('error') || session()->has('success')),
        type: @js(session()->has('error') ? 'error' : 'success'),
        message: @js(session('error') ?? (session('success') ?? '')),
    
        notify(event) {
            this.type = event.detail.type ?? 'error';
            this.message = event.detail.message ?? '';
            this.show = true;
        }
    }" x-show="show" x-cloak @cart-notification.window="notify($event)"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
        @click.outside="show = false" class="fixed top-5 right-5 z-50 w-[calc(100%-2.5rem)] max-w-sm" role="status">
        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-lg">
            <div class="flex-1">
                <p class="text-sm font-semibold" :class="type === 'error' ? 'text-red-600' : 'text-emerald-600'"
                    x-text="type === 'error' ? 'Something went wrong.' : 'Success'"></p>

                <p class="mt-1 text-sm text-slate-600" x-text="message"></p>
            </div>

            <button type="button" @click="show = false" class="text-slate-400 transition hover:text-slate-700"
                aria-label="Dismiss notification">
                &times;
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-5">
                    <a href="{{ route('home') }}">
                        <i class="bx bx-caret-big-left text-blue-600 relative top-[6px] text-3xl"></i>
                    </a>
                    My Cart
                </h1>

                @if ($cartItems->isEmpty())
                    <div class="bg-white p-6 rounded-xl shadow text-gray-500 text-center">
                        The cart is still empty...
                    </div>
                @else
                    <label class="flex items-center gap-2 mb-4 cursor-pointer">
                        <input type="checkbox" id="select-all" class="w-5 h-5 accent-blue-500 cursor-pointer">
                        <span class="text-sm">Select All</span>
                    </label>

                    <div class="space-y-3">

                        @foreach ($cartItems as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;

                                $displayQuantity = in_array($item->cartStatus, ['sold_out', 'preorder_closed'], true)
                                    ? 0
                                    : $item->quantity;

                                $canIncrease =
                                    $item->cartStatus === 'available' &&
                                    $variant &&
                                    $product &&
                                    ($item->maxAllowedQuantity === null || $item->quantity < $item->maxAllowedQuantity);

                                $lineTotal =
                                    $variant && $item->cartStatus === 'available'
                                        ? $variant->price * $item->quantity
                                        : 0;
                                $comparePrice = null;

                                if ($variant && $product && $item->cartStatus === 'available') {
                                    $candidateComparePrice = $variant->compare_price ?? $product->compare_price;

                                    if (
                                        $candidateComparePrice !== null &&
                                        is_numeric($candidateComparePrice) &&
                                        (float) $candidateComparePrice > (float) $variant->price
                                    ) {
                                        $comparePrice = (float) $candidateComparePrice;
                                    }
                                }

                                $variantImage = $variant?->optionValues->first(fn($value) => $value->image);
                                $productImage = $product?->images->first();
                                $imagePath = $variantImage?->image?->path ?? $productImage?->path;
                            @endphp

                            <div class="bg-white p-4 rounded-xl shadow">
                                <div class="flex gap-3">

                                    <label class="cursor-pointer mt-1">
                                        <input type="checkbox"
                                            class="item-checkbox w-5 h-5 accent-blue-500 cursor-pointer mt-1 disabled:cursor-not-allowed"
                                            data-id="{{ $item->id }}"
                                            data-price="{{ $variant && $item->cartStatus === 'available' ? $variant->price * $item->quantity : 0 }}"
                                            {{ $item->cartStatus === 'available' && $item->is_selected ? 'checked' : '' }}
                                            @disabled($item->cartStatus !== 'available')>
                                    </label>

                                    <div class="flex-1 min-w-0">

                                        <div class="flex gap-3 items-start">

                                            <div class="relative w-16 h-16 shrink-0 overflow-hidden rounded bg-slate-100">
                                                @if ($imagePath)
                                                    <img src="{{ asset('storage/' . $imagePath) }}"
                                                        alt="{{ $product?->name ?? 'Product image' }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div
                                                        class="flex w-full h-full items-center justify-center text-slate-400">
                                                        <i class="bx bx-image text-2xl"></i>
                                                    </div>
                                                @endif

                                                @if ($item->cartStatus !== 'available')
                                                    <div class="absolute inset-0 bg-black/25 pointer-events-none">
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">

                                                @if ($product)
                                                    <a href="{{ route('products.show', $product->slug) }}">
                                                        <h2
                                                            class="font-semibold text-sm md:text-base hover:text-blue-500 hover:underline">
                                                            {{ $product->name }}
                                                        </h2>
                                                    </a>
                                                @else
                                                    <h2 class="font-semibold text-sm text-gray-500">
                                                        Product unavailable
                                                    </h2>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    @if ($item->cartStatus === 'sold_out')
                                                        <span class="text-xs font-semibold text-red-600">
                                                            Sold Out
                                                        </span>
                                                    @elseif ($item->cartStatus === 'preorder_closed')
                                                        <span class="text-xs font-semibold text-red-600">
                                                            Pre-Order Closed
                                                        </span>

                                                        @if ($item->cartMessage)
                                                            <span class="text-xs text-gray-500">
                                                                {{ $item->cartMessage }}
                                                            </span>
                                                        @endif
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
                                                        </span>
                                                    @endif
                                                </div>

                                                @if ($product?->availability === 'preorder' && filled($product?->preorder_release_label))
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ $product->preorder_release_label }}
                                                    </p>
                                                @endif

                                                @if ($variant && $variant->optionValues->isNotEmpty())
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $variant->optionValues->pluck('value')->join(' / ') }}
                                                    </p>
                                                @endif

                                                @if ($variant)
                                                    <div class="flex flex-wrap items-center gap-x-2 mt-1 text-xs">
                                                        <span class="text-gray-500">
                                                            {{ $displayQuantity }} x
                                                        </span>

                                                        <span class="font-medium text-slate-700">
                                                            Rp {{ number_format($variant->price, 0, ',', '.') }}
                                                        </span>

                                                        @if ($comparePrice !== null)
                                                            <span class="text-slate-400 line-through">
                                                                Rp {{ number_format($comparePrice, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between gap-2 mt-3 pt-3 border-t border-t-slate-200">

                                            @if ($variant)
                                                <p class="font-bold text-lg text-blue-600 whitespace-nowrap">
                                                    Rp
                                                    {{ number_format($lineTotal, 0, ',', '.') }}
                                                </p>
                                            @endif

                                            <div class="flex flex-row gap-2 shrink-0">
                                                <div x-data="{ loading: false }"
                                                    class="inline-flex items-center overflow-hidden rounded-full border border-slate-300 bg-white">


                                                    <form action="{{ route('cart.decrease', $item->id) }}" method="POST"
                                                        @submit="loading = true">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit" @disabled($item->cartStatus !== 'available' || $item->quantity <= 1)
                                                            :disabled="loading ||
                                                                {{ $item->cartStatus !== 'available' || $item->quantity <= 1 ? 'true' : 'false' }}"
                                                            aria-label="Decrease quantity"
                                                            class="flex h-9 w-9 items-center justify-center text-lg font-medium text-slate-700 transition hover:bg-slate-100 disabled:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed">
                                                            −
                                                        </button>
                                                    </form>

                                                    <span
                                                        class="flex h-9 w-9 items-center justify-center border-x border-slate-200 px-2 text-sm font-semibold tabular-nums">
                                                        {{ $displayQuantity }}
                                                    </span>

                                                    <form action="{{ route('cart.increase', $item->id) }}" method="POST"
                                                        @submit="loading = true">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit" @disabled(!$canIncrease)
                                                            :disabled="loading || {{ $canIncrease ? 'false' : 'true' }}"
                                                            aria-label="Increase quantity"
                                                            class="flex h-9 w-9 items-center justify-center text-lg font-medium text-slate-700 transition hover:bg-slate-100 disabled:bg-slate-50 disabled:text-slate-300 disabled:cursor-not-allowed">
                                                            +
                                                        </button>
                                                    </form>
                                                </div>

                                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-red-50 hover:text-red-600 cursor-pointer">
                                                        <i class="bx bx-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            {{-- CART SUMMARY --}}
            <div class="lg:w-[400px] w-full">
                <div class="lg:sticky lg:top-50">

                    <div class="p-3 bg-white rounded-lg shadow">
                        <div class="flex flex-row gap-2">
                            <input type="text" class="border border-slate-200 w-full rounded-lg outline-none py-2 px-4"
                                placeholder="Discount or gift card"> <button
                                class="border border-slate-200 rounded-lg p-2 bg-gray-100 text-sm">Apply</button>
                        </div>
                    </div>

                    {{-- Total and desktop checkout --}}
                    <div class="hidden md:block mt-5 bg-white p-3 rounded-lg shadow ">
                        <div class="mb-4">
                            <span class="font-bold">Total</span>

                            <span id="total-price" class="font-bold text-xl text-blue-600">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </span>
                        </div>

                        <a href="{{ route('checkout') }}"
                            class="checkout-link inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg cursor-pointer w-full">
                            Checkout
                        </a>
                    </div>
                </div>

                {{-- Mobile sticky checkout --}}
                <div
                    class="fixed bottom-0 left-0 right-0 bg-white border-t shadow p-4 flex justify-between items-center md:hidden z-50">
                    <div class="flex flex-col text-xl">
                        <span>Total :</span>
                        <span id="total-price-mobile" class="font-bold text-blue-600 text-xl"></span>
                    </div>
                    <a href="{{ route('checkout') }}"
                        class="checkout-link inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded cursor-pointer text-xl">
                        Checkout
                    </a>
                </div>
            </div>

        </div>
    </div>
    <script>
        const checkboxes = [...document.querySelectorAll('.item-checkbox')];
        const selectableCheckboxes = checkboxes.filter(cb => !cb.disabled);
        const selectAll = document.getElementById('select-all');
        const checkoutLinks = [...document.querySelectorAll('.checkout-link')];

        let selectionRequestPending = false;


        function setSelectionPending(pending) {
            selectionRequestPending = pending;

            // Prevent overlapping selection requests.
            selectableCheckboxes.forEach(cb => {
                cb.disabled = pending;
            });

            if (selectAll) {
                selectAll.disabled =
                    pending || selectableCheckboxes.length === 0;
            }
        }

        function showCartNotification(message, type = 'error') {
            window.dispatchEvent(
                new CustomEvent('cart-notification', {
                    detail: {
                        type,
                        message
                    }
                })
            );
        }

        function updateTotal() {
            let total = 0;

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    total += Number(cb.dataset.price) || 0;
                }
            });

            const formatted = 'Rp ' + total.toLocaleString('id-ID');

            const desktop = document.getElementById('total-price');
            const mobile = document.getElementById('total-price-mobile');

            if (desktop) desktop.innerText = formatted;
            if (mobile) mobile.innerText = formatted;
        }

        function updateSelectAll() {
            if (!selectAll) return;

            const checkedCount = selectableCheckboxes.filter(
                cb => cb.checked
            ).length;

            selectAll.checked =
                selectableCheckboxes.length > 0 &&
                checkedCount === selectableCheckboxes.length;

            selectAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < selectableCheckboxes.length;

            selectAll.disabled =
                selectionRequestPending ||
                selectableCheckboxes.length === 0;
        }


        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.disabled) return;

                setSelectionPending(true);
                updateTotal();

                fetch(`/cart/select/${this.dataset.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            is_selected: this.checked
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to update selection.');
                        }
                    })
                    .then(() => {
                        setSelectionPending(false);
                        updateTotal();
                        updateSelectAll();
                    })
                    .catch(() => window.location.reload());
            });
        });

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isSelected = this.checked;

                setSelectionPending(true);

                fetch('/cart/select-all', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            is_selected: isSelected
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to update selection.');
                        }
                    })
                    .then(() => {
                        selectableCheckboxes.forEach(cb => {
                            cb.checked = isSelected;
                        });

                        setSelectionPending(false);
                        updateTotal();
                        updateSelectAll();
                    })
                    .catch(() => window.location.reload());
            });
        }
        checkoutLinks.forEach(link => {
            link.addEventListener('click', event => {
                if (selectionRequestPending) {
                    event.preventDefault();
                    return;
                }

                const hasSelectedItems = selectableCheckboxes.some(
                    cb => cb.checked
                );

                if (!hasSelectedItems) {
                    event.preventDefault();

                    showCartNotification(
                        'Please select at least one product before checkout.'
                    );
                }
            });
        });

        // Initialize the cart summary and checkout state.
        updateTotal();
        updateSelectAll();
        setSelectionPending(false);
    </script>
@endsection
