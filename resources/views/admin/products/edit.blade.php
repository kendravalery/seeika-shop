@extends('layouts.layout_admin')

@section('ContentAdmin')
    {{-- Even though the `->sortBy('position')` syntax is already in the model,
    I added it anyway—just to be doubly sure. --}}
    @php
        $isVariantProduct = $product->options->isNotEmpty();
        $variant = $product->variants->first();

        $preorderReleaseLabel = old('preorder_release_label', $product->preorder_release_label ?? '');
        $singleStock = (string) old('stock', $variant?->stock ?? 0);
        $singleComparePrice = old(
            'compare_price',
            $variant?->compare_price !== null ? number_format($variant->compare_price, 0, ',', '.') : '',
        );
        $singleMaxPurchasePerOrder = (string) old('max_purchase_per_order', $variant?->max_purchase_per_order ?? '');
        $singlePreorderLimit = (string) old('preorder_limit', $variant?->preorder_limit ?? '');

        // images used by option values should not appear in product images
        $optionImageIds = $product->options
            ->flatMap(fn($option) => $option->values->pluck('image_id'))
            ->filter()
            ->unique();

        $productImages = $product->images->whereNotIn('id', $optionImageIds)->sortBy('position')->values();
        $editOptions = $product->options
            ->map(function ($option) {
                return [
                    'key' => 'option-' . $option->id,
                    'id' => $option->id,
                    'name' => $option->option_name,
                    'useImages' => $option->values->contains(fn($value) => $value->image_id),
                    'values' => $option->values
                        ->sortBy('position')

                        ->map(function ($value) {
                            return [
                                'key' => 'value-' . $value->id,
                                'id' => $value->id,
                                'value' => $value->value,
                                'position' => $value->position,
                                'imageId' => $value->image_id,
                                'imageUrl' => $value->image ? asset('storage/' . $value->image->path) : null,
                                'imagePreview' => $value->image ? asset('storage/' . $value->image->path) : null,
                                'newImage' => null,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    @endphp

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data"
        x-data="{
            productType: @js($isVariantProduct ? 'variant' : 'single'),
            productName: @js(old('name', $product->name)),
            productSlug: @js(old('slug', $product->slug)),
            availability: @js(old('availability', $product->availability)),
            preorderReleaseLabel: @js($preorderReleaseLabel),
            singleStock: @js($singleStock),
            singleComparePrice: @js($singleComparePrice),
            singleMaxPurchasePerOrder: @js($singleMaxPurchasePerOrder),
            singlePreorderLimit: @js($singlePreorderLimit),

            images: @js(
    $productImages

        ->map(
            fn($image) => [
                'id' => $image->id,

                'path' => $image->path,

                'is_primary' => $image->is_primary,
            ],
        )
        ->values(),
),
            newImages: [],
            removedImages: [],
            primaryType: @js($productImages->firstWhere('is_primary', true) ? 'existing' : null),

            primaryImageId: @js(optional($productImages->firstWhere('is_primary', true))->id),
            primaryNewIndex: null,

            addImages(event) {
                const files = Array.from(event.target.files);

                this.newImages.push(...files);
                // set first new image as primary when no primary exists.
                if (
                    this.primaryType === null &&
                    this.newImages.length > 0
                ) {
                    this.primaryType = 'new';
                    this.primaryNewIndex = 0;
                    this.primaryImageId = null;
                }
                const dataTransfer = new DataTransfer();
                this.newImages.forEach(file => {
                    dataTransfer.items.add(file);
                });

                event.target.files = dataTransfer.files;
            },

            removeNewImage(index) {
                this.newImages.splice(index, 1);

                if (this.primaryType === 'new') {
                    if (this.primaryNewIndex === index) {
                        if (this.newImages.length > 0) {
                            this.primaryNewIndex = 0;
                            this.primaryType = 'new';
                        } else if (this.images.length > 0) {
                            this.primaryType = 'existing';
                            this.primaryImageId = this.images[0]?.id ?? null;
                            this.primaryNewIndex = null;
                        } else {
                            this.primaryType = null;
                            this.primaryNewIndex = null;
                            this.primaryImageId = null;
                        }
                    } else if (this.primaryNewIndex > index) {

                        this.primaryNewIndex--;
                    }
                }
                const input = this.$refs.imageInput;
                const dataTransfer = new DataTransfer();

                this.newImages.forEach(file => {
                    dataTransfer.items.add(file);
                });
                input.files = dataTransfer.files;
            },

            removeExistingImage(index) {
                const image = this.images[index];

                if (!image) return;

                this.removedImages.push(image.id);
                this.images.splice(index, 1);

                if (
                    this.primaryType === 'existing' &&
                    this.primaryImageId === image.id
                ) {
                    if (this.images.length > 0) {
                        this.primaryType = 'existing';
                        this.primaryImageId = this.images[0].id;
                        this.primaryNewIndex = null;
                    } else if (this.newImages.length > 0) {
                        this.primaryType = 'new';
                        this.primaryNewIndex = 0;
                        this.primaryImageId = null;
                    } else {
                        this.primaryType = null;
                        this.primaryImageId = null;
                        this.primaryNewIndex = null;
                    }
                }
            },

            setExistingPrimary(id) {
                this.primaryType = 'existing';
                this.primaryImageId = id;
                this.primaryNewIndex = null;
            },

            setNewPrimary(index) {
                this.primaryType = 'new';
                this.primaryNewIndex = index;
                this.primaryImageId = null;
            },

            formatRupiah(input) {
                let value = input.value.replace(/\D/g, '');
                input.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
            },

            initRupiah(input) {

                if (!input.value) return;
                let value = input.value.replace(/\D/g, '');

                input.value = value ?
                    new Intl.NumberFormat('id-ID').format(value) : '';
            },

            cleanRupiah() {
                this.$root.querySelectorAll('[data-rupiah]').forEach(input => {

                    input.value = input.value.replace(/\D/g, '');
                });
            },

            showSinglePreorderLimit() {
                return this.availability === 'preorder';
            },

            updateSinglePreorderLimit() {
                if (this.availability !== 'preorder') {
                    this.singlePreorderLimit = '';
                }
            },

            showVariantPreorderLimit(variant) {
                return this.availability === 'preorder';
            },

            updateVariantPreorderLimit(variant) {
                if (this.availability !== 'preorder') {
                    variant.preorder_limit = '';
                }
            },

            generateSlug(value) {
                return value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            },

            updateAvailability(value) {
                this.availability = value;
                this.updateSinglePreorderLimit();
                this.variants.forEach(variant => this.updateVariantPreorderLimit(variant));
                if (value !== 'preorder') {
                    this.preorderReleaseLabel = '';
                }
            },

            options: @js($editOptions),
            removedOptionIds: [],
            removedValueIds: [],
            photoOpen: false,
            photoSrc: '',

            openPhoto(src) {
                this.photoSrc = src;
                this.photoOpen = true;
            },
            closePhoto() {
                this.photoOpen = false;
                this.photoSrc = '';
            },

            addOption() {
                if (this.options.length >= 2) {
                    return;
                }

                this.options.push({
                    key: crypto.randomUUID(),
                    id: null,
                    name: '',
                    useImages: false,
                    values: [{
                        key: crypto.randomUUID(),
                        id: null,
                        value: '',
                        position: 0,
                        imageId: null,
                        imageUrl: null,
                        imagePreview: null,
                        newImage: null,
                    }],
                });

                this.variantsDirty = true;
            },

            removeOption(index) {

                if (this.options.length <= 1) {
                    return;
                }

                const option = this.options[index];

                if (option.id) {

                    this.removedOptionIds.push(option.id);

                    option.values.forEach(value => {
                        if (value.id) {
                            this.removedValueIds.push(value.id);
                        }
                    });
                }

                this.options.splice(index, 1);
                this.variantsDirty = true;
            },
            addValue(optionIndex) {
                this.options[optionIndex].values.push({
                    key: crypto.randomUUID(),
                    id: null,
                    value: '',
                    position: this.options[optionIndex].values.length,
                    imageId: null,
                    imageUrl: null,
                    imagePreview: null,
                    newImage: null,
                });

                this.variantsDirty = true;
            },

            removeValue(optionIndex, valueIndex) {

                const option = this.options[optionIndex];

                if (option.values.length <= 1) {
                    return;
                }

                const value = option.values[valueIndex];

                if (value.id) {
                    this.removedValueIds.push(value.id);
                }

                option.values.splice(valueIndex, 1);

                option.values.forEach((value, index) => {
                    value.position = index;
                });
                this.variantsDirty = true;
            },
            changeOptionImage(event, optionIndex, valueIndex) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                const value = this.options[optionIndex].values[valueIndex];

                value.newImage = file;

                value.imagePreview = URL.createObjectURL(file);
            },

            toggleOptionImages(optionIndex) {
                const selectedOption = this.options[optionIndex];

                if (!selectedOption.useImages) {
                    selectedOption.values.forEach(value => {
                        value.imagePreview = null;
                    });

                    return;
                }

                selectedOption.values.forEach(value => {
                    if (value.newImage) {
                        value.imagePreview = URL.createObjectURL(value.newImage);
                    } else {
                        value.imagePreview = value.imageUrl;
                    }
                });

                this.options.forEach((option, index) => {

                    if (index !== optionIndex) {
                        option.useImages = false;

                        option.values.forEach(value => {
                            value.imagePreview = null;
                        });
                    }
                });
            },

            variants: @js(
    $product->variants
        ->map(function ($variant) {
            return [
                'key' => 'variant-' . $variant->id,
                'id' => $variant->id,
                'optionValueKeys' => $variant->optionValues->pluck('id')->map(fn($id) => 'id:' . $id)->values()->all(),
                'sku' => $variant->sku,
                'price' => number_format($variant->price, 0, ',', '.'),
                'compare_price' => $variant->compare_price !== null ? number_format($variant->compare_price, 0, ',', '.') : '',
                'stock' => $variant->stock,
                'max_purchase_per_order' => $variant->max_purchase_per_order,
                'preorder_limit' => $variant->preorder_limit,
                'weight' => $variant->weight,
                'is_active' => (bool) $variant->is_active,
            ];
        })
        ->values(),
),
            removedVariantIds: [],

            variantsDirty: false,

            getValueToken(value) {
                return value.id ?
                    `id:${value.id}` :
                    `key:${value.key}`;
            },

            getCombinationKey(tokens) {
                return [...tokens].sort().join('|');
            },

            buildCombinations() {
                const combinations = [];
                const walk = (optionIndex, currentTokens) => {

                    if (optionIndex < 0) {
                        combinations.push([...currentTokens].reverse());
                        return;
                    }

                    const option = this.options[optionIndex];
                    option.values.forEach(value => {

                        walk(
                            optionIndex - 1,
                            [...currentTokens, this.getValueToken(value)]
                        );
                    });
                };
                walk(this.options.length - 1, []);
                return combinations;
            },

            variantLabel(variant) {
                return this.options

                    .map(option => {

                        const value = option.values.find(value =>
                            variant.optionValueKeys.includes(
                                this.getValueToken(value)
                            )
                        );

                        return value?.value ?? '';
                    })
                    .filter(Boolean)
                    .join(' / ');
            },

            normalizeSku(sku) {
                return String(sku ?? '').trim().toLowerCase();
            },

            isDuplicateSku(variant) {
                const sku = this.normalizeSku(variant.sku);

                if (!sku) return false;

                return this.variants.filter(item =>
                    this.normalizeSku(item.sku) === sku
                ).length > 1;
            },

            activeVariantCount() {
                return this.variants.filter(variant => variant.is_active).length;
            },

            activeVariantCount() {
                return this.variants.filter(variant => variant.is_active).length;
            },

            toggleVariantActive(index) {
                const variant = this.variants[index];

                // Don't allow the last active variant to become inactive.
                if (
                    variant.is_active &&
                    this.activeVariantCount() === 1
                ) {
                    return;
                }

                variant.is_active = !variant.is_active;
            },

            syncVariants() {
                if (this.options.length === 0) {
                    return;
                }

                // Make sure every option has a name and every value has a name.
                const invalidOption = this.options.some(option =>
                    !option.name.trim() ||
                    option.values.length === 0 ||
                    option.values.some(value => !value.value.trim())
                );

                if (invalidOption) {
                    alert('Please complete all option names and values before syncing variants.');
                    return;
                }

                // Build all valid option-value tokens from the current options.
                const currentValueTokens = new Set();

                this.options.forEach(option => {
                    option.values.forEach(value => {
                        currentValueTokens.add(this.getValueToken(value));
                    });
                });

                //  If a new option exists, its first value becomes the default
                //   value for existing variants.
                const defaultNewOptionTokens = this.options
                    .filter(option => option.id === null)
                    .map(option => this.getValueToken(option.values[0]));

                const existingVariantMap = new Map();

                this.variants.forEach(variant => {
                    let tokens = (variant.optionValueKeys ?? [])
                        .filter(token => currentValueTokens.has(token));
                    defaultNewOptionTokens.forEach(token => {
                        if (!tokens.includes(token)) {
                            tokens.push(token);
                        }
                    });
                    const combinationKey = this.getCombinationKey(tokens);

                    // If multiple old variants collapse into the same combination,
                    // keep the first one as the variant to reuse.
                    if (existingVariantMap.has(combinationKey)) {
                        return;
                    }

                    existingVariantMap.set(combinationKey, {
                        ...variant,
                        optionValueKeys: tokens,
                    });
                });

                const combinations = this.buildCombinations();

                if (combinations.length === 0) {
                    return;
                }

                // Unused old variants are marked for deletion.
                const usedExistingVariantIds = new Set();

                const nextVariants = combinations.map(tokens => {
                    const combinationKey = this.getCombinationKey(tokens);
                    const existingVariant = existingVariantMap.get(combinationKey);

                    if (existingVariant) {
                        if (existingVariant.id) {
                            usedExistingVariantIds.add(existingVariant.id);
                        }
                        return {
                            ...existingVariant,
                            optionValueKeys: [...tokens],
                        };
                    }

                    return {
                        key: crypto.randomUUID(),
                        id: null,
                        optionValueKeys: [...tokens],
                        sku: '',
                        price: '',
                        compare_price: '',
                        stock: 0,
                        max_purchase_per_order: '',
                        preorder_limit: '',
                        weight: '',
                        is_active: true
                    };
                });

                // Make sure at least one variant remains active.
                if (
                    nextVariants.length > 0 &&
                    !nextVariants.some(variant => variant.is_active)
                ) {
                    nextVariants[0].is_active = true;
                }

                const obsoleteVariantIds = this.variants
                    .filter(variant =>
                        variant.id &&
                        !usedExistingVariantIds.has(variant.id)

                    )
                    .map(variant => variant.id);

                if (obsoleteVariantIds.length > 0) {
                    const confirmed = confirm(
                        `${obsoleteVariantIds.length} existing variant(s) ` +
                        `are no longer compatible with the current options.\n\n` +
                        `They will be removed when you save the product.\n\n` +
                        `Continue?`
                    );

                    if (!confirmed) {
                        return;
                    }
                }

                this.removedVariantIds = [
                    ...new Set([
                        ...this.removedVariantIds,
                        ...obsoleteVariantIds,
                    ]),

                ];

                this.variants = nextVariants;
                this.variantsDirty = false;
            },

            handleSubmit(event) {
                this.cleanRupiah();
                if (this.productType === 'variant' && this.variantsDirty) {
                    event.preventDefault();
                    alert('Please sync variants before saving the product.');
                }
            },

            sortOptionValues(optionIndex, valueIndex, event) {
                const option = this.options[optionIndex];

                let newPosition = Number(event.target.value);

                if (Number.isNaN(newPosition)) { newPosition = valueIndex; }

                newPosition = Math.max(
                    0,
                    Math.min(newPosition, option.values.length - 1)

                );

                const [movedValue] = option.values.splice(valueIndex, 1);
                option.values.splice(newPosition, 0, movedValue);

                option.values.forEach((value, index) => {
                    value.position = index;
                });
                this.variantsDirty = true;
            },
        }" x-init="updateAvailability(availability)" @submit="handleSubmit($event)">
        @csrf
        @method('PUT')

        <div>
            {{-- // Breadcrumb  --}}
            <div class="flex gap-3 text-slate-500 mb-4">
                <a href="{{ route('admin.products.index') }}">Products</a>

                <i class="bx bx-chevron-right text-2xl"></i>
                <p>Edit Product</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 my-4">
                <div class="w-full lg:flex-1">

                    <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-5">Product Information</h2>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product Name</label>
                            <input type="text" id="product-name" name="name" x-model="productName"
                                @input="productSlug = generateSlug($event.target.value)"
                                value="{{ old('name', $product->name) }}"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 outline-none focus:border-slate-400"
                                required>
                            @error('name')
                                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label for="product-slug" class="block text-sm font-medium text-slate-700 mb-2">Slug</label>
                            <input type="text" id="product-slug" name="slug" x-model="productSlug"
                                value="{{ old('slug', $product->slug) }}" placeholder="product-slug"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 outline-none focus:border-slate-400"
                                required>
                            @error('slug')
                                <span class="text-red-600 text-sm mt-1 block">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="">
                            <label class="block text-sm font-medium text-slate-700 mb-2"> Description </label>

                            <textarea name="description" rows="5"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 outline-none focus:border-slate-400">{{ old('description', $product->description) }}</textarea>

                            @error('description')
                                <span class="text-red-600 text-sm mt-1 block"> {{ $message }} </span>
                            @enderror
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-700 mb-3">
                                Product Specifications
                                <span class="text-xs text-slate-400 ">/Optional</span>
                            </h2>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Scale
                            </label>
                            <input type="text" name="attributes[scale]"
                                value="{{ old('attributes.scale', data_get($product->attributes, 'scale')) }}"
                                placeholder="e.g. 1/7"
                                class="border border-slate-200 p-2 w-full mb-3 rounded-lg outline-none focus:border-slate-400">

                            @error('attributes.scale')
                                <span class="text-sm text-red-600 block mb-2">{{ $message }}</span>
                            @enderror

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Manufacturer
                            </label>
                            <input type="text" name="attributes[manufacturer]"
                                value="{{ old('attributes.manufacturer', data_get($product->attributes, 'manufacturer')) }}"
                                placeholder="e.g. Company"
                                class="border border-slate-200 p-2 w-full rounded-lg outline-none focus:border-slate-400">

                            @error('attributes.manufacturer')
                                <span class="text-sm text-red-600 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-5 mt-4">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                                {{-- existing product images, okay --}}
                                <template x-for="(image, index) in images" :key="image.id">
                                    <div class="relative group">
                                        <div class="w-full h-28 rounded-lg overflow-hidden bg-slate-200 border-2"
                                            :class="primaryImageId === image.id ? 'border-blue-500' : 'border-slate-200'">
                                            <img :src="'{{ asset('storage') }}/' + image.path"
                                                class="w-full h-full object-cover">
                                        </div>

                                        <button type="button" @click="setExistingPrimary(image.id)"
                                            class="absolute top-1.5 left-1.5 w-6 h-6 rounded-full flex items-center justify-center shadow transition-opacity"
                                            :class="primaryImageId === image.id ? 'bg-amber-400 text-white' :
                                                'bg-white/90 text-slate-400 hover:text-amber-500 opacity-0 group-hover:opacity-100'"
                                            :title="primaryImageId === image.id ? 'Primary photo' : 'Set as primary'">
                                            <i class="bx bx-star text-sm"></i>
                                        </button>

                                        <button type="button" @click="removeExistingImage(index)"
                                            class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-white/90 text-slate-500 hover:text-red-600  flex items-center justify-center shadow  opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Remove photo">
                                            <i class="bx bx-x text-sm"></i>
                                        </button>
                                    </div>
                                </template>

                                <template x-for="(file, index) in newImages" :key="index">

                                    <div class="relative group">
                                        <div class="w-full h-28 rounded-lg overflow-hidden bg-slate-200 border-2"
                                            :class="primaryType === 'new' && primaryNewIndex === index ?
                                                'border-blue-500' : 'border-slate-200'">
                                            <img :src="URL.createObjectURL(file)" class="w-full h-full object-cover">
                                        </div>

                                        <button type="button" @click="setNewPrimary(index)"
                                            class="absolute top-1.5 left-1.5 w-6 h-6 rounded-full flex items-center justify-center shadow transition-opacity"
                                            :class="primaryType === 'new' && primaryNewIndex === index ?
                                                'bg-amber-400 text-white' :
                                                'bg-white/90 text-slate-400 hover:text-amber-500 opacity-0 group-hover:opacity-100'"
                                            :title="primaryType === 'new' && primaryNewIndex === index ? 'Primary photo' :
                                                'Set as primary'">
                                            <i class="bx bx-star text-sm"></i>
                                        </button>

                                        <button type="button" @click="removeNewImage(index)"
                                            class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-white/90 text-slate-500 hover:text-red-600 flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Remove photo">
                                            <i class="bx bx-x text-sm"></i>
                                        </button>
                                    </div>
                                </template>

                                <label
                                    class="w-full h-28 rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 hover:border-slate-400 hover:text-slate-500 cursor-pointer transition-colors">
                                    <i class="bx bx-plus text-xl"></i>
                                    <span class="text-xs mt-1">Add Photo</span>
                                    <input x-ref="imageInput" type="file" name="images[]" multiple accept="image/*"
                                        @change="addImages($event)" class="hidden">
                                </label>
                            </div>

                            {{-- removed existing images --}}
                            <template x-for="id in removedImages" :key="'removed-' + id">
                                <input type="hidden" name="removed_images[]" :value="id">
                            </template>

                            <input type="hidden" name="primary_image_id"
                                :value="primaryType === 'existing' ? primaryImageId : ''">

                            <input type="hidden" name="primary_new_index"
                                :value="primaryType === 'new' ? primaryNewIndex : ''">
                        </div>
                    </div>

                    {{-- single product fields placed here --}}
                    <div x-show="productType === 'single'" x-cloak
                        class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
                        <fieldset :disabled="productType !== 'single'">

                            <h2 class="font-semibold mb-4 text-slate-500">Single Product</h2>
                            <div id="single-product-section">
                                <label class="block text-sm font-medium text-slate-700 mt-1">Sku</label>
                                @error('sku')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                                <input type="text" name="sku" value="{{ old('sku', $variant?->sku ?? '') }}"
                                    placeholder="TSH-NKE-LNG-M-BLK"
                                    class="border border-slate-200 p-2 w-full rounded-md  focus:outline-none focus:border-slate-400 "
                                    required>

                                <label class="block text-sm font-medium text-slate-700 mt-1">Price</label>
                                @error('price')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                                <div class="relative mb-2">
                                    <span class="absolute  left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="text" name="price"
                                        value="{{ old('price', $variant?->price ? number_format($variant->price, 0, ',', '.') : '') }}"
                                        data-rupiah x-init="initRupiah($el)" @input="formatRupiah($el)"
                                        inputmode="numeric" placeholder="0"
                                        class="border border-slate-200 p-2 pl-10 w-full rounded-md  focus:outline-none focus:border-slate-400"
                                        required>
                                </div>

                                <label class="block text-sm font-medium text-slate-700 mt-1">
                                    Price before discount <span class="text-xs text-slate-400">/Optional</span>
                                </label>
                                <div class="relative mb-2">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="text" name="compare_price" x-model="singleComparePrice"
                                        @input="formatRupiah($el); singleComparePrice = $el.value" data-rupiah
                                        inputmode="numeric" placeholder="0"
                                        class="border border-slate-200 p-2 pl-10 w-full rounded-md focus:outline-none focus:border-slate-400">
                                </div>

                                <label class="block text-sm font-medium text-slate-700 mt-1">Stock</label>
                                @error('stock')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                                <input type="number" name="stock" placeholder="0" min="0" step="1"
                                    x-model="singleStock" @input="updateSinglePreorderLimit()"
                                    @change="updateSinglePreorderLimit()"
                                    class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                                    required>

                                <label class="block text-sm font-medium text-slate-700 mt-1">weight</label>
                                @error('weight')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                                <div class="relative mb-2">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">kg</span>
                                    <input type="number" step="0.01" name="weight" placeholder="0"
                                        value="{{ old('weight', $variant?->weight ?? 0) }}"
                                        class="border border-slate-200 p-2 pl-10 w-full rounded-md  focus:outline-none focus:border-slate-400"
                                        required>
                                </div>

                                <label class="block text-sm font-medium text-slate-700 mt-1">
                                    Max Purchase per Order <span class="text-xs text-slate-400">/Optional</span>
                                </label>
                                @error('max_purchase_per_order')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                                <input type="number" name="max_purchase_per_order" x-model="singleMaxPurchasePerOrder"
                                    min="1" step="1" placeholder="No limit"
                                    class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400">

                                <div x-show="showSinglePreorderLimit()" x-cloak class="mt-3">
                                    <label class="block text-sm font-medium text-slate-700 mt-1">
                                        Pre-order Limit
                                    </label>
                                    @error('preorder_limit')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                    <input type="number" name="preorder_limit" x-model="singlePreorderLimit"
                                        min="1" step="1" placeholder="Enter pre-order limit"
                                        class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400">
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    {{-- // variant editor placed here --}}
                    <div x-show="productType === 'variant'" x-cloak class="mt-6">
                        <fieldset :disabled="productType !== 'variant'">
                            <div class="bg-white rounded border border-slate-200 p-6">
                                <h2 class="font-semibold text-lg mb-5">Product Options</h2>

                                <div id="options-container">
                                    <template x-for="(option, optionIndex) in options" :key="option.key">

                                        <div class="option-item border border-slate-200 rounded-lg p-4 mb-4 shadow-sm">
                                            <input type="hidden" :name="`options[${optionIndex}][id]`"
                                                :value="option.id ?? ''">

                                            <div class="flex items-center justify-between">
                                                <label class="block mb-3"> Option Name</label>
                                                <button type="button" @click="removeOption(optionIndex)"
                                                    class="rounded-lg text-gray-400 hover:text-red-600"
                                                    title="Remove Option" :disabled="options.length <= 1">
                                                    <i class="bx bx-trash text-xl"></i>
                                                </button>
                                            </div>

                                            <div class="flex gap-2 mb-3">
                                                <input type="text" :name="`options[${optionIndex}][name]`"
                                                    x-model="option.name" placeholder="Option name (e.g. Color, Size)"
                                                    class="option-name-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                                                    required>
                                            </div>

                                            <label class="flex items-center gap-2 mb-3">
                                                <input type="checkbox" :name="`options[${optionIndex}][use_images]`"
                                                    value="1" x-model="option.useImages"
                                                    @change="toggleOptionImages(optionIndex)" class="use-images-checkbox">
                                                <span>Use image for this option</span>
                                            </label>

                                            <label class="block mb-2">Values</label>

                                            {{-- values --}}
                                            <div class="values-container">
                                                <template x-for="(value, valueIndex) in option.values"
                                                    :key="value.key">

                                                    <div class="values-item flex items-center gap-2 mb-2">
                                                        <input type="number" min="0" :value="value.position"
                                                            @change="sortOptionValues(optionIndex, valueIndex, $event)"
                                                            class="w-16 border border-slate-200 p-2 rounded-md focus:outline-none focus:border-slate-400">

                                                        <input type="hidden"
                                                            :name="`options[${optionIndex}][values][${valueIndex}][id]`"
                                                            :value="value.id ?? ''">

                                                        <input type="hidden"
                                                            :name="`options[${optionIndex}][values][${valueIndex}][key]`"
                                                            :value="value.key">

                                                        <input type="hidden"
                                                            :name="`options[${optionIndex}][values][${valueIndex}][image_id]`"
                                                            :value="value.imageId ?? ''">

                                                        <input type="text"
                                                            :name="`options[${optionIndex}][values][${valueIndex}][value]`"
                                                            x-model="value.value"
                                                            placeholder="Value name (e.g. Red, Blue, S)"
                                                            class="option-value-input border border-slate-200 p-2 w-full rounded-md outline-none focus:border-slate-400"
                                                            required>

                                                        <template x-if="value.imagePreview">
                                                            <button type="button" @click="openPhoto(value.imagePreview)"
                                                                x-show="option.useImages"
                                                                class="shrink-0 w-10 h-10 rounded-md overflow-hidden border border-slate-200 hover:border-blue-400 cursor-pointer"
                                                                title="View image">
                                                                <img :src="value.imagePreview"
                                                                    class="w-full h-full object-cover">
                                                            </button>
                                                        </template>

                                                        <label x-show="option.useImages"
                                                            class="shrink-0 px-3 py-2 border border-slate-200 rounded-md text-sm text-slate-600 hover:bg-slate-50 cursor-pointer">
                                                            Change
                                                            <input type="file"
                                                                :name="`option_images[${optionIndex}][${valueIndex}]`"
                                                                accept="image/*" class="hidden"
                                                                :disabled="!option.useImages"
                                                                @change="changeOptionImage($event, optionIndex, valueIndex)">

                                                        </label>

                                                        <button type="button"
                                                            @click="removeValue(optionIndex, valueIndex)"
                                                            class="shrink-0 rounded-xl hover:text-red-600 text-gray-400"
                                                            title="Remove Value" :disabled="option.values.length <= 1">
                                                            <i class="bx bx-x text-2xl"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>

                                            <button type="button" @click="addValue(optionIndex)"
                                                class="text-blue-600 text-sm cursor-pointer">
                                                + Add Value
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="addOption()" :disabled="options.length >= 2"
                                    :class="options.length >= 2 ? 'opacity-50 cursor-not-allowed' :
                                        'cursor-pointer hover:bg-slate-50'"
                                    class="px-4 py-2 border border-slate-200 rounded">
                                    <i class="bx bx-plus"></i>
                                    Add Option
                                </button>
                            </div>

                            <div class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
                                <div class="flex items-center justify-between mb-5">
                                    <h2 class="font-semibold text-lg">Variants</h2>

                                    <button type="button" @click="syncVariants()" :disabled="!variantsDirty"
                                        :class="variantsDirty ? 'bg-blue-600 hover:bg-blue-700 text-white' :
                                            'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                        class="px-4 py-2 rounded-md text-sm font-medium transition">
                                        Sync Variants
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="(variant, variantIndex) in variants" :key="variant.key">
                                        <div class="border border-slate-200 rounded-xl bg-white p-4 sm:p-5 shadow-sm">
                                            {{-- Variant identity --}}
                                            <div class="mb-4">
                                                <h3 class="font-semibold text-slate-800"
                                                    x-text="variantLabel(variant) || `Variant ${variantIndex + 1}`"></h3>
                                                <p class="text-xs text-slate-400 mt-1">
                                                    Variant #<span x-text="variantIndex + 1"></span>
                                                </p>
                                            </div>

                                            {{-- preserve variant IDs and option combinations --}}
                                            <input type="hidden" :name="`variants[${variantIndex}][id]`"
                                                :value="variant.id ?? ''">

                                            <template x-for="valueKey in variant.optionValueKeys" :key="valueKey">
                                                <input type="hidden"
                                                    :name="`variants[${variantIndex}][option_value_keys][]`"
                                                    :value="valueKey">
                                            </template>

                                            <input type="hidden" :name="`variants[${variantIndex}][is_active]`"
                                                :value="variant.is_active ? 1 : 0">

                                            {{-- first row --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 items-end">
                                                <label class="block min-w-0">
                                                    <span class="block text-xs font-medium text-slate-600 mb-1">SKU</span>
                                                    <input type="text" :name="`variants[${variantIndex}][sku]`"
                                                        x-model="variant.sku" placeholder="SKU"
                                                        class="rounded-md p-2 w-full min-w-0 outline-none"
                                                        :class="isDuplicateSku(variant) ?
                                                            'border border-red-500 text-red-500 bg-red-50 focus:border-red-500' :
                                                            'border border-slate-200 focus:border-slate-400'">
                                                </label>

                                                <label class="block min-w-0">
                                                    <span
                                                        class="block text-xs font-medium text-slate-600 mb-1">Price</span>
                                                    <div class="relative">
                                                        <span
                                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                                        <input type="text" :name="`variants[${variantIndex}][price]`"
                                                            x-model="variant.price"
                                                            @input="formatRupiah($event.target); variant.price = $event.target.value"
                                                            data-rupiah inputmode="numeric" placeholder="0"
                                                            class="border border-slate-200 rounded-md p-2 pl-8 w-full min-w-0 outline-none focus:border-slate-400">
                                                    </div>
                                                </label>
                                                <label class="block min-w-0">
                                                    <span class="block text-xs font-medium text-slate-600 mb-1">
                                                        Price before discount
                                                        <span class="text-slate-400">/Optional</span>
                                                    </span>
                                                    <div class="relative">
                                                        <span
                                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                                        <input type="text"
                                                            :name="`variants[${variantIndex}][compare_price]`"
                                                            x-model="variant.compare_price"
                                                            @input="formatRupiah($event.target); variant.compare_price = $event.target.value"
                                                            data-rupiah inputmode="numeric" placeholder="0"
                                                            class="border border-slate-200 rounded-md p-2 pl-8 w-full min-w-0 outline-none focus:border-slate-400">
                                                    </div>
                                                </label>

                                                <label class="block min-w-0">
                                                    <span
                                                        class="block text-xs font-medium text-slate-600 mb-1">Stock</span>
                                                    <input type="number" min="0" step="1"
                                                        :name="`variants[${variantIndex}][stock]`" x-model="variant.stock"
                                                        @input="updateVariantPreorderLimit(variant)"
                                                        @change="updateVariantPreorderLimit(variant)" placeholder="0"
                                                        class="border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400">
                                                </label>

                                            </div>
                                            {{-- Second row: Compare Price, purchase limit, preorder limit, status --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 items-end"
                                                :class="showVariantPreorderLimit(variant) ? 'xl:grid-cols-4' : 'xl:grid-cols-3'">

                                                <label class="block min-w-0">
                                                    <span class="block text-xs font-medium text-slate-600 mb-1">Weight
                                                        (kg)</span>
                                                    <input type="number" min="0" step="0.01"
                                                        :name="`variants[${variantIndex}][weight]`"
                                                        x-model="variant.weight" placeholder="0"
                                                        class="border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400">
                                                </label>

                                                <label class="block min-w-0">
                                                    <span class="block text-xs font-medium text-slate-600 mb-1">
                                                        Max Purchase per Order
                                                        <span class="text-slate-400">/Optional</span>
                                                    </span>
                                                    <input type="number" min="1" step="1"
                                                        :name="`variants[${variantIndex}][max_purchase_per_order]`"
                                                        x-model="variant.max_purchase_per_order" placeholder="No limit"
                                                        class="border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400">
                                                </label>

                                                <label x-show="showVariantPreorderLimit(variant)" class="block min-w-0"
                                                    x-cloak>
                                                    <span class="block text-xs font-medium text-slate-600 mb-1">
                                                        Pre-order Limit
                                                    </span>
                                                    <input type="number" min="1" step="1"
                                                        :name="`variants[${variantIndex}][preorder_limit]`"
                                                        x-model="variant.preorder_limit" placeholder="pre-order limit"
                                                        class="border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400">
                                                </label>

                                                <div class="min-w-0">
                                                    <span
                                                        class="block text-xs font-medium text-slate-600 mb-1">Status</span>
                                                    <div class="flex items-center gap-2 h-[38px]">
                                                        <button type="button" @click="toggleVariantActive(variantIndex)"
                                                            :disabled="variant.is_active && activeVariantCount() === 1"
                                                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
                                                            :class="[
                                                                variant.is_active ? 'bg-green-500' : 'bg-slate-300',
                                                                variant.is_active && activeVariantCount() === 1 ?
                                                                'opacity-60 cursor-not-allowed' :
                                                                'cursor-pointer'
                                                            ]">
                                                            <span
                                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                                                                :class="variant.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                                                        </button>
                                                        <span class="text-sm whitespace-nowrap"
                                                            :class="variant.is_active ? 'text-green-600' : 'text-slate-500'"
                                                            x-text="variant.is_active ? 'Active' : 'Inactive'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="variants.length === 0"
                                        class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                                        No variants available. Add product options and sync variants first.
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- check section for Option images / popup  --}}
                        <template x-if="photoOpen">
                            <div x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
                                @click.self="closePhoto()" @keydown.escape.window="closePhoto()">
                                <div class="relative max-w-3xl max-h-[90vh]">
                                    <button type="button" @click="closePhoto()"
                                        class="absolute -top-10 right-0 text-white hover:text-slate-300" title="Close">
                                        <i class="bx bx-x text-3xl"></i>
                                    </button>
                                    <img :src="photoSrc" class="max-w-full max-h-[85vh] rounded-lg shadow-xl">
                                </div>
                            </div>
                        </template>

                        <template x-for="id in removedOptionIds" :key="`removed-option-${id}`">
                            <input type="hidden" name="removed_option_ids[]" :value="id">
                        </template>

                        <template x-for="id in removedValueIds" :key="`removed-value-${id}`">
                            <input type="hidden" name="removed_value_ids[]" :value="id">
                        </template>

                        <template x-for="id in removedVariantIds" :key="`removed-variant-${id}`">
                            <input type="hidden" name="removed_variant_ids[]" :value="id">
                        </template>
                    </div>
                    {{-- // --}}
                </div>

                <div class="w-full lg:w-80">
                    <div class="lg:sticky lg:top-4">

                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
                            <div class="px-5 py-4 border-b border-slate-200">
                                <h2 class="font-semibold text-slate-800">
                                    Product Organization
                                </h2>

                                <p class="text-sm text-slate-500 mt-1">Organize how this product appears in shop.</p>
                            </div>

                            <div class="p-5 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Collection <span class="text-xs text-slate-400">/Optional</span>
                                    </label>

                                    <select name="collection_id"
                                        class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400">
                                        <option value="">-- No Collection --</option>
                                        @foreach ($collections as $collection)
                                            <option value="{{ $collection->id }}" @selected(old('collection_id', $product->collection_id) == $collection->id)>
                                                {{ $collection->name }}</option>
                                        @endforeach
                                    </select>

                                    @error('collection_id')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>

                                    <select name="category_id"
                                        class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400"
                                        required>

                                        <option value="">-- Choose Category --</option>
                                        @foreach ($categoryChildrens as $categoryChildren)
                                            <option value="{{ $categoryChildren->id }}" @selected(old('category_id', $product->category_id) == $categoryChildren->id)>
                                                {{ $categoryChildren->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('category_id')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Availability
                                    </label>

                                    <select name="availability" @change="updateAvailability($event.target.value)"
                                        class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400"
                                        required>

                                        <option value="ready" @selected(old('availability', $product->availability) === 'ready')>
                                            Ready
                                        </option>

                                        <option value="preorder" @selected(old('availability', $product->availability) === 'preorder')>
                                            Pre-Order
                                        </option>
                                    </select>

                                    @error('availability')
                                        <span class="text-red-600 text-sm mt-1 block">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div x-show="availability === 'preorder'" x-cloak>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Pre-order Release
                                    </label>
                                    @error('preorder_release_label')
                                        <span class="text-red-600 text-sm block mb-1">{{ $message }}</span>
                                    @enderror
                                    <input type="text" name="preorder_release_label" x-model="preorderReleaseLabel"
                                        placeholder="e.g. Expected to ship in June 2027"
                                        class="border border-slate-200 p-2.5 w-full rounded-md focus:outline-none focus:border-slate-400">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Status
                                    </label>

                                    <select name="status"
                                        class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400"
                                        required>

                                        <option value="draft" @selected(old('status', $product->status) === 'draft')>
                                            Draft
                                        </option>

                                        <option value="active" @selected(old('status', $product->status) === 'active')>
                                            Active
                                        </option>

                                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>
                                            Inactive
                                        </option>
                                    </select>

                                    @error('status')
                                        <span class="text-red-600 text-sm mt-1 block">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 mt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-yellow-400 w-full text-white shadow-lg rounded border border-slate-200 hover:bg-yellow-500 cursor-pointer">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.products.index') }}"
                                class="px-4 py-2 bg-white w-full shadow-lg rounded border border-slate-200 hover:bg-slate-50 text-center">
                                Cancel
                            </a>
                        </div>

                        <div id="variant-hidden-inputs"></div>

                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
