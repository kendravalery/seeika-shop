@extends('layouts.layout_admin')
@section('ContentAdmin')
    <div x-data="{
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
    }" @keydown.escape.window="closeDelete()">
        {{-- Breadcrumb --}}
        <div class="flex gap-3 text-slate-500 mb-4">
            <p>Collections</p>
        </div>
        @php
            $types = [
                'original' => 'Original',
                'collaboration' => 'Collaboration',
                'seasonal' => 'Seasonal',
                'event' => 'Event',
            ];

            $statuses = [
                'draft' => 'Draft',
                'active' => 'Active',
                'inactive' => 'Inactive',
            ];
        @endphp
        <div class="border border-slate-200 bg-white shadow-sm rounded-xl">
            <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Collections</h2>
                    <p class="mt-1 text-sm text-slate-500">Collections table</p>
                </div>
                <a href="{{ route('admin.collections.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 px-5 py-2 text-white rounded-lg font-medium">
                    <i class="bx bx-plus text-sm"></i>
                    Add Collection
                </a>
            </div>

            <div class="py-2 flex items-center justify-between px-3">

                {{-- search collections --}}
                <div class="w-64 px-1 ">
                    <form action="{{ route('admin.collections.index') }}" method="GET" class="max-w-xl mx-auto relative">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">

                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search Collection"
                            class="w-full pl-4 py-2.5 pr-20 border border-gray-200 rounded-full  focus:outline-none focus:ring-blue-300 text-sm shadow">

                        <button type="submit"
                            class="absolute right-1 top-1/2 -translate-y-1/2 bg-blue-600 text-white px-4 py-1.5 rounded-full hover:bg-blue-700">
                            <i class="bx bx-search"></i>
                        </button>
                    </form>
                </div>
                <form action="{{ route('admin.collections.index') }}" method="GET" class="flex gap-2"
                    x-data="{
                        typeOpen: false,
                        statusOpen: false,

                        type: @js(request('type', '')),
                        status: @js(request('status', '')),

                        types: @js($types),
                        statuses: @js($statuses),
                        typeLabel() {
                            return this.types[this.type] ?? 'Type';
                        },
                        statusLabel() {
                            return this.statuses[this.status] ?? 'Status';
                        }
                    }">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="type" x-model="type">
                    <input type="hidden" name="status" x-model="status">

                    <div class="relative">
                        <button type="button" @click="typeOpen = !typeOpen; statusOpen = false"
                            @click.outside="typeOpen = false"
                            class="flex items-center gap-2 border border-slate-300 rounded-lg py-1 px-4 bg-white shadow">

                            <span x-text="typeLabel()"></span>
                            <i class="bx bx-chevron-down text-xl transition-all duration-200"
                                :class="{ 'rotate-180': typeOpen }"></i>
                        </button>
                        <div x-show="typeOpen" x-cloak
                            class="absolute mt-2 w-48 border border-slate-200 bg-white shadow-lg">
                            <button type="submit" @click="type = ''"
                                class="block w-full text-left px-4 py-2 hover:bg-slate-100">
                                All Types
                            </button>
                            @foreach ($types as $value => $label)
                                <button type="submit" @click="type = @js($value)"
                                    class="block w-full text-left px-4 py-2 hover:bg-slate-100">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    {{-- Status --}}
                    <div class="relative">
                        <button type="button" @click="statusOpen = !statusOpen; typeOpen = false"
                            @click.outside="statusOpen = false"
                            class="flex items-center gap-2 border border-slate-300 rounded-lg py-1 px-4 bg-white shadow">
                            <span x-text="statusLabel()"></span>
                            <i class="bx bx-chevron-down text-xl transition-all duration-200"
                                :class="{ 'rotate-180': statusOpen }"></i>
                        </button>
                        <div x-show="statusOpen" x-cloak
                            class="absolute right-0 mt-2 w-48 border border-slate-200 bg-white shadow-lg">
                            <button type="submit" @click="status = ''"
                                class="block w-full text-left px-4 py-2 hover:bg-slate-100">
                                All Status
                            </button>
                            @foreach ($statuses as $value => $label)
                                <button type="submit" @click="status = @js($value)"
                                    class="block w-full text-left px-4 py-2 hover:bg-slate-100">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-4">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-slate-400 text-slate-900">
                            <th class="p-3">Collection</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Products</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Updated</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($collections as $collection)
                            <tr class="border-t border-slate-400 hover:bg-gray-50">
                                <td class="p-4">{{ $collection->name }}</td>
                                <td class="p-4">{{ $types[$collection->type] ?? $collection->type }}</td>
                                <td class="p-4">{{ $collection->products_count }}</td>
                                <td class="p-4">{{ $statuses[$collection->status] ?? $collection->status }}</td>

                                <td class="p-4">
                                    {{ $collection->updated_at?->setTimezone(auth()->user()->timezone)->format('d M Y, H:i') ?? '-' }}
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    <a href="{{ route('admin.collections.edit', $collection->id) }}"
                                        class="text-yellow-500 hover:text-yellow-600 mr-2" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <button type="button"
                                        @click="openDelete(
                                            @js(route('admin.collections.destroy', $collection->id)),
                                            @js($collection->name)
                                        )"
                                        class="text-red-500 hover:text-red-700 cursor-pointer" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500">
                                        <i class="bx bx-search-alt text-4xl mb-4"></i>

                                        @if (request()->filled('q') || request()->filled('type') || request()->filled('status'))
                                            <p class="font-medium text-slate-700">No collections found</p>
                                            <p class="text-sm mt-1">Try adjusting search or filters.</p>
                                        @else
                                            <p class="font-medium text-slate-700">No collections yet</p>
                                            <p class="text-sm mt-1">Create first collection to get started.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
                @if ($collections->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">
                        {{ $collections->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- for popup --}}
        <div x-show="deleteOpen" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div x-show="deleteOpen" x-transition.scale @click.outside="closeDelete()"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="px-6 py-5 text-center">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Delete Collection?
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
@endsection
