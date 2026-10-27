@extends('layouts.layout_customer')

@section('title', 'Collections')

@section('ContentCustomer')
    <main class="bg-white min-h-screen">

        <div class="shadow px-4 py-2 lg:py-5 border-b border-b-slate-200 bg-white">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900">
                    Collections
                </h1>
            </div>
        </div>
        <section class="max-w-7xl mx-auto px-6 py-8">

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($collections as $collection)
                    <a href="{{ route('storefront.collections.show', ['slug' => $collection->slug]) }}" class="group block">
                        @if ($collection->thumbnail)
                            <img src="{{ asset('storage/' . $collection->thumbnail) }}" alt="{{ $collection->name }}"
                                class="w-full aspect-square object-cover rounded-lg transition-transform duration-300 group-hover:scale-[1.02]">
                        @else
                            <div class="w-full aspect-square rounded-lg bg-gray-100 flex items-center justify-center">
                                <span class="text-gray-400 text-sm">No image</span>
                            </div>
                        @endif

                        <h2 class="mt-3 font-semibold text-gray-900 group-hover:text-blue-600">
                            {{ $collection->name }}
                        </h2>
                    </a>
                @empty
                @endforelse
            </div>

            <div class="mt-8">
                {{ $collections->links() }}
            </div>
        </section>
    </main>
@endsection
