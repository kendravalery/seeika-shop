@extends('layouts.layout_admin')

@section('ContentAdmin')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-3 text-slate-500 mb-4">
            {{-- Breadcrumb --}}
            <p>Categories</p>
        </div>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Category List
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Product categories with hierarchical structure.
                </p>
            </div>
            <a href="{{ route('admin.categories.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2  font-medium text-white transition hover:bg-blue-700">
                <i class="bx bx-plus text-sm"></i>
                Add Category
            </a>
        </div>

        @if ($categories->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <h3 class="mt-4 text-lg font-semibold text-slate-900">
                    No Parent categories yet
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Start by adding your Parent category with seeders
                </p>
            </div>
        @else
            {{-- Category List --}}
            <div class="divide-y divide-slate-200">

                @foreach ($categories as $category)
                    <div x-cloak x-data="{ open: false }">
                        {{-- Parent --}}
                        <div class="group flex items-center justify-between px-5 py-4 transition hover:bg-slate-50">
                            <div class="flex items-center gap-3">

                                @if ($category->children->isNotEmpty())
                                    <button type="button" @click="open = !open"
                                        class="flex h-7 w-7 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                                        <i class="bx bx-chevron-right text-3xl font-semibold transition-transform duration-200"
                                            :class="{ 'rotate-90': open }"></i>
                                    </button>
                                @else
                                    <div class="h-7 w-7"></div>
                                @endif

                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-900">
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        /{{ $category->slug }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- children --}}
                        @if ($category->children->isNotEmpty())
                            <div x-show="open" x-collapse class="border-t border-slate-100 bg-slate-50/50">
                                @foreach ($category->children as $child)
                                    <div
                                        class="group flex items-center justify-between px-5 py-3 pl-20 transition hover:bg-slate-100">
                                        <div class="flex flex-row gap-3">

                                            <div class="flex flex-col">

                                                <span class="text-sm font-medium text-slate-700">
                                                    {{ $child->name }}
                                                </span>
                                                <span class="text-xs text-slate-400">
                                                    /{{ $child->slug }}
                                                </span>
                                            </div>
                                            |
                                            <div>
                                                <p class="text-sm font-medium text-slate-700">Total Products</p>
                                                <span class="text-slate-500">{{ $child->products_count }}</span>
                                            </div>

                                        </div>
                                        {{-- child Actions --}}
                                        <div
                                            class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                            <a href="{{ route('admin.categories.edit', $child) }}"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-yellow-100 hover:text-yellow-600"
                                                title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $child) }}" method="POST"
                                                onsubmit="return confirm('Do you want to delete this category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 cursor-pointer"
                                                    title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
