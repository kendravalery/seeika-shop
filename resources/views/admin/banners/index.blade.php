@extends('layouts.layout_admin')

@section('ContentAdmin')
    <div class="mb-4 text-slate-500">
        <p>Banners</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" x-data="{
        deleteOpen: false,
        deleteName: '',
        deleteAction: '',

        closeDelete() {
            this.deleteOpen = false;
            this.deleteName = '';
            this.deleteAction = '';
        }
    }">
        <div
            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Banners</h2>

                <p class="text-sm text-slate-500">Manage your shop banners.</p>
            </div>

            <a href="{{ route('admin.banners.create') }}"
                class="inline-flex w-fit items-center justify-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                <i class="bx bx-plus text-sm"></i>
                Add Banner
            </a>
        </div>

        <div class="overflow-x-auto px-4">
            <table class="w-full">
                <thead>
                    <tr class="border-b text-left text-sm text-slate-600">
                        <th class="p-2">Image</th>
                        <th class="p-2">Product</th>
                        <th class="p-2">Link</th>
                        <th class="p-2 text-center">Status</th>
                        <th class="p-2 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($banners as $banner)
                        <tr class="border-b border-slate-200 text-sm transition hover:bg-slate-50">
                            <td class="p-2">
                                <img src="{{ asset('storage/' . $banner->image) }}" alt="Banner image"
                                    class="mx-auto h-16 w-32 rounded object-cover">
                            </td>

                            <td class="p-2">
                                {{ $banner->product?->name ?? '-' }}
                            </td>

                            <td class="max-w-xs break-all p-2">
                                {{ $banner->link ?: '-' }}
                            </td>

                            <td class="p-2 text-center">
                                <form action="{{ route('admin.banners.toggle', $banner->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                        class="rounded px-3 py-1 text-xs font-medium text-white transition
                                        {{ $banner->is_active ? 'bg-green-500 hover:bg-green-600' : 'bg-slate-500 hover:bg-slate-600' }}">
                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-2">
                                <div class="flex items-center justify-center gap-3">

                                    <a href="{{ route('admin.banners.edit', $banner->id) }}"
                                        class="text-lg text-yellow-500 transition hover:text-yellow-600" title="Edit Banner"
                                        aria-label="Edit banner">
                                        <i class="bx bx-edit"></i>
                                    </a>

                                    <button type="button"
                                        @click="deleteName = @js($banner->product?->name ?? 'Banner #' . $banner->id); deleteAction = @js(route('admin.banners.destroy', $banner->id));deleteOpen = true;"
                                        class="text-lg text-red-500 transition hover:text-red-700" title="Delete Banner"
                                        aria-label="Delete banner">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                No banners found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="deleteOpen" x-cloak x-transition.opacity @keydown.escape.window="closeDelete()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div x-show="deleteOpen" x-transition.scale @click.outside="closeDelete()"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="px-6 py-5 text-center">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Delete Banner?
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
