@extends('layouts.layout_admin')
@section('ContentAdmin')
    <div class="flex gap-3 text-slate-500 mb-4">
        <a href="{{ route('admin.collections.index') }}">Collections</a>
        <i class="bx bx-chevron-right text-2xl"></i>
        <p>Edit Collection</p>
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

    <form action="{{ route('admin.collections.update', $collection->id) }}" method="POST" enctype="multipart/form-data"
        x-data="{
            name: @js(old('name', $collection->name)),
            slug: @js(old('slug', $collection->slug)),
            slugManuallyEdited: @js($collection->slug !== \Illuminate\Support\Str::slug($collection->name)),

            generateSlug(value) {
                return value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },
            updateSlug() {
                if (!this.slugManuallyEdited) {
                    this.slug = this.generateSlug(this.name);
                }
            },
            editSlug() {
                this.slugManuallyEdited = true;

                if (this.slug === '') {
                    this.slugManuallyEdited = false;
                    this.slug = this.generateSlug(this.name);
                }
            }
        }">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-6 lg:flex-row">

            <div class="flex-1 border border-slate-200 bg-white p-2 rounded-xl shadow">
                <div class="border-b border-slate-200 py-4 px-5">
                    <h2 class="font-semibold text-slate-900">Collection Details</h2>
                </div>

                <div class="px-5 py-4">
                    <label for="name" class="block mb-1">Name :</label>
                    <input type="text" name="name" id="name" x-model="name" @input="updateSlug"
                        placeholder="Collection name"
                        class="w-full rounded border border-slate-200 py-2 px-3  text-sm text-slate-700 outline-none shadow" required>
                    @error('name')
                        <span class="text-red-600 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="px-5 py-4">
                    <label for="slug" class="block mb-1">Slug :</label>

                    <input type="text" name="slug" id="slug" x-model="slug" @input="editSlug"
                        placeholder="collection-slug"
                        class="w-full rounded border border-slate-200 py-2 px-3  text-sm text-slate-700 outline-none shadow"
                        required>

                </div>

                <div class="px-5 py-4">
                    <label for="type" class="block mb-1">Type :</label>
                    <select name="type" id="type"
                        class="w-full rounded border border-slate-200 py-2 px-3 text-sm text-slate-700 outline-none shadow"
                        required>
                        <option value="">Select type</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $collection->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <span class="text-red-600 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="px-5 py-4">
                    <label for="description" class="block mb-1">Description/<span
                            class="text-xs text-slate-500">optional</span> :</label>

                    <textarea name="description" id="description" rows="10"
                        class="w-full border border-slate-200 rounded-lg outline-none shadow-sm px-1">{{ old('description', $collection->description) }}</textarea>
                </div>
                <div class="px-5 py-4">
                    <label for="status" class="block mb-1">Status :</label>
                    <select name="status" id="status"
                        class="w-full rounded border border-slate-200 py-2 px-3 text-sm text-slate-700 outline-none shadow" required>
                        <option value="">Select status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $collection->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <span class="text-red-600 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <div class=" flex-1 border border-slate-200 bg-white  p-2 rounded-xl shadow">
                <div class="border-b border-slate-200 py-4 px-5">
                    <h2 class="font-semibold text-slate-900">Banner & Thumbnail </h2>
                </div>

                <div x-data="{
                    preview: null,
                    existing: @js($collection->banner ? asset('storage/' . $collection->banner) : null),
                    removeExisting: false,

                    clear() {
                        if (this.preview) {
                            URL.revokeObjectURL(this.preview);
                            this.preview = null;
                            this.$refs.banner.value = '';
                            return;
                        }

                        this.existing = null;
                        this.removeExisting = true;
                    }
                }" class="px-5 py-4">

                    <label for="banner"
                        class="block mb-2 font-medium text-slate-700 border-b border-slate-200">Banner</label>

                    <input type="hidden" name="remove_banner" :value="removeExisting ? 1 : 0">

                    <input type="file" name="banner" id="banner" accept="image/*" class="hidden" x-ref="banner"
                        @change="
                        const file = $event.target.files[0];

                        if (file) {
                            if(preview){
                                URL.revokeObjectURL(preview);
                            }
                            preview = URL.createObjectURL(file);
                            removeExisting = false;
                        }">

                    <div @click="$refs.banner.click()"
                        class="relative w-full aspect-[3/1] border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 overflow-hidden hover:bg-slate-100 transition">


                        <template x-if="preview">
                            <img :src="preview" alt="Banner Preview" class="w-full h-full object-cover">
                        </template>


                        <template x-if="!preview && existing">
                            <img :src="existing" alt="Current Banner" class="w-full h-full object-cover">
                        </template>

                        <template x-if="!preview && !existing">
                            <div class="h-full flex flex-col items-center justify-center text-slate-400">
                                <p class="text-sm font-medium">Upload Banner</p>
                                <p class="text-xs mt-1"> Recommended: 2400 x 800 px</p>
                            </div>
                        </template>

                        <button type="button" x-show="preview || existing" x-cloak @click.stop="clear()"
                            class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center rounded-full bg-black/60 text-white hover:bg-red-600 transition">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>
                </div>

                <div x-data="{
                    preview: null,

                    existing: @js($collection->thumbnail ? asset('storage/' . $collection->thumbnail) : null),
                    removeExisting: false,
                    clear() {
                        if (this.preview) {
                            URL.revokeObjectURL(this.preview);
                            this.preview = null;
                            this.$refs.thumbnail.value = '';
                            return;
                        }

                        this.existing = null;
                        this.removeExisting = true;
                    }
                }" class="px-5 py-4">

                    <label for="thumbnail"
                        class="block mb-2 font-medium text-slate-700 border-b border-slate-200">Thumbnail</label>

                    <input type="hidden" name="remove_thumbnail" :value="removeExisting ? 1 : 0">

                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="hidden"
                        x-ref="thumbnail"
                        @change="
                        const file = $event.target.files[0];

                        if (file) {
                            if(preview){
                                URL.revokeObjectURL(preview);
                            }
                            preview = URL.createObjectURL(file);
                            removeExisting = false;
                        }">

                    <div @click="$refs.thumbnail.click()"
                        class="relative w-full aspect-square max-w-sm border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 overflow-hidden hover:bg-slate-100 transition">

                        {{--  new preview --}}
                        <template x-if="preview">
                            <img :src="preview"
                                alt="thumbnail preview"class="absolute inset-0 w-full h-full object-cover">
                        </template>
                        {{-- existing image --}}
                        <template x-if="!preview && existing">
                            <img :src="existing" alt="Current Thumbnail"
                                class="absolute inset-0 w-full h-full object-cover">
                        </template>

                        <template x-if="!preview && !existing">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                                <p class="text-sm font-medium">Upload Thumbnail</p>
                                <p class="text-xs mt-1">Recommended: 1200 x 1200 px</p>
                            </div>
                        </template>

                        <button type="button" x-show="preview || existing" x-cloak @click.stop="clear()"
                            class="absolute top-2 right-2 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/60 text-white hover:bg-red-600 transition">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 my-3">
            <button type="submit" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-white rounded-lg shadow-sm">
                Save Changes
            </button>

            <a href="{{ route('admin.collections.index') }}"
                class="border border-slate-200 px-4 py-2 rounded-lg shadow-sm">Cancel</a>
        </div>

    </form>
@endsection
