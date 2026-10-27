@extends('layouts.layout_customer')
@section('title', 'Manage Address')

@section('ContentCustomer')
    <div class="flex flex-col lg:flex-row gap-5 max-w-7xl mx-auto p-4 sm:p-6">
        @include('customer.components.account_sidebar')
        <main class="flex-1 bg-white p-5 rounded-xl shadow">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Manage Account</h1>
                <p class="text-sm text-gray-500">Update Your profile information</p>
            </div>
            {{-- Profile --}}
            <div class="flex items-center gap-5 mb-10">
                <form id="photoForm" action="{{ route('customer.profile.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="relative w-24 h-24">
                        @if (auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                                class="w-24 h-24 rounded-full object-cover border ">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}"
                                class="w-24 h-24 rounded-full border">
                        @endif
                        {{-- Profile edit --}}
                        <label for="profile_photo"
                            class="absolute bottom-0 right-0 bg-white p-2 rounded-full shadow cursor-pointer hover:bg-gray-100">
                            <img src="{{ asset('icon/4682658.png') }}" width="15" height="15" alt="">
                        </label>
                    </div>
                    {{-- Upload photo --}}

                    <input type="file" name="profile_photo" id="profile_photo" class="hidden"
                        onchange="document.getElementById('photoForm').submit()">
                </form>
            </div>
            <form action="{{ route('customer.manage.account.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-2  text-sm font-medium">name : </label>
                    <input
                        class="w-full border border-gray-200 rounded p-2 focus:ring-1 focus:ring-blue-300 focus:outline-none"
                        type="text" value="{{ auth()->user()->name }}" name="name">
                </div>
                <div>
                    <label class="block mb-2  text-sm font-medium">Username : </label>
                    <input type="text" name="username"
                        class="w-full p-2 rounded  border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-300"
                        value="{{ auth()->user()->username }}">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium">Phone :</label>
                    <input type="tel"
                        class="w-full border border-gray-200 p-2 rounded focus:ring-1 focus:outline-none focus:ring-blue-300"
                        value="{{ auth()->user()->phone }}" name="phone">
                </div>

                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg">Save
                        Changes</button>
                </div>
            </form>
        </main>
    </div>
@endsection
