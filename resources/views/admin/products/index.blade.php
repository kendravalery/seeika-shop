@extends('layouts.layout_admin')

@section('ContentAdmin')
    <div class="flex gap-3 text-slate-500 mb-4">
        {{-- Breadcrumb --}}
        <p> Products</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200" x-data="{
        deleteOpen: false,
        deleteAction: '',
        deleteName: '',

        openDelete(action, name) {
            this.deleteAction = action;
            this.deleteName = name;
            this.deleteOpen = true;
        },

        closeDelete() {
            this.deleteOpen = false;
            this.deleteAction = '';
            this.deleteName = '';
        }
    }"
        @keydown.escape.window="closeDelete()">
        <div class="border-b border-slate-200  px-5 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Products</h2>
                <p class="mt-1 text-sm text-slate-500">categories table</p>
            </div>
            <a href="{{ route('admin.products.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                <i class="bx bx-plus text-sm"></i> Add Product
            </a>
        </div>
        <div class="py-2 flex items-center justify-between px-3">
            <div class="w-64 px-1 ">
                <form action="{{ route('admin.products.index') }}" method="GET" class="max-w-xl mx-auto relative ">

                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search Products"
                        class="w-full pl-4 py-2.5 pr-20 border border-gray-200 rounded-full  focus:outline-none focus:ring-blue-300 text-sm shadow">

                    <button type="submit"
                        class="absolute right-1 top-1/2 -translate-y-1/2 bg-blue-600 text-white px-4 py-1.5 rounded-full hover:bg-blue-700">
                        <i class="bx bx-search"></i>
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-3" x-data="{
                filterOpen: false,
                sortOpen: false
            }">

                <div class="relative">
                    <button type="button" @click="filterOpen = !filterOpen; sortOpen = false"
                        class="border border-slate-200 px-4 py-2 rounded-lg shadow-sm text-sm flex items-center gap-1">
                        <i class="bx bx-filter-alt"></i>
                        <span>Filter</span>
                        <i class="bx bx-chevron-down"></i>
                    </button>

                    <div x-show="filterOpen" x-cloak @click.outside="filterOpen = false"
                        class="absolute right-0 top-full mt-2 z-30 w-72 bg-white border border-slate-200 rounded-xl shadow-lg p-4">

                        <form action="{{ route('admin.products.index') }}" method="GET" class="space-y-4">


                            <input type="hidden" name="q" value="{{ request('q') }}">

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Status
                                </label>

                                <select name="status" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">All Status</option>
                                    <option value="active" @selected(request('status') === 'active')>
                                        Active
                                    </option>
                                    <option value="inactive" @selected(request('status') === 'inactive')>
                                        Inactive
                                    </option>
                                    <option value="draft" @selected(request('status') === 'draft')>
                                        Draft
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Category
                                </label>

                                <select name="category_id"
                                    class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">All Categories</option>

                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Collection
                                </label>

                                <select name="collection_id"
                                    class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">All Collections</option>

                                    @foreach ($collections as $collection)
                                        <option value="{{ $collection->id }}" @selected((string) request('collection_id') === (string) $collection->id)>
                                            {{ $collection->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Product Type
                                </label>

                                <select name="product_type"
                                    class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">All Types</option>
                                    <option value="single" @selected(request('product_type') === 'single')>
                                        Single Product
                                    </option>
                                    <option value="variant" @selected(request('product_type') === 'variant')>
                                        With Variants
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Availability
                                </label>

                                <select name="availability"
                                    class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">All Availability</option>
                                    <option value="ready" @selected(request('availability') === 'ready')>
                                        Ready
                                    </option>
                                    <option value="preorder" @selected(request('availability') === 'preorder')>
                                        Pre-Order
                                    </option>
                                </select>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <a href="{{ route('admin.products.index') }}"
                                    class="flex-1 text-center border border-slate-200 rounded-md px-3 py-2 text-sm hover:bg-slate-50">
                                    Reset
                                </a>

                                <button type="submit"
                                    class="flex-1 bg-blue-600 text-white rounded-md px-3 py-2 text-sm hover:bg-blue-700">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" @click="sortOpen = !sortOpen; filterOpen = false"
                        class="border border-slate-200 px-4 py-2 rounded-lg shadow-sm text-sm flex items-center gap-1">
                        <i class="bx bx-sort"></i>
                        <span>Sort</span>
                        <i class="bx bx-chevron-down"></i>
                    </button>

                    <div x-show="sortOpen" x-cloak @click.outside="sortOpen = false"
                        class="absolute right-0 top-full mt-2 z-30 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-2">

                        @php
                            $sorts = [
                                '' => 'Newest',
                                'oldest' => 'Oldest',
                                'name_asc' => 'Name A-Z',
                                'name_desc' => 'Name Z-A',
                                'price_asc' => 'Price Low → High',
                                'price_desc' => 'Price High → Low',
                                'stock_asc' => 'Stock Low → High',
                                'stock_desc' => 'Stock High → Low',
                                'sold_desc' => 'Best Selling',
                            ];

                            // Preserve search and filters, but reset pagination.
                            $sortQuery = request()->except('page');
                        @endphp

                        @foreach ($sorts as $value => $label)
                            @php
                                $sortParams = $sortQuery;

                                if ($value === '') {
                                    unset($sortParams['sort']);
                                } else {
                                    $sortParams['sort'] = $value;
                                }
                            @endphp

                            <a href="{{ route('admin.products.index', $sortParams) }}"
                                class="block px-4 py-2 text-sm hover:bg-slate-50{{ request('sort', '') === $value ? 'bg-slate-50 font-medium text-blue-600' : 'text-slate-700' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto px-4">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-b-slate-400">
                        <th class="p-3">Image</th>
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Stock</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">status</th>
                        <th class="p-3 px-4">Availability</th>
                        <th class="p-3">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $variants = $product->variants;

                            $minPrice = $variants->min('price');
                            $maxPrice = $variants->max('price');

                            $totalStock = $variants->sum('stock');
                        @endphp
                        <tr class="border-t border-t-slate-400 hover:bg-gray-50">
                            <td class="p-3">
                                {{-- IMAGE --}}
                                @if ($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                        class="w-12 h-12 object-cover rounded cursor-pointer"
                                        onclick="openImageModal(this.src)">
                                @else
                                    <span class="text-gray-400 text-sm">No Image</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $product->name }}</td>
                            {{-- Price --}}
                            <td class="p-3 whitespace-nowrap">
                                @if ($minPrice !== null && $maxPrice !== null)
                                    @if ($minPrice === $maxPrice)
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                        -
                                        Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                    @endif
                                @else
                                    <span class="text-gray-400">
                                        -
                                    </span>
                                @endif
                            </td>

                            <td class="p-3">{{ $totalStock }}</td>
                            <td class="p-3">
                                {{ $product->category?->name ?? '-' }}
                            </td>
                            <td class="p-3">
                                @if ($product->status === 'active')
                                    <span class="text-sm py-1 px-3 bg-blue-100 text-blue-600 rounded-lg">Active</span>
                                @elseif ($product->status === 'inactive')
                                    <span class="text-sm py-1 px-3 bg-gray-100 text-gray-500 rounded-lg">inactive</span>
                                @else
                                    <span class="text-sm py-1 px-3 bg-yellow-100 text-yellow-500 rounded-lg">draft</span>
                                @endif
                            </td>

                            <td class="p-3">
                                @if ($product->availability === 'ready')
                                    <span class="text-sm py-1 px-3 bg-green-100 text-green-500 rounded-lg">
                                        Ready
                                    </span>
                                @else
                                    <span class="text-sm py-1 px-3 bg-yellow-100 text-yellow-500 rounded-lg">
                                        Pre-order
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 whitespace-nowrap">

                                <a href="{{ route('admin.products.show', $product->id) }}"
                                    class="text-slate-500 hover:text-blue-500 mr-2" title="show Product">
                                    <i class="bx bx-eye"></i>
                                </a>

                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="text-blue-500 hover:text-blue-600 mr-2" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </a>


                                <button type="button" class="text-red-500 hover:text-red-600 cursor-pointer"
                                    title="Delete"
                                    @click="openDelete(
                                        '{{ route('admin.products.destroy', $product->id) }}',
                                        @js($product->name)
                                    )">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500">
                                    <i class="bx bx-search-alt text-4xl mb-4"></i>
                                    <p class="font-medium text-slate-700">No Product Yet</p>
                                    <p class="text-sm mt-1">Create first Product to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($products->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @endif
        </div>

        <div x-show="deleteOpen" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div x-show="deleteOpen" x-transition.scale @click.outside="closeDelete()"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="px-6 py-5 text-center">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Delete Product?
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
                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 cursor-pointer">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-50">
        <div class="relative">
            <img id="modalImage" alt="product image" class="max-w-3xl max-h-[88vh] rounded shadow-lg">
            <button onclick="closeImageModal()"
                class="absolute -top-3 -right-3 bg-black/40 hover:bg-red-500 text-white w-8 h-8 rounded-full ">X</button>
        </div>
    </div>
    <script>
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const img = document.getElementById('modalImage');

            img.src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target.id == this) {
                closeImageModal();
            }
        });
    </script>
@endsection
