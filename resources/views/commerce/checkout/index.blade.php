@extends('layouts.layout_customer')

@section('ContentCustomer')
    @php
        $shippingPrices = [
            'jne_reg' => 18000,
            'jnt' => 20000,
            'sicepat' => 19000,
        ];

        $initialShipping = old('shipping', session('shipping', ''));
        $initialShippingCost = $shippingPrices[$initialShipping] ?? 0;

        $shippingNames = [
            'jne_reg' => 'JNE Reguler',
            'jnt' => 'J&T Express',
            'sicepat' => 'SiCepat REG',
        ];

        $initialShippingName = $shippingNames[$initialShipping] ?? 'Select Method';
    @endphp
    <div x-data="{
        openModal: false,
        openShippingMethod: false,
        showShippingError: @js($errors->has('shipping')),
        shipping: @js($initialShipping),
        shippingCost: @js($initialShippingCost),
        subtotal: @js((float) $total),

        get grandTotal() {
            return this.subtotal + this.shippingCost;
        },

        formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },
        shippingName: @js($initialShippingName),

        deleteOpen: false,
        deleteName: '',
        deleteAction: '',

        openDelete(name, action) {
            this.deleteName = name;
            this.deleteAction = action;
            this.deleteOpen = true;
        },

        closeDelete() {
            this.deleteOpen = false;
        }
    }">

        <form action="{{ route('checkout.continue') }}" method="POST"
            @submit="
        if (!$el.querySelector('input[name=shipping]:checked')) {
            $event.preventDefault();
            showShippingError = true;
        }
    ">
            @csrf
            <div class="max-w-7xl mx-auto p-4 sm:p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-5">
                            <a href="{{ route('cart.index') }}">
                                <i class="bx bx-caret-big-left text-blue-600 relative top-[6px] text-3xl"></i>
                            </a>
                            Shipping Details
                        </h1>
                        <div class="bg-white p-4 rounded-xl mb-2 shadow">
                            <div class="flex items-center justify-between border-b  gap-10 p-2 border-gray-300">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-600">
                                        shipping address :
                                    </h2>
                                </div>
                                <div>
                                    <button type="button" @click="openModal = true"
                                        class="text-blue-600 hover:text-blue-700 cursor-pointer ">
                                        Changes Address
                                    </button>
                                    {{-- use alpine --}}
                                </div>
                            </div>
                            {{-- re-add and set as the primary address --}}
                            <div class="flex justify-between mb-2 p-2 border-b border-gray-300">
                                @if ($defaultAddress)
                                    <div class="text-sm">
                                        <p class="font-semibold">
                                            {{ $defaultAddress->receiver_name }}
                                            <span class="text-gray-500">
                                                | {{ $defaultAddress->phone }}
                                            </span>
                                        </p>

                                        <p class="mt-2">
                                            {{ $defaultAddress->full_address }}
                                        </p>

                                        <p class="mt-1 text-gray-500">
                                            {{ optional(\Laravolt\Indonesia\Models\Village::where('code', $defaultAddress->village)->first())->name }}
                                            {{ optional(\Laravolt\Indonesia\Models\District::where('code', $defaultAddress->district)->first())->name }},
                                            {{ optional(\Laravolt\Indonesia\Models\City::where('code', $defaultAddress->city)->first())->name }},
                                            {{ optional(\Laravolt\Indonesia\Models\Province::where('code', $defaultAddress->province)->first())->name }},
                                            {{ $defaultAddress->postal_code }}
                                        </p>
                                    </div>
                                @else
                                    <p class="text-sm text-red-600">You haven't selected a shipping address yet. Please add
                                        an address before checkout.</p>
                                @endif
                            </div>

                            <label for="checkout-note" class="mb-2 block text-sm">
                                Notes:
                            </label>

                            <textarea id="checkout-note" name="note"
                                class="w-full rounded-lg border border-gray-300 p-2 outline-none focus:border-slate-400" rows="3"
                                placeholder="Note for seeika (optional)">{{ old('note', session('checkout_note', '')) }}</textarea>
                            {{-- <input type="textarea" class="border border-gray-300 p-2 "> --}}

                            <p class="text-lg font-bold text-gray-600">
                                Shipping method:
                            </p>
                            <div @click="openShippingMethod = true"
                                class="p-2 border rounded-xl cursor-pointer border-gray-300">
                                <p x-text="shippingName" class="hover:translate-x-2 transition duration-300">
                                    Select Method
                                </p>
                            </div>
                            {{-- alert --}}
                            <div x-cloak x-show="showShippingError" x-transition role="alert"
                                class="mt-3 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                                <i class="bx bx-error-circle mt-0.5 text-xl"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold">
                                        Shipping method required
                                    </p>
                                    <p class="mt-1 text-sm">
                                        Please select a shipping method before continuing.
                                    </p>
                                </div>
                                <button type="button" @click="showShippingError = false"
                                    class="cursor-pointer text-red-500 hover:text-red-700" aria-label="Close alert">
                                    <i class="bx bx-x text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <h1 class="text-2xl font-bold text-gray-500 mb-2">
                            Order Details
                        </h1>

                        @foreach ($cartItems as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;

                                // Get the image from the selected variant's option values.
                                $variantImage = $variant?->optionValues?->first(
                                    fn($optionValue) => $optionValue->image?->path,
                                )?->image?->path;

                                // Use the product image as a fallback.
                                $imagePath = $variantImage ?? $product?->images?->first()?->path;

                                $lineTotal = $variant ? $variant->price * $item->quantity : 0;
                            @endphp

                            <div class="mb-4 overflow-hidden rounded-lg border border-gray-200 shadow">
                                @if ($item->productVariant->product->preorder_release_label)
                                    <div class="bg-blue-200 p-2">

                                        {{ $item->productVariant->product->preorder_release_label }}
                                    </div>
                                @else
                                    <div class="bg-blue-200 p-2">
                                        Estimated arrival:
                                        {{ date('F') }} - {{ date('F', strtotime('+1 month')) }}
                                        <span class="text-xs">(dummy)</span>
                                    </div>
                                @endif

                                <div class="bg-white p-3">
                                    <div class="flex items-start justify-between gap-3">

                                        <div class="flex min-w-0 flex-1 gap-3">

                                            <div
                                                class="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                                @if ($imagePath)
                                                    <img src="{{ asset('storage/' . $imagePath) }}"
                                                        alt="{{ $product?->name ?? 'Product image' }}"
                                                        class="h-full w-full object-cover">
                                                @else
                                                    <div
                                                        class="flex h-full w-full items-center justify-center text-slate-400">
                                                        <i class="bx bx-image text-2xl"></i>
                                                    </div>
                                                @endif

                                                @if ($item->cartStatus !== 'available')
                                                    <div class="pointer-events-none absolute inset-0 bg-black/25"></div>
                                                @endif
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <p class="font-medium">
                                                    {{ $product?->name ?? 'Product unavailable' }}
                                                </p>

                                                <div class="mt-1 flex flex-wrap items-center gap-2">

                                                    @if ($item->cartStatus === 'sold_out')
                                                        <span class="text-xs font-semibold text-red-600">
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
                                                        </span>
                                                    @endif

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
                                    <input type="text"
                                        class="border border-slate-200 w-full rounded-lg outline-none py-2 px-4"
                                        placeholder="Discount or gift card">
                                    <button type="button"
                                        class="border border-slate-200 rounded-lg p-2 bg-gray-100 text-sm">Apply</button>
                                </div>
                            </div>
                            {{-- TOTAL --}}
                            <div class="mt-5 bg-white p-4 rounded-lg shadow">
                                <div class="space-y-3 mb-4">
                                    <div class="flex justify-between">
                                        <span>Sub-Total</span>
                                        <span x-text="formatRupiah(subtotal)">
                                            Rp {{ number_format($total, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span>Shipping cost</span>
                                        <span x-text="shipping ? formatRupiah(shippingCost) : 'Select Method'">
                                            {{ $initialShipping ? 'Rp ' . number_format($initialShippingCost, 0, ',', '.') : 'Select Method' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center border-t border-slate-200 pt-3">
                                        <span class="font-semibold">Total</span>

                                        <span id="total-price" class="font-bold text-xl text-blue-600"
                                            x-text="formatRupiah(grandTotal)">
                                            Rp {{ number_format($total + $initialShippingCost, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- DESKTOP Continue to payment --}}
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg items-center justify-center cursor-pointer w-full md:flex hidden">
                                    Continue to payment
                                </button>
                            </div>
                        </div>
                        {{-- MOBILE STICKY Continue to payment --}}
                        <div
                            class="fixed bottom-0 left-0 right-0 bg-white border-t shadow p-4 flex justify-between items-center md:hidden z-50 ">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500">Total</span>
                                <span class="font-bold text-blue-600 text-lg" x-text="formatRupiah(grandTotal)">
                                    Rp {{ number_format($total + $initialShippingCost, 0, ',', '.') }}
                                </span>
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                                Continue to payment
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SHIPPING METHOD MODAL --}}
                <div x-cloak x-show="openShippingMethod"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div @click.away="openShippingMethod = false" x-transition
                        class="w-full max-w-md rounded-xl bg-white shadow-lg">
                        <div class="border-b">
                            <div class="flex items-center justify-between p-3">
                                <div class="text-lg">Delivery</div>
                                <button type="button" @click="openShippingMethod = false" class="cursor-pointer"
                                    aria-label="Close delivery options">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3 p-4">
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border p-3">
                                <div>
                                    <p class="font-semibold">JNE Reguler</p>
                                    <p class="text-sm text-gray-500">Estimated 2–4 days</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">Rp 18.000</p>

                                    <input type="radio" name="shipping" id="jne_reg" value="jne_reg"
                                        {{ old('shipping', session('shipping')) == 'jne_reg' ? 'checked' : '' }}
                                        @change="shipping = 'jne_reg';
                                        shippingName = 'JNE Reguler';
                                        shippingCost = 18000;
                                        showShippingError = false;
                                        openShippingMethod = false;">
                                </div>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border p-3">
                                <div>
                                    <p class="font-semibold">J&T Express</p>
                                    <p class="text-sm text-gray-500">Estimated 2–4 days</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">Rp 20.000</p>
                                    <input type="radio" name="shipping" id="jnt" value="jnt"
                                        {{ old('shipping', session('shipping')) == 'jnt' ? 'checked' : '' }}
                                        @change="shipping = 'jnt';
                                        shippingName = 'J&T Express';
                                        shippingCost = 20000;
                                        showShippingError = false;
                                        openShippingMethod = false;">
                                </div>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border p-3">
                                <div>
                                    <p class="font-semibold">SiCepat REG</p>
                                    <p class="text-sm text-gray-500">Estimated 2–4 days</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">Rp 19.000</p>
                                    <input type="radio" name="shipping" id="sicepat" value="sicepat"
                                        {{ old('shipping', session('shipping')) == 'sicepat' ? 'checked' : '' }}
                                        @change="shipping = 'sicepat';
                                        shippingName = 'SiCepat REG';
                                        shippingCost = 19000; showShippingError = false;
                                        openShippingMethod = false;">
                                </div>
                            </label>

                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- modal Shipping address --}}
        <div x-cloak x-show="openModal" x-transition.opacity @click.self="openModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

            <div x-show="openModal" x-transition.scale
                class="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-lg">

                <div class="flex shrink-0 items-center justify-between border-b border-slate-200 p-4">
                    <div class="text-lg font-medium">
                        Shipping address
                    </div>
                    <button type="button" @click="openModal = false"
                        class="cursor-pointer text-xl text-slate-500 hover:text-slate-800" aria-label="Close modal">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                {{-- Address list: this is the scrollable section --}}
                <div class="min-h-0 flex-1 space-y-2 overflow-y-auto p-4">
                    @forelse ($addresses as $address)
                        <div class="flex flex-col items-start rounded-lg border border-gray-300 p-3">
                            <div class="font-semibold">
                                {{ $address->receiver_name }} |
                                <span class="text-gray-500">
                                    {{ $address->phone }}
                                </span>
                                @if ($address->is_default)
                                    <span class="ml-2 rounded-lg bg-green-100 p-1 text-xs text-green-700">
                                        Main address
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 text-gray-700">
                                {{ $address->full_address }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ optional(\Laravolt\Indonesia\Models\Village::where('code', $address->village)->first())->name }}
                                {{ optional(\Laravolt\Indonesia\Models\District::where('code', $address->district)->first())->name }},
                                {{ optional(\Laravolt\Indonesia\Models\City::where('code', $address->city)->first())->name }},
                                {{ optional(\Laravolt\Indonesia\Models\Province::where('code', $address->province)->first())->name }},
                                {{ $address->postal_code }}
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <button type="button" @click="openDelete($el.dataset.name, $el.dataset.action)"
                                    data-name="{{ $address->receiver_name }}"
                                    data-action="{{ route('customer.Address.delete', $address->id) }}"
                                    class="cursor-pointer text-red-700 hover:text-red-800" aria-label="Delete address">
                                    <i class="bx bx-trash text-sm"></i>
                                </button>
                                <a href="{{ route('customer.address') }}" class="text-slate-600 hover:text-slate-900"
                                    title="Edit address">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @if (!$address->is_default)
                                    <form action="{{ route('customer.Address.default', $address->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-700">
                                            Set as primary address
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-500">
                            You haven't added any shipping addresses yet.
                        </p>
                    @endforelse
                </div>
                <div class="shrink-0 border-t border-slate-200 bg-white p-4">
                    <a href="{{ route('customer.address') }}"
                        class="inline-block rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                        + Add address
                    </a>
                </div>
            </div>
        </div>

        {{-- Delete Address Confirmation Modal --}}
        <div x-show="deleteOpen" x-cloak x-transition.opacity @click.self="closeDelete()"
            @keydown.escape.window="closeDelete()"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
            <div x-show="deleteOpen" x-transition.scale @click.outside="closeDelete()" role="dialog" aria-modal="true"
                aria-labelledby="delete-address-title" class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="px-6 py-5 text-center">
                    <h2 id="delete-address-title" class="text-lg font-semibold text-slate-900">
                        Delete Address?
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Are you sure you want to delete
                        <span class="font-semibold text-red-500" x-text="deleteName"></span>?
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        This action cannot be undone.
                    </p>
                </div>
                <div class="flex gap-3 border-t border-slate-200 px-6 py-4">
                    <button type="button" @click="closeDelete()"
                        class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>

                    <form method="POST" :action="deleteAction" class="flex-1">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="w-full cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
