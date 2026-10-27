@extends('layouts.layout_admin')
@section('ContentAdmin')
    <div class="flex gap-3 text-slate-500 mb-4">
        {{-- Breadcrumb --}}
        <a href="{{ route('admin.categories.index') }}" class="">Categories</a>
        <i class="bx bx-chevron-right text-2xl"></i>
        <p>+Add Category</p>
    </div>
    <form action="{{route('admin.categories.store' )}}" method="POST">
        @csrf
        <div class="flex flex-col gap-6 lg:flex-row">

            <div class="flex-1  rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 py-4 px-5">
                    <h2 class="mb-2 font-semibold text-lg text-slate-900 ">Category information</h2>
                    <p class="text-sm text-slate-500">Fill in the product category details completely.</p>
                </div>

                <div class="px-5 py-4">
                    <label for="parent_id" class="mb-2 block text-sm font-medium text-slate-700">
                        Parent Category
                    </label>

                    <select id="parent_id" name="parent_id"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700" required>
                        <option value="">None</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>

                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="px-5 py-4">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Category name</label>

                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter Category Name"
                        required
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">

                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="w-full lg:w-80">
                <div class="flex flex-col gap-3">

                    <button type="submit"
                        class="w-full rounded-lg border border-slate-200 bg-blue-600 py-2.5 px-4 text-center text-sm font-medium text-white shadow-sm hover:bg-blue-700 cursor-pointer">Save
                        Category</button>

                    <a href="{{ route('admin.categories.index') }}"
                        class="w-full rounded-lg border border-slate-200 bg-white  py-2.5 px-4 text-center text-sm  font-medium text-slate-700 shadow-sm">Cancel</a>
                </div>
            </div>
        </div>
    </form>
@endsection
