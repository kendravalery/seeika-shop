@extends('layouts.layout_admin')

@section('ContentAdmin')
    <div class="flex gap-3 text-slate-500 mb-4">
        {{-- Breadcrumb --}}
        <a href="{{ route('admin.products.index') }}" class="">Products</a>
        <i class="bx bx-chevron-right text-2xl"></i>
        <p>+Add Product</p>
    </div>
    <form id="product-form" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
                <p class="font-semibold mb-2">error:</p>

                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-6 lg:flex-row my-4">
            <div class="flex-1  bg-white rounded-lg border border-slate-200 shadow-sm">
                <div class="pb-4  border-b border-slate-200 p-6">
                    <h1 class="text-lg font-semibold ">
                        Add Product
                    </h1>
                </div>
                <div class="p-5">
                    <label class="block text-sm font-medium text-slate-700 mt-1">Product Name</label>
                    @error('name')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                    <input name="name" id="product-name" value="{{ old('name') }}" placeholder="name..."
                        class="border border-slate-200 p-2 w-full mb-2 rounded-md focus:outline-none focus:border-slate-400"
                        required>
                    @error('slug')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                    <label for="product-slug" class="block text-sm font-medium text-slate-700 mt-1">
                        Slug
                    </label>
                    <input type="text" name="slug" id="product-slug" value="{{ old('slug') }}"
                        placeholder="product-slug"
                        class="border border-slate-200 p-2 w-full mb-2 rounded-md focus:outline-none focus:border-slate-400"
                        required>

                    <label class="block text-sm font-medium text-slate-700 mt-1">description</label>
                    <textarea name="description"
                        class="border border-slate-200 p-2 w-full mb-2 rounded-md  focus:outline-none focus:border-slate-400">{{ old('description') }}</textarea>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-700 mb-3">
                            Product Specifications<span class="text-xs text-slate-400">/ Optional</span>
                        </h2>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Scale
                        </label>
                        <input type="text" name="attributes[scale]" value="{{ old('attributes.scale') }}"
                            placeholder="e.g. 1/7" class="border border-slate-200 p-2 w-full mb-3 rounded-md">

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Manufacturer
                        </label>
                        <input type="text" name="attributes[manufacturer]" value="{{ old('attributes.manufacturer') }}"
                            placeholder="e.g.Company" class="border border-slate-200 p-2 w-full rounded-md">
                    </div>

                    {{-- Multiple images --}}
                    <div>
                        <div id="preview" class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4">
                            <label for="imageInput" data-add-photo="true"
                                class="w-full h-28 border-2 rounded-lg  border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 hover:border-slate-400 hover:text-slate-500 transition-colors cursor-pointer ">
                                <i class="bx bx-plus text-xl"></i>
                                <span class="text-xs mt-1">
                                    Add Photo
                                </span>
                            </label>
                        </div>
                        <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                            onchange="previewImages(event)" class="hidden">
                    </div>
                </div>

                <hr class="border-slate-200">
                {{-- Product Type --}}
                <div class="p-5">

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Product Type <span class="text-xs text-slate-400">/ Optional</span>
                    </label>

                    <div class="relative flex w-full max-w-md p-1 bg-slate-100 rounded-lg border border-slate-200">

                        {{-- slinding background --}}
                        <div id="product-type-slider"
                            class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] bg-white rounded-md shadow-sm transition-transform duration-200 ease-out">
                        </div>
                        {{-- Single Product  --}}
                        <label class="relative z-10 flex-1 cursor-pointer">
                            <input type="radio" name="product_type" value="single" class="peer sr-only"
                                onchange="toggleProductType(event)" checked>
                            <span
                                class="flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-500 peer-checked:text-slate-700 transition-colors duration-200">Single
                                Product</span>
                        </label>

                        {{-- PRODUCT WITH VARIANT --}}
                        <label class="relative z-10 flex-1 cursor-pointer">
                            <input type="radio" name="product_type" value="variant" class="peer sr-only"
                                onchange="toggleProductType(event)">
                            <span
                                class="flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-500 peer-checked:text-slate-700 transition-colors duration-200">Product
                                With Variants</span>
                        </label>
                    </div>

                    {{-- Single Product --}}
                    <div id="single-product-section" class="mt-6">
                        <label class="block text-sm font-medium text-slate-700 mt-1">SKU</label>
                        @error('sku')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        <input type="text" name="sku" value="{{ old('sku') }}" placeholder="TSH-NKE-LNG-M-BLK"
                            class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                            required>

                        <label class="block text-sm font-medium text-slate-700 mt-1">Price</label>
                        @error('price')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        <div class="relative mb-2">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" name="price" value="{{ old('price') }}" oninput="RupiahFormat(this)"
                                inputmode="numeric" placeholder="0"
                                class="border border-slate-200 p-2 pl-10 w-full rounded-md focus:outline-none focus:border-slate-400"
                                required>
                        </div>

                        <label class="block text-sm font-medium text-slate-700">
                            Price before discount
                            <span class="text-xs text-slate-400">/Optional</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" name="compare_price" value="{{ old('compare_price') }}"
                                id="compare_price" inputmode="numeric" placeholder="0" oninput="RupiahFormat(this)"
                                class="border border-slate-200 p-2 pl-10 w-full rounded-md focus:outline-none focus:border-slate-400">
                        </div>

                        <label class="block text-sm font-medium text-slate-700 mt-1">Stock</label>
                        @error('stock')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror

                        <input type="number" name="stock" id="single-stock" placeholder="0" min="0"
                            step="1" value="{{ old('stock') }}" oninput="updateSinglePreorderLimit()"
                            onchange="updateSinglePreorderLimit()"
                            class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                            required>

                        <label class="block text-sm font-medium text-slate-700 mt-1">Weight</label>
                        @error('weight')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        <div class="relative mb-2">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">kg</span>
                            <input type="number" step="0.01" name="weight" placeholder="0"
                                value="{{ old('weight') }}"
                                class="border border-slate-200 p-2 pl-10 w-full rounded-md focus:outline-none focus:border-slate-400"
                                required>
                        </div>

                        {{-- Max Purchase per Order --}}
                        <label class="block text-sm font-medium text-slate-700 mt-1">
                            Max Purchase per Order
                            <span class="text-xs text-slate-400">/Optional</span>
                        </label>
                        @error('max_purchase_per_order')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        <input type="number" name="max_purchase_per_order" value="{{ old('max_purchase_per_order') }}"
                            min="1" step="1" placeholder="No limit"
                            class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400">

                        {{-- Pre-order Limit --}}
                        <div id="single-preorder-limit-wrapper" class="hidden">
                            <label class="block text-sm font-medium text-slate-700 mt-1">
                                Pre-order Limit
                                <span class="text-xs text-slate-400">/Optional</span>
                            </label>
                            @error('preorder_limit')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                            <input type="number" name="preorder_limit" value="{{ old('preorder_limit') }}"
                                min="1" step="1" placeholder="Enter pre-order limit" disabled
                                class="border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    {{-- varinat product --}}
                    <div id="variant-product-section" class="hidden mb-4">
                        <h2 class="font-semibold text-lg my-4">Product Option</h2>
                        <div id="options-container">
                            <div class="option-item border border-slate-200 rounded p-4 mb-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <label class="block mb-3">Name Option</label>
                                    <button type="button" onclick="removeOption(this)"
                                        class=" rounded-lg text-gray-400 hover:text-red-600 " title="Remove Option">
                                        <i class="bx bx-trash text-xl"></i>
                                    </button>
                                </div>
                                <div class="flex gap-2 mb-3">
                                    <input type="text" name="options[0][name]"
                                        placeholder="option name (e.g. color,size..)"
                                        class="option-name-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                                        required>
                                </div>
                                <label class="flex items-center gap-2 mb-3">
                                    <input type="checkbox" name="options[0][use_images]" value="1"
                                        class="use-images-checkbox" onchange="toggleOptionImages(this)">
                                    <span>Use image for this option</span>
                                </label>

                                <label class="block  mb-2">Values</label>
                                <div class="values-container">
                                    <div class="values-item flex gap-2 mb-2 items-center">
                                        <input type="text" name="options[0][values][]"
                                            placeholder="value name (e.g. blue,m...)"
                                            class="option-value-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400"
                                            required>
                                        <input type="file" name="option_images[0][]" accept="image/*"
                                            onchange="previewOptionImage(this)"
                                            class="option-image-input hidden border border-slate-200 p-2 rounded-md">
                                        <img src="" alt="Option image preview"
                                            class="option-image-preview hidden w-10 h-10 rounded object-cover border border-slate-200 shrink-0">
                                        <button type="button" onclick="removeValue(this)"
                                            class=" rounded-xl hover:text-red-600 text-gray-400" title="Remove Value">
                                            <i class="bx bx-x text-2xl "></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" onclick="addValue(this)"
                                    class="text-blue-600 text-sm cursor-pointer">+
                                    add value
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-4">
                            <button type="button" onclick="addOption()"
                                class="px-4 py-2 border border-slate-200 rounded cursor-pointer" id="add-option-btn">+ add
                                Option
                            </button>
                            <button type="button" onclick="generateVariants()"
                                class="px-4 py-2 bg-blue-600 text-white rounded ">
                                Sync Variants
                            </button>
                        </div>

                        <div id="variant-list-section" class="hidden mt-6">
                            <h2 class="font-semibold text-lg mb-4">Variants</h2>
                            <div id="variant-list" class="space-y-4">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-80 ">
                <div class="lg:sticky lg:top-4 ">
                    <div class=" bg-white rounded-lg border border-slate-200 shadow-sm">

                        <div class="px-5 py-4 border-b border-slate-200">
                            <h2 class="font-semibold text-slate-800">Product Organization</h2>
                            <p class="text-sm text-slate-500 mt-1">Organize how this product appears in your shop.</p>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Collection <span class="text-xs text-slate-400 ">/Optional.</span>
                                </label>
                                <select name="collection_id"
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400">

                                    <option value="">-- No Collection --</option>
                                    @foreach ($collections as $collection)
                                        <option value="{{ $collection->id }}" @selected(old('collection_id') == $collection->id)>
                                            {{ $collection->name }}
                                        </option>
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
                                        <option value="{{ $categoryChildren->id }}" @selected(old('category_id') == $categoryChildren->id)>
                                            {{ $categoryChildren->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Availability</label>
                                <select name="availability" onchange="renderVariantList(); updateSinglePreorderLimit()"
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400"
                                    required>
                                    <option value="ready" @selected(old('availability', 'ready') === 'ready')>Ready</option>
                                    <option value="preorder" @selected(old('availability') === 'preorder')>Pre-Order</option>
                                </select>
                                @error('availability')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div id="preorder-release-wrapper"
                                class="{{ old('availability', 'ready') === 'preorder' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Pre-order Release
                                </label>

                                @error('preorder_release_label')
                                    <span class="text-red-600 text-sm block mb-1">
                                        {{ $message }}
                                    </span>
                                @enderror

                                <input type="text" id="preorder-release-label" name="preorder_release_label"
                                    value="{{ old('preorder_release_label') }}"
                                    placeholder="e.g. Expected to ship in June 2027"
                                    class="border border-slate-200 p-2.5 w-full rounded-md focus:outline-none focus:border-slate-400">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                                <select name="status"
                                    class="border border-slate-200 p-2.5 w-full rounded-md bg-white text-sm focus:outline-none focus:border-slate-400"
                                    required>
                                    <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                                    <option value="active" @selected(old('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 mt-4">
                        <button type="submit"
                            class=" px-4 py-2 bg-blue-600 w-full text-white shadow-lg rounded border border-slate-200 hover:bg-blue-700 cursor-pointer">
                            Save Product
                        </button>
                        <a href="{{ route('admin.products.index') }}"
                            class=" px-4 py-2 bg-white w-full  shadow-lg rounded border border-slate-200 hover:bg-slate-50 text-center">
                            Cancel
                        </a>
                    </div>
                    <div id="variant-hidden-inputs"></div>
                </div>
            </div>
        </div>
    </form>
    <script>
        const oldOptions = @json(old('options', []));
        const oldVariants = @json(old('variants', []));
        const oldProductType = @json(old('product_type', 'single'));
        const productName = document.getElementById('product-name');
        const productSlug = document.getElementById('product-slug');

        let selectedFiles = [];

        // primari image / index 0
        let primaryIndex = 0;

        const maxImages = 10;

        // max options
        const maxOptions = 2;

        // variable for price input
        let variantState = {};

        function RupiahFormat(input) {
            let value = input.value.replace(/\D/g, '');
            input.value = value ?
                new Intl.NumberFormat('id-ID').format(value) : '';
        }

        function generateSlug(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        productName.addEventListener('input', function() {
            productSlug.value = generateSlug(productName.value);
        });

        function previewImages(event) {
            let files = Array.from(event.target.files);

            if (!files.length) {
                return;
            }

            // add new instead of replacing existing files
            const remainingSlots = maxImages - selectedFiles.length;

            if (files.length > remainingSlots) {
                alert(`Maximum of ${maxImages} images!`);

                selectedFiles.push(...files.slice(0, remainingSlots));
            } else {
                selectedFiles.push(...files);
            }

            // first image is always primary
            if (selectedFiles.length > 0 && primaryIndex >= selectedFiles.length) {
                primaryIndex = 0;
            }

            // reset input first
            event.target.value = '';

            // then put selected files back into the input
            updateInputFiles();
            renderPreview();
        }


        function renderPreview() {
            const preview = document.getElementById('preview');
            if (!preview) return;
            preview.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'relative group';
                const imageUrl = URL.createObjectURL(file);
                div.innerHTML = `
            <div class="relative">
                <div class="w-full h-28 rounded-lg overflow-hidden
                            bg-slate-200
                            ${index === primaryIndex
                                ? 'border-2 border-blue-500'
                                : 'border border-slate-200'}">
                    <img
                        src="${imageUrl}"
                        alt="${file.name}"
                        class="w-full h-full object-cover"
                    >
                </div>

                <button
                    type="button"
                    onclick="setPrimary(${index})"
                    class="absolute top-1.5 left-1.5
                           w-6 h-6 rounded-full
                           flex items-center justify-center
                           shadow
                           ${
                               index === primaryIndex
                                   ? 'bg-amber-400 text-white'
                                   : 'bg-white/90 text-slate-400 hover:text-amber-500 opacity-0 group-hover:opacity-100'
                           }
                           transition-all"
                    title="${index === primaryIndex ? 'Primary photo' : 'Set as primary'}"
                >
                    <i class="bx bx-star text-sm"></i>
                </button>

                <button
                    type="button"
                    onclick="removeImage(${index})"
                    class="absolute top-1.5 right-1.5
                           w-6 h-6 rounded-full
                           bg-white/90
                           text-slate-500
                           hover:text-red-600
                           flex items-center justify-center
                           shadow
                           opacity-0
                           group-hover:opacity-100
                           transition-opacity"
                    title="Remove photo"
                >
                    <i class="bx bx-x text-sm"></i>
                </button>
            </div>

            <p class="mt-1 text-[11px] text-slate-500 truncate">
                ${file.name}
            </p>
        `;
                preview.appendChild(div);
            });

            // Add Photo – only needs to be called once
            addPhotoButton();
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);

            // Keep primary valid
            if (selectedFiles.length === 0) {
                primaryIndex = 0;
            } else {
                primaryIndex = 0;
            }

            updateInputFiles();
            renderPreview();
        }

        function addPhotoButton() {
            const preview = document.getElementById('preview');

            if (!preview) return;

            // remove old and add photo
            const oldButton = preview.querySelector('[data-add-photo]');

            if (oldButton) {
                oldButton.remove();
            }
            // maximum reached
            if (selectedFiles.length >= maxImages) {
                return;
            }
            const label = document.createElement('label');
            label.setAttribute('for', 'imageInput');
            label.setAttribute('data-add-photo', 'true');
            label.className =
                `w-full h-28 rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 hover:border-slate-400 hover:text-slate-500 cursor-pointer transition-colors `;
            label.innerHTML = `
            <i class="bx bx-plus text-xl"></i>
            <span class="text-xs mt-1">Add photo</span>
        `;
            preview.appendChild(label);
        }

        function setPrimary(index) {
            if (!selectedFiles[index]) return;
            // move selected image to first position
            const [file] = selectedFiles.splice(index, 1);
            selectedFiles.unshift(file);
            // First image is primary
            primaryIndex = 0;

            updateInputFiles();
            renderPreview();
        }


        function updateInputFiles() {
            const input = document.getElementById('imageInput');
            if (!input) return;
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }

        // Hellper toggle section (Without Confirmation Alert)
        function setProductTypeSection(type) {
            const singleSection = document.getElementById('single-product-section');
            const variantSection = document.getElementById('variant-product-section');

            const singleInputs = singleSection.querySelectorAll('input');
            const variantInputs = variantSection.querySelectorAll('input');

            if (type === 'single') {
                singleSection.classList.remove('hidden');
                variantSection.classList.add('hidden');

                singleInputs.forEach(input => input.disabled = false);
                variantInputs.forEach(input => input.disabled = true);
            } else {
                singleSection.classList.add('hidden');
                variantSection.classList.remove('hidden');

                singleInputs.forEach(input => input.disabled = true);
                variantInputs.forEach(input => input.disabled = false);
            }
        }

        function updateProductTypeSlider(type) {
            const slider = document.getElementById('product-type-slider');

            if (!slider) return;

            if (type === 'variant') {
                slider.style.transform = 'translateX(100%)';
            } else {
                slider.style.transform = 'translateX(0)';
            }
        }

        //  product type change
        function toggleProductType(event) {
            const newType = event.target.value;

            if (newType === 'single') {
                const hasVariant = Object.keys(variantState).length > 0 ||
                    getVariantOptions().some(option => option.name !== '' || option.values.length > 0);

                if (hasVariant) {
                    const confirmSwitch = confirm(
                        'Data Variant sudah dibuat.\n\n' +
                        'Jika pindah ke single product, data variant akan dikoksongkan. \n\n' + 'lanjutkan?'
                    );

                    if (!confirmSwitch) {
                        document.querySelector('input[name="product_type"][value="variant"]').checked = true;
                        return;
                    }

                    // clear the variant
                    variantState = {};
                    document.getElementById('variant-list').innerHTML = '';
                    document.getElementById('variant-list-section').classList.add('hidden');
                    // emty the option
                    document.getElementById('options-container').innerHTML = '';
                    // create another emty option
                    addOption();
                }

            } else {
                const sku = document.querySelector('input[name="sku"]').value.trim();
                const price = document.querySelector('input[name="price"]').value.trim();
                const stock = document.querySelector('input[name="stock"]').value.trim();
                const weight = document.querySelector('input[name="weight"]').value.trim();

                const hasSingle = sku !== '' || price !== '' || stock !== '' || Number(stock) > 0 || weight !== '';

                if (hasSingle) {
                    const confirmSwitch = confirm(
                        'Data single product sudah di isi. \n\n' +
                        'Jika pindah ke product with Variants, data Single Product akan dikosongkan. \n\n' + 'lanjtkan?'
                    );

                    if (!confirmSwitch) {
                        document.querySelector('input[name="product_type"][value="single"]').checked = true;
                        return;
                    }
                    // clear the single
                    document.querySelector('input[name="sku"]').value = '';
                    document.querySelector('input[name="price"]').value = '';
                    document.querySelector('input[name="stock"]').value = '';
                    document.querySelector('input[name="weight"]').value = '';
                }
            }
            // existing section logic
            setProductTypeSection(newType);
            // update slading toggle
            updateProductTypeSlider(newType);
        }

        const availabilitySelect = document.querySelector(
            'select[name="availability"]'
        );
        const preorderReleaseWrapper = document.getElementById(
            'preorder-release-wrapper'
        );
        const preorderReleaseInput = document.getElementById(
            'preorder-release-label'
        );

        function updatePreorderReleaseLabel() {
            const isPreorder = availabilitySelect.value === 'preorder';

            preorderReleaseWrapper.classList.toggle('hidden', !isPreorder);
            preorderReleaseInput.disabled = !isPreorder;
        }

        availabilitySelect.addEventListener(
            'change',
            updatePreorderReleaseLabel
        );

        updatePreorderReleaseLabel();

        document.addEventListener('DOMContentLoaded', () => {
            restoreOldData();
            updateSinglePreorderLimit();

            document.getElementById('product-form').addEventListener('submit', function(event) {
                const type = document.querySelector(
                    'input[name="product_type"]:checked'
                ).value;

                if (type === 'variant') {
                    const duplicateSku = document.querySelector(
                        '.variant-input[data-field="sku"].border-red-500'
                    );

                    if (duplicateSku) {
                        event.preventDefault();
                        alert('Variant SKUs must be unique');
                        duplicateSku.focus();
                        return;
                    }

                    // Make sure the shared Compare Price is submitted.
                    const comparePriceInput = document.getElementById('compare_price');

                    if (comparePriceInput) {
                        comparePriceInput.disabled = false;
                    }

                    // Submit variant data through hidden inputs.
                    syncVariantInputs();
                }
            });
        });

        // l egacy data Restoration
        function restoreOldData() {
            // 1 set Radio product type
            const activeRadio = document.querySelector(`input[name="product_type"][value="${oldProductType}"]`);

            if (activeRadio) {
                activeRadio.checked = true;

                setProductTypeSection(oldProductType);
                updateProductTypeSlider(oldProductType);
            }

            // 2. Restore options & values
            if (Array.isArray(oldOptions) && oldOptions.length > 0) {
                const container = document.getElementById('options-container');
                container.innerHTML = '';
                oldOptions.forEach((option, optIdx) => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'option-item border border-slate-200 rounded p-4 mb-4 shadow-sm';
                    const useImages = option.use_images == 1;
                    let valuesHTML = '';
                    const values = Array.isArray(option.values) ? option.values : [];
                    values.forEach((val) => {
                        valuesHTML += `
                        <div class="values-item flex gap-2 mb-2 items-center">
                            <input type="text" name="options[${optIdx}][values][]" value="${escapeHtml(val)}" placeholder="value name (e.g. blue, M...)"
                            class="option-value-input border border-slate-200 p-2 flex-1 rounded-md focus:outline-none focus:border-slate-400" required>
                            <input type="file" name="option_images[${optIdx}][]"
                            accept="image/*" onchange="previewOptionImage(this)"
                            class="option-image-input hidden border border-slate-200 p-2 rounded-md">
                            <img src="" alt="Option image preview"
                            class="option-image-preview hidden w-10 h-10 rounded object-cover border border-slate-200 shrink-0">
                            <button type="button" onclick="removeValue(this)" class="rounded-xl hover:text-red-600 text-gray-400" title="Remove Value">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        `;
                    })
                    optionDiv.innerHTML = `
                    <div class="flex items-center justify-between">
                        <label class="block mb-3">Name Option : </label>
                        <button type="button" onclick="removeOption(this)" class=" rounded-lg text-gray-400 hover:text-red-600 " title="Remove Option">
                            <i class="bx bx-trash text-xl"></i>
                        </button>
                    </div>
                    <div class="flex gap-2 mb-3">
                        <input type="text" name="options[${optIdx}][name]" value="${escapeHtml(option.name || '')}" placeholder="option name (e.g. color,size...)" class="option-name-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400" required>
                    </div>
                    <label class="flex items-center gap-2 mb-3">
                        <input type="checkbox" name="options[${optIdx}][use_images]" value="1" class="use-images-checkbox" onchange="toggleOptionImages(this)" ${useImages ? 'checked' : ''}>
                        <span>Use image for this option</span>
                    </label>
                    <label class="block mb-2">Values : </label>
                    <div class="values-container">
                        ${valuesHTML}
                    </div>
                    <button type="button" onclick="addValue(this)" class="text-blue-600 text-sm cursor-pointer">+ Add Value</button>
                    `
                    container.appendChild(optionDiv);
                    const checkbox = optionDiv.querySelector('.use-images-checkbox');
                    if (checkbox.checked) {
                        toggleOptionImagesInputs(checkbox);
                    }

                });
                updateAddOptionButton();
            }
            // 3. Restore generated Variants State
            const variantList = Array.isArray(oldVariants) ? oldVariants : Object.values(oldVariants || {});
            if (variantList.length > 0) {
                variantState = {};
                variantList.forEach(variant => {
                    if (variant && variant.values) {
                        const key = createVariantKey(variant.values);
                        variantState[key] = {
                            values: variant.values,
                            sku: variant.sku || '',
                            price: variant.price || '',
                            stock: variant.stock ?? '',
                            weight: variant.weight ?? '',
                            max_purchase_per_order: variant.max_purchase_per_order ?? '',
                            preorder_limit: variant.preorder_limit ?? '',
                            is_active: variant.is_active === undefined ?
                                true : Boolean(Number(variant.is_active))
                        };
                    }
                });
                renderVariantList();
                document.getElementById('variant-list-section').classList.remove('hidden');
            }
        }

        function getOptionItems() {
            return Array.from(document.getElementById('options-container').children)
                .filter(element => element.classList.contains('option-item'));
        }

        //==== Add value ===
        function addValue(button) {
            const optionItem = button.closest('.option-item');
            const valuesContainer = optionItem.querySelector('.values-container');

            // get all options that actually exist
            const optionItems = getOptionItems();
            const optionIndex = optionItems.indexOf(optionItem);
            const checkbox = optionItem.querySelector('.use-images-checkbox');

            const valueDiv = document.createElement('div');
            valueDiv.className = 'values-item flex gap-2 mb-2 items-center';
            valueDiv.innerHTML = `
            <input type="text" name="options[${optionIndex}][values][]" placeholder="value name (e.g. blue,m...)" class="option-value-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400" required>

            <input type="file" name="option_images[${optionIndex}][]" accept="image/*" onchange="previewOptionImage(this)" class="option-image-input ${checkbox.checked ? '' : 'hidden'} border border-slate-200 p-2">

            <img src="" alt="Option image preview" class="option-image-preview hidden w-10 h-10 rounded object-cover border border-slate-200 shrink-0">
        <button
            type="button"
            onclick="removeValue(this)"
            class="rounded-xl hover:text-red-600 text-gray-400"
            title="Remove Value">
            <i class="bx bx-x text-2xl"></i>
        </button>`;
            valuesContainer.appendChild(valueDiv);
        }

        function removeValue(button) {
            const valueItem = button.closest('.values-item');
            if (!valueItem) return;
            const preview = valueItem.querySelector('.option-image-preview');
            if (preview?.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
            }

            valueItem.remove();
        }

        //==== Add Option ===
        function addOption() {
            const container = document.getElementById('options-container');
            const optionCount = getOptionItems().length;

            if (optionCount >= maxOptions) {
                return;
            }
            const optionIndex = optionCount;
            const optionDiv = document.createElement('div');

            optionDiv.className = 'option-item border border-slate-200 rounded p-4 mb-4 shadow-sm';

            optionDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <label class="block mb-3">Name Option : </label>
                <button type="button" onclick="removeOption(this)" class=" rounded-lg text-gray-400 hover:text-red-600 " title="Remove Option">
                    <i class="bx bx-trash text-xl"></i>
                </button>
            </div>
            <div class="flex gap-2 mb-3">
                <input type="text" name="options[${optionIndex}][name]" placeholder="option name (e.g. color,size..)" class="option-name-input border border-slate-200 p-2 w-full rounded-md" required>
            </div>

            <label class="flex items-center gap-2 mb-3">
                <input
                    type="checkbox"
                    name="options[${optionIndex}][use_images]"
                    value="1"
                    class="use-images-checkbox"
                    onchange="toggleOptionImages(this)">
                <span>Use image for this option</span>
            </label>

            <label class="block mb-2">Values</label>
            <div class="values-container">
                <div class="values-item flex gap-2 mb-2 items-center">
                    <input type="text" name="options[${optionIndex}][values][]" placeholder="value name (e.g. blue,m...)"
                     class="option-value-input border border-slate-200 p-2 w-full rounded-md focus:outline-none focus:border-slate-400" required>
                     <input type="file" name="option_images[${optionIndex}][]" accept="image/*" onchange="previewOptionImage(this)" class="option-image-input hidden border border-slate-200 p-2">
                     <img src="" alt="Option image preview" class="option-image-preview hidden w-10 h-10 rounded object-cover border border-slate-200 shrink-0">
                     <button type="button" onclick="removeValue(this)" class="rounded-xl hover:text-red-600 text-gray-400" title="Remove Value" >
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </div>
                <button type="button" onclick="addValue(this)"class="text-blue-600 text-sm">
                    +Add Value
                </button>
            `;
            container.appendChild(optionDiv);
            updateAddOptionButton();

        }

        function toggleOptionImages(checkbox) {
            if (checkbox.checked) {
                // uncheck the checkbox for the other option
                document.querySelectorAll('.use-images-checkbox').forEach(otherCheckbox => {
                    if (otherCheckbox !== checkbox) {
                        otherCheckbox.checked = false;
                        toggleOptionImagesInputs(otherCheckbox);
                    }
                });
            }
            toggleOptionImagesInputs(checkbox);
        }

        function toggleOptionImagesInputs(checkbox) {
            const optionItem = checkbox.closest('.option-item');
            const imageInputs = optionItem.querySelectorAll('.option-image-input');

            imageInputs.forEach(input => {
                const preview = input.closest('.values-item')
                    ?.querySelector('.option-image-preview');

                if (checkbox.checked) {
                    input.classList.remove('hidden');
                } else {
                    input.classList.add('hidden');
                    input.value = '';

                    if (preview) {
                        if (preview.dataset.objectUrl) {
                            URL.revokeObjectURL(preview.dataset.objectUrl);
                            delete preview.dataset.objectUrl;
                        }

                        preview.removeAttribute('src');
                        preview.classList.add('hidden');
                    }
                }
            });
        }

        function removeOption(button) {
            const optionItem = button.closest('.option-item');

            if (!optionItem) return;

            optionItem.querySelectorAll('.option-image-preview').forEach(preview => {
                if (preview.dataset.objectUrl) {
                    URL.revokeObjectURL(preview.dataset.objectUrl);
                }
            });

            optionItem.remove();
            reindexOptions();
            updateAddOptionButton();
        }

        // update add option button
        function updateAddOptionButton() {
            // const container = document.getElementById('options-container');
            const button = document.getElementById('add-option-btn');
            if (!button) return;
            const optionCount = getOptionItems().length;

            button.classList.toggle(
                'hidden',
                optionCount >= maxOptions
            );
        }

        function reindexOptions() {
            getOptionItems().forEach((optionItem, optionIndex) => {
                const nameInput = optionItem.querySelector('.option-name-input');

                if (nameInput) nameInput.name = `options[${optionIndex}][name]`;
                // options values
                optionItem.querySelectorAll('.option-value-input').forEach(input => {
                    input.name = `options[${optionIndex}][values][]`;
                });
                // options images
                optionItem.querySelectorAll('.option-image-input').forEach(input => {
                    input.name = `option_images[${optionIndex}][]`;
                });
                const useImagesCheckbox = optionItem.querySelector('.use-images-checkbox');
                if (useImagesCheckbox) {
                    useImagesCheckbox.name = `options[${optionIndex}][use_images]`
                }

            });
        }
        // getVariantOptions
        function getVariantOptions() {
            return getOptionItems().map(optionItem => {
                const nameInput = optionItem.querySelector('.option-name-input');
                const values = Array.from(optionItem.querySelectorAll('.option-value-input'))
                    .map(input => input.value.trim())
                    .filter(value => value !== '');

                return {
                    name: nameInput ? nameInput.value.trim() : '',
                    values: values
                };
            });
        }
        // generate combination
        function generateCombinations(options) {
            let combinations = [
                []
            ];

            options.forEach(option => {
                const newCombinations = [];

                option.values.forEach(value => {
                    combinations.forEach(combination => {
                        newCombinations.push([
                            ...combination,
                            value
                        ]);
                    });
                });
                combinations = newCombinations;
            });
            return combinations;
        }

        // key variants
        function createVariantKey(values) {
            return values.join('|');
        }

        function generateVariants() {
            const options = getVariantOptions();

            if (options.length === 0) {
                alert('Add at least one option.');
                return;
            }

            for (const option of options) {
                if (!option.name) {
                    alert('The option name has not been filled in.');
                    return;
                }
                if (option.values.length === 0) {
                    alert(`Option "${option.name}" does not yet have value.`);
                    return;
                }
            }

            const combinations = generateCombinations(options);
            const newState = {};

            combinations.forEach(values => {
                const key = createVariantKey(values);
                // when the variant has already existed
                // Retrieve the historical data
                if (variantState[key]) {

                    newState[key] = {
                        ...variantState[key],
                        values: values
                    };
                } else {
                    // new variant
                    newState[key] = {
                        values: values,
                        sku: '',
                        price: '',
                        stock: '',
                        weight: '',
                        max_purchase_per_order: '',
                        preorder_limit: '',
                        is_active: true,
                    };
                }
            });
            // make sure at least one variant remains active
            const newKeys = Object.keys(newState);
            if (newKeys.length > 0 && !Object.values(newState).some(variant => variant.is_active)) {
                newState[newKeys[0]].is_active = true;
            }

            variantState = newState;

            renderVariantList();

            document.getElementById('variant-list-section').classList.remove('hidden');
        }
        //  escapeHtml
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function activeVariantCount() {
            return Object.values(variantState).filter(variant => variant.is_active).length;
        }

        function toggleVariantActive(key) {
            const variant = variantState[key];

            if (!variant) {
                return;
            }
            const activeCount = activeVariantCount();

            // Don't allow the last active variant to become inactive
            if (variant.is_active && activeCount === 1) {
                return;
            }

            variant.is_active = !variant.is_active;

            renderVariantList();
        }

        function renderVariantList() {
            const variantList = document.getElementById('variant-list');
            const availability = document.querySelector(
                'select[name="availability"]'
            )?.value || 'ready';

            variantList.innerHTML = '';

            const comparePriceInput = document.getElementById('compare_price');
            const sharedComparePrice = comparePriceInput?.value ?? '';

            Object.entries(variantState).forEach(([key, variant]) => {
                const card = document.createElement('div');

                card.className = 'border border-slate-200 rounded-lg bg-white p-4 shadow';

                const variantText = variant.values
                    .map(value => escapeHtml(value))
                    .join(' / ');

                const priceDigits = String(variant.price ?? '').replace(/\D/g, '');
                const formattedPrice = priceDigits ?
                    new Intl.NumberFormat('id-ID').format(Number(priceDigits)) :
                    '';

                const comparePriceDigits = String(sharedComparePrice).replace(/\D/g, '');
                const formattedComparePrice = comparePriceDigits ?
                    new Intl.NumberFormat('id-ID').format(Number(comparePriceDigits)) :
                    '';

                const stock = variant.stock ?? '';

                const showPreorderLimit =
                    availability === 'preorder' &&
                    (stock === '' || Number(stock) === 0);

                if (!showPreorderLimit) {
                    variant.preorder_limit = '';
                }

                const isOnlyActive =
                    variant.is_active && activeVariantCount() === 1;

                const secondRowColumns = showPreorderLimit ?
                    'grid-cols-4' :
                    'grid-cols-3';

                card.innerHTML = `
            <div class="flex gap-4 items-start">

                <div class="w-10 min-w-[50px] pt-2">
                    <div class="font-semibold text-slate-800">
                        ${variantText}
                    </div>
                </div>

                <div class="flex-1 min-w-0">

                    <div class="grid grid-cols-4 gap-3">

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                SKU
                            </span>
                            <input
                                type="text"
                                value="${escapeHtml(variant.sku ?? '')}"
                                class="variant-input border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400"
                                data-key="${escapeHtml(key)}"
                                data-field="sku"
                                placeholder="SKU"
                                oninput="updateVariantState(this)">
                        </label>

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Price
                            </span>
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">
                                    Rp
                                </span>
                                <input
                                    type="text"
                                    value="${escapeHtml(formattedPrice)}"
                                    class="variant-input border border-slate-200 rounded-md p-2 pl-8 w-full min-w-0 outline-none focus:border-slate-400"
                                    data-key="${escapeHtml(key)}"
                                    data-field="price"
                                    inputmode="numeric"
                                    placeholder="0"
                                    oninput="RupiahFormat(this); updateVariantState(this)">
                            </div>
                        </label>

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Stock
                            </span>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                value="${escapeHtml(stock)}"
                                class="variant-input border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400"
                                data-key="${escapeHtml(key)}"
                                data-field="stock"
                                placeholder="0"
                                oninput="updateVariantState(this)"
                                onchange="renderVariantList(); syncVariantInputs()">
                        </label>

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Weight (kg)
                            </span>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                value="${escapeHtml(variant.weight ?? '')}"
                                class="variant-input border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400"
                                data-key="${escapeHtml(key)}"
                                data-field="weight"
                                placeholder="0"
                                oninput="updateVariantState(this)">
                        </label>

                    </div>

                    <div class="grid ${secondRowColumns} gap-3 mt-3 items-end">

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Price before discount <span class="text-xs text-slate-400 ">/Optional.</span>
                            </span>
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">
                                    Rp
                                </span>
                                <input
                                    type="text"
                                    value="${escapeHtml(formattedComparePrice)}"
                                    class="shared-compare-price border border-slate-200 rounded-md p-2 pl-8 w-full min-w-0 outline-none focus:border-slate-400"
                                    inputmode="numeric"
                                    placeholder="0"
                                    oninput="RupiahFormat(this); updateSharedComparePrice(this)">
                            </div>
                        </label>

                        <label class="block min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Max Purchase per Order <span class="text-xs text-slate-400 ">/Optional.</span>
                            </span>
                            <input
                                type="number"
                                min="1"
                                value="${escapeHtml(variant.max_purchase_per_order ?? '')}"
                                class="variant-input border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400"
                                data-key="${escapeHtml(key)}"
                                data-field="max_purchase_per_order"
                                placeholder="No limit"
                                oninput="updateVariantState(this)">
                        </label>

                       ${
                        showPreorderLimit
                        ? `
                            <label class="block min-w-0">
                                <span class="block text-xs font-medium text-slate-600 mb-1">
                                    Pre-order Limit<span class="text-xs text-slate-400">/ Optional</span>
                                    </span>
                                        <input type="number" min="1" value="${escapeHtml(variant.preorder_limit ?? '')}"
                                            class="variant-input border border-slate-200 rounded-md p-2 w-full min-w-0 outline-none focus:border-slate-400"
                                            data-key="${escapeHtml(key)}"
                                            data-field="preorder_limit"
                                            placeholder="Enter pre-order limit"
                                            oninput="updateVariantState(this)">
                            </label>` : ''
                        }

                        <div class="min-w-0">
                            <span class="block text-xs font-medium text-slate-600 mb-1">
                                Status
                            </span>

                            <div class="flex items-center gap-2 h-[38px]">
                                <button
                                    type="button"
                                    data-key="${escapeHtml(key)}"
                                    onclick="toggleVariantActive(this.dataset.key)"
                                    ${isOnlyActive ? 'disabled' : ''}
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition
                                        ${variant.is_active ? 'bg-green-500' : 'bg-slate-300'}
                                        ${isOnlyActive ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'}">

                                    <span class="inline-block w-4 h-4 transform bg-white transition rounded-full
                                        ${variant.is_active ? 'translate-x-6' : 'translate-x-1'}">
                                    </span>
                                </button>

                                <span class="text-sm whitespace-nowrap
                                    ${variant.is_active ? 'text-green-600' : 'text-slate-500'}">
                                    ${variant.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        `;

                variantList.appendChild(card);
            });
        }

        function updateVariantState(input) {
            const key = input.dataset.key;
            const field = input.dataset.field;

            if (variantState[key]) {
                variantState[key][field] = input.value;
            }

            if (field === 'sku') {
                validateDuplicateSkus();
            }
        }

        function updateSharedComparePrice(input) {
            const comparePriceInput = document.getElementById('compare_price');
            // Keep the original product-level input synchronized.
            if (comparePriceInput) {
                comparePriceInput.value = input.value;
            }

            // Update Compare Price in every variant row.
            document.querySelectorAll('.shared-compare-price').forEach(field => {
                if (field !== input) {
                    field.value = input.value;
                }
            });
        }

        function syncVariantInputs() {
            const container = document.getElementById('variant-hidden-inputs');
            container.innerHTML = '';

            Object.entries(variantState).forEach(([key, variant], index) => {
                variant.values.forEach((value, valueIndex) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `variants[${index}][values][${valueIndex}]`;
                    input.value = value;
                    container.appendChild(input);
                });

                const fields = ['sku', 'price', 'stock', 'weight', 'max_purchase_per_order', 'preorder_limit',
                    'is_active'
                ];

                fields.forEach(field => {
                    const input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = `variants[${index}][${field}]`;
                    if (field === 'is_active') {
                        input.value = variant.is_active ? 1 : 0;
                    } else {
                        input.value = variant[field] ?? '';

                    }

                    container.appendChild(input);
                });
            });
        }

        function validateDuplicateSkus() {
            const skuInputs = document.querySelectorAll(
                '.variant-input[data-field="sku"]'
            );

            const skuMap = {};

            //Reset all input Sku to normal
            skuInputs.forEach(input => {
                input.classList.remove(
                    'border-red-500',
                    'text-red-500',
                    'bg-red-50',
                );

                input.classList.add('border-slate-200');

                const sku = input.value.trim().toLowerCase();

                if (!sku) return;

                if (!skuMap[sku]) {
                    skuMap[sku] = [];
                }

                skuMap[sku].push(input);
            });

            // Mark duplicate SKUS
            Object.values(skuMap).forEach(inputs => {
                if (inputs.length > 1) {
                    inputs.forEach(input => {
                        input.classList.remove('border-slate-200');
                        input.classList.add(
                            'border-red-500',
                            'text-red-500',
                            'bg-red-50',
                        );
                    });
                }
            });
        }

        function updateSinglePreorderLimit() {
            const availability = document.querySelector(
                '[name="availability"]'
            ).value;

            const stock = document.getElementById('single-stock').value;

            const wrapper = document.getElementById(
                'single-preorder-limit-wrapper'
            );

            const input = wrapper.querySelector(
                '[name="preorder_limit"]'
            );

            const showPreorderLimit =
                availability === 'preorder' &&
                (stock === '' || Number(stock) === 0);

            wrapper.classList.toggle('hidden', !showPreorderLimit);
            input.disabled = !showPreorderLimit;
        }

        function previewOptionImage(input) {
            const preview = input.closest('.values-item')
                ?.querySelector('.option-image-preview');

            if (!preview) return;

            const file = input.files?.[0];

            // Remove the previous preview URL.
            if (preview.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
                delete preview.dataset.objectUrl;
            }

            if (!file || !file.type.startsWith('image/')) {
                preview.removeAttribute('src');
                preview.classList.add('hidden');
                return;
            }
            const imageUrl = URL.createObjectURL(file);
            preview.src = imageUrl;
            preview.dataset.objectUrl = imageUrl;
            preview.classList.remove('hidden');
        }
    </script>
@endsection

{{-- For the product creation feature,
I deliberately used native JS so I could deepen my understanding of it a bit. --}}
