@extends('layouts.layout_customer')
@section('title', 'My Account')

@section('ContentCustomer')
    <div class="w-full max-w-7xl mx-auto px-3 py-4 sm:px-6 sm:py-6">
        <div class="flex flex-col lg:flex-row gap-4 sm:gap-5">

            @include('customer.components.account_sidebar')

            <main class="min-w-0 flex-1 bg-white p-4 sm:p-5 lg:p-6 rounded-xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 sm:gap-5 mb-6 sm:mb-10">
                    <div class="relative shrink-0">
                        @if (auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                                alt="{{ auth()->user()->name }}"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border border-slate-200">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                                alt="{{ auth()->user()->name }}"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border border-slate-200">
                        @endif
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900
                            break-words">
                            {{ auth()->user()->name }}
                        </h2>

                        <p class="text-sm text-gray-500 break-all mt-1">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div class="min-w-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Name
                        </label>

                        <input type="text" value="{{ auth()->user()->name }}" readonly
                            class="w-full min-w-0 border border-gray-200 rounded-lg px-3 py-3 bg-gray-50 text-sm text-gray-700 outline-none">
                    </div>

                    <div class="min-w-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>

                        <input type="text" value="{{ auth()->user()->username }}" readonly
                            class="w-full min-w-0 border border-gray-200 rounded-lg px-3 py-3 bg-gray-50 text-sm text-gray-700 outline-none">
                    </div>

                    <div class="min-w-0 md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>

                        <input type="email" value="{{ auth()->user()->email }}" readonly
                            class="w-full min-w-0 border border-gray-200 rounded-lg px-3 py-3 bg-gray-50 text-sm text-gray-700 outline-none">
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
