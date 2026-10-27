@extends('layouts.layout_admin')

@section('ContentAdmin')
    <div class="flex gap-3 text-slate-500 mb-4">
        <a href="{{ route('admin.banners.index') }}">Banners</a>
        <i class="bx bx-chevron-right text-2xl"></i>
        <p>Edit Banner</p>
    </div>

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data"
        x-data="{
            preview: null,
            existingImage: @js($banner->image ? asset('storage/' . $banner->image) : null),

            clear() {
                if (this.preview) {
                    URL.revokeObjectURL(this.preview);
                }
                this.preview = null;
                this.$refs.image.value = '';
            }
        }">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="flex-1 rounded-xl border border-slate-200 bg-white p-2 shadow">

                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Banner Details</h2>
                </div>

                <div class="px-5 py-4">
                    <label for="product_id" class="mb-1 block">
                        Product :
                    </label>

                    <select name="product_id" id="product_id"
                        class="w-full rounded border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow outline-none">

                        <option value="">
                            None
                        </option>

                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $banner->product_id) == $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="px-5 py-4">
                    <label for="link" class="mb-1 block">
                        Link :
                    </label>

                    <input type="text" name="link" id="link" value="{{ old('link', $banner->link) }}"
                        placeholder="https://example.com"
                        class="w-full rounded border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow outline-none">

                    @error('link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="px-5 py-4">
                    <label for="is_active" class="mb-1 block">
                        Status :
                    </label>

                    @php
                        $selectedStatus = (string) old('is_active', (int) $banner->is_active);
                    @endphp

                    <select name="is_active" id="is_active"
                        class="w-full rounded border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow outline-none"
                        required>

                        <option value="1" @selected($selectedStatus === '1')>
                            Active
                        </option>

                        <option value="0" @selected($selectedStatus === '0')>
                            Inactive
                        </option>
                    </select>

                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="flex-1 rounded-xl border border-slate-200 bg-white p-2 shadow">

                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">
                        Banner Image
                    </h2>
                </div>

                <div class="px-5 py-4">

                    <label for="image" class="mb-2 block border-b border-slate-200 font-medium text-slate-700">
                        Image
                        <span class="text-xs text-slate-500">(optional)</span>
                    </label>

                    <input type="file" name="image" id="image" accept="image/*" class="hidden" x-ref="image"
                        @change="
                            const file = $event.target.files[0];

                            if (file) {
                                if (preview) {
                                    URL.revokeObjectURL(preview);
                                }

                                preview = URL.createObjectURL(file);
                            }">
                    <div @click="$refs.image.click()"
                        class="relative aspect-[3/1] w-full cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:bg-slate-100">

                        <template x-if="preview">
                            <img :src="preview" alt="New banner preview"
                                class="absolute inset-0 h-full w-full object-cover">
                        </template>

                        <template x-if="!preview && existingImage">
                            <img :src="existingImage" alt="Current banner"
                                class="absolute inset-0 h-full w-full object-cover">
                        </template>

                        <template x-if="!preview && !existingImage">
                            <div class="flex h-full flex-col items-center justify-center text-slate-400">
                                <i class="bx bx-image-add text-3xl"></i>
                                <p class="mt-1 text-sm font-medium">Upload Banner</p>
                                <p class="mt-1 text-xs">Click to select an image</p>
                            </div>
                        </template>

                        {{-- clear new image selection --}}
                        <button type="button" x-show="preview" x-cloak @click.stop="clear()"
                            class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-red-600"
                            aria-label="Clear selected image">
                            <i class="bx bx-x text-xl"></i>
                        </button>

                    </div>

                    <p class="mt-2 text-xs text-slate-500">Select a new image to replace the current banner.</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                </div>
            </div>
        </div>

        <div class="my-3 flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700">
                Update Banner
            </button>

            <a href="{{ route('admin.banners.index') }}"
                class="rounded-lg border border-slate-200 px-4 py-2 shadow-sm hover:bg-slate-50">
                Cancel
            </a>
        </div>

    </form>
@endsection
