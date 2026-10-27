@extends('layouts.layout_customer')
@section('title' ,'Address')

@section('ContentCustomer')
    <div x-data="{
        openModal: false,
        openModalEdit: false,
        editData: {},

        cities: [],
        districts: [],
        villages: [],
        editRequestId: 0,
        loadingRegions: false,

        deleteOpen: false,
        deleteData: {},
        deleteAction: '',

        openDelete(address, action) {
            this.deleteData = address;
            this.deleteAction = action;
            this.deleteOpen = true;
        },

        closeDelete() {
            this.deleteOpen = false;
            this.deleteData = {};
            this.deleteAction = '';
        },

        resetAddAddress() {
            this.openModal = false;

            const form = this.$refs.addAddressForm;

            if (!form) return;

            form.reset();
            form.elements.namedItem('city')?.replaceChildren(
                new Option('Choose City / Regency', '')
            );

            form.elements.namedItem('district')?.replaceChildren(
                new Option('Choose District', '')
            );

            form.elements.namedItem('village')?.replaceChildren(
                new Option('Choose Village / Subdistrict', '')
            );
        },

        async openEdit(address) {
            const requestId = ++this.editRequestId;

            const provinceCode = String(address.province ?? '');
            const cityCode = String(address.city ?? '');
            const districtCode = String(address.district ?? '');
            const villageCode = String(address.village ?? '');

            // Show the modal immediately.
            this.editData = {
                ...address,
                province: provinceCode,
                city: cityCode,
                district: districtCode,
                village: villageCode
            };

            this.cities = [];
            this.districts = [];
            this.villages = [];

            this.loadingRegions = true;
            this.openModalEdit = true;

            try {
                const [cities, districts, villages] = await Promise.all([
                    provinceCode ?
                    api('/get-cities/' + provinceCode) :
                    Promise.resolve([]),

                    cityCode ?
                    api('/get-districts/' + cityCode) :
                    Promise.resolve([]),

                    districtCode ?
                    api('/get-villages/' + districtCode) :
                    Promise.resolve([])
                ]);

                // Ignore results from an older edit request.
                if (requestId !== this.editRequestId) return;

                this.cities = Array.isArray(cities) ? cities : [];
                this.districts = Array.isArray(districts) ? districts : [];
                this.villages = Array.isArray(villages) ? villages : [];

                // Wait for the dropdown options to render.
                await this.$nextTick();

                // Reapply saved codes so the correct options stay selected.
                this.editData = {
                    ...this.editData,
                    province: provinceCode,
                    city: cityCode,
                    district: districtCode,
                    village: villageCode
                };

            } catch (error) {
                if (requestId !== this.editRequestId) return;

                console.error('Failed to load address regions:', error);
            } finally {
                if (requestId === this.editRequestId) {
                    this.loadingRegions = false;
                }
            }
        }
    }"
        class="w-full max-w-7xl mx-auto px-3 py-4 sm:px-6 sm:py-6 flex flex-col lg:flex-row gap-4 sm:gap-5">

        @include('customer.components.account_sidebar')

        <main class="w-full min-w-0 flex-1 bg-white p-3 sm:p-5 rounded-xl shadow-sm border border-slate-100">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 sm:mb-8 border-b border-slate-200 pb-3">

                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold">
                            Manage Address
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Manage your address
                        </p>
                    </div>

                    <button type="button" @click="openModal = true"
                        class="inline-flex shrink-0 items-center gap-1.5 px-3 py-2 bg-blue-500 text-white hover:bg-blue-600 rounded-lg text-sm sm:text-base">
                        <i class="bx bx-plus"></i>
                        Add Address
                    </button>
                </div>
                @if ($addresses->count())
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4  py-2 rounded">
                        @foreach ($addresses as $address)
                            <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col">
                                <div class="border-b border-slate-200 py-2">
                                    <div class="font-semibold">
                                        {{ $address->receiver_name }}
                                        <span class="text-gray-500">| {{ $address->phone }}</span>
                                        @if ($address->is_default)
                                            <span class=" text-xs bg-blue-100 text-blue-700 py-1 px-2 font-bold rounded">
                                                Main Address
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-gray-700 mt-2">
                                        {{ $address->full_address }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ collect([
                                            $villageNames->get($address->village),
                                            $districtNames->get($address->district),
                                            $cityNames->get($address->city),
                                            $provinceNames->get($address->province),
                                        ])->filter()->implode(', ') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $address->postal_code }}
                                    </div>
                                </div>
                                <div class="flex flex-col p-1 border-t- border-slate-200">
                                    <div class="flex gap-3">

                                        <button @click="openEdit(@js($address))"
                                            class="text-yellow-500 cursor-pointer">
                                            <i class="bx bx-edit text-lg mt-1" title="Edit"></i>
                                        </button>
                                        <form action="{{ route('customer.Address.delete', $address->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                @click="openDelete(@js(['receiver_name' => $address->receiver_name,'phone' => $address->phone,'is_default' => (bool) $address->is_default,'full_address' => $address->full_address,'region' => collect([$villageNames->get($address->village), $districtNames->get($address->district), $cityNames->get($address->city), $provinceNames->get($address->province)])->filter()->implode(', '),'postal_code' => $address->postal_code,]),

                                                @js(route('customer.Address.delete', $address->id)))"
                                                class="text-red-700 hover:text-red-800 cursor-pointer"
                                                aria-label="Delete address" title="Delete Address">
                                                <i class="bx bx-trash text-lg mt-1"></i>
                                            </button>
                                        </form>
                                        @if (!$address->is_default)
                                            <form action="{{ route('customer.Address.default', $address->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button class="font-medium  text-sm text-gray-500">
                                                    <span class="text-blue-600 hover:blue-700 cursor-pointer ml-1">
                                                        Set as Main Address
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="col-span-full flex items-center justify-center py-20">
                        <p class="font-semibold text-center">The address is still blank...</p>
                    </div>
                @endif
            </div>

            {{-- Modal Add Address --}}
            <div x-cloak x-show="openModal" x-transition.opacity x-on:click.self="resetAddAddress()"
                x-on:keydown.escape.window="if (openModal) resetAddAddress()"
                class="fixed inset-0 z-[100] flex items-start sm:items-center justify-center bg-black/50 p-3 sm:p-5 overflow-y-auto overscroll-contain">

                <div x-on:click.stop x-transition
                    class="w-full max-w-md max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] bg-white rounded-xl shadow-lg overflow-hidden flex flex-col my-auto">

                    <div
                        class="shrink-0 bg-white flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-6 sm:py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Add Address</h2>

                        <button type="button" x-on:click="resetAddAddress()"
                            class="flex items-center justify-center shrink-0 w-9 h-9 rounded-lg text-gray-500 hover:bg-slate-100 hover:text-gray-900 transition"
                            aria-label="Close add address modal">
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                        <div class="p-4 sm:p-6">
                            <form x-ref="addAddressForm" action="{{ route('customer.Address.store') }}" method="POST"
                                class="space-y-3">
                                @csrf

                                <div>
                                    <label for="receiver_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Receiver Name
                                    </label>

                                    <input type="text" name="receiver_name" id="receiver_name"
                                        placeholder="Enter receiver name" value="{{ auth()->user()->name }}"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-400">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number
                                    </label>

                                    <input type="text" name="phone" id="phone" placeholder="Enter phone number"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-400">
                                </div>

                                <div>
                                    <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Address
                                    </label>

                                    <textarea name="full_address" id="full_address" rows="4" placeholder="Enter your full address"
                                        class="w-full min-w-0 resize-y border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-400"></textarea>
                                </div>

                                <div>
                                    <label for="province" class="block text-sm font-medium text-gray-700 mb-1">
                                        Province
                                    </label>

                                    <select name="province" id="province"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:border-slate-400">
                                        <option value="">Choose Province</option>

                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->code }}">
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                                        City / Regency
                                    </label>

                                    <select id="city" name="city"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:border-slate-400">
                                        <option value="">Choose City / Regency</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="district" class="block text-sm font-medium text-gray-700 mb-1">
                                        District
                                    </label>

                                    <select id="district" name="district"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:border-slate-400">
                                        <option value="">Choose District</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="village" class="block text-sm font-medium text-gray-700 mb-1">
                                        Village / Subdistrict
                                    </label>

                                    <select id="village" name="village"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:border-slate-400">
                                        <option value="">Choose Village / Subdistrict</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                                        Postal Code
                                    </label>

                                    <input type="text" name="postal_code" id="postal_code"
                                        placeholder="Enter postal code"
                                        class="w-full min-w-0 border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-400">
                                </div>

                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full rounded-lg text-white px-4 py-3 cursor-pointer bg-blue-500 hover:bg-blue-600 font-semibold text-sm transition">
                                        Save Address
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Edit Address --}}
            <div x-cloak x-show="openModalEdit" x-transition.opacity x-on:click.self="openModalEdit = false"
                x-on:keydown.escape.window="openModalEdit = false"
                class="fixed inset-0 z-[100] flex items-start sm:items-center justify-center bg-black/50 p-3 sm:p-5 overflow-y-auto overscroll-contain">

                <div x-on:click.stop x-transition
                    class="w-full max-w-md max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] bg-white rounded-xl shadow-lg overflow-hidden flex flex-col my-auto">

                    <div
                        class="shrink-0 bg-white flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-6 sm:py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Edit Address
                        </h2>

                        <button type="button" x-on:click="openModalEdit = false"
                            class="flex items-center justify-center shrink-0 w-9 h-9 rounded-lg text-gray-500 hover:bg-slate-100 hover:text-gray-900 transition"
                            aria-label="Close edit address modal">
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                        <form :action="`/manage-account/update/${editData.id}`" method="POST"
                            class="p-4 sm:p-6 space-y-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <input type="text" name="receiver_name" x-model="editData.receiver_name"
                                    placeholder="Recipient's name"
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                            </div>

                            <div>
                                <input type="text" name="phone" x-model="editData.phone" placeholder="Phone number"
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                            </div>

                            <div>
                                <textarea name="full_address" rows="4" placeholder="Full address" x-model="editData.full_address"
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500"></textarea>
                            </div>

                            <div>
                                <select name="province" x-model="editData.province"
                                    @change=" cities = editData.province ? await api('/get-cities/' + editData.province) : [];
                                districts = [];
                                villages = [];

                                editData.city = '';
                                editData.district = '';
                                editData.village = '';
                                "
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                                    <option value="">Select Province</option>

                                    @foreach ($provinces as $province)
                                        <option value="{{ $province->code }}">
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <select name="city" x-model="editData.city"
                                    @change=" districts = editData.city ? await api('/get-districts/' + editData.city) : [];
                                    villages = [];
                                    editData.district = '';
                                    editData.village = '';
                        "
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                                    <option value="">Select City / Regency</option>
                                    <template x-for="city in cities" :key="city.code">
                                        <option :value="city.code" x-text="city.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <select name="district" x-model="editData.district"
                                    @change=" villages = editData.district ? await api('/get-villages/' + editData.district) : [];
                                    editData.village = '';
                        "
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                                    <option value="">Select District</option>

                                    <template x-for="district in districts" :key="district.code">
                                        <option :value="district.code" x-text="district.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <select name="village" x-model="editData.village"
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                                    <option value="">Select Village / Subdistrict</option>

                                    <template x-for="village in villages" :key="village.code">
                                        <option :value="village.code" x-text="village.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <input type="text" name="postal_code" x-model="editData.postal_code"
                                    placeholder="Postal code"
                                    class="border border-slate-300 p-2 rounded-lg w-full focus:outline-none focus:border-slate-500">
                            </div>

                            <div class="pt-2">
                                <button type="submit" :disabled="loadingRegions"
                                    class="w-full rounded-lg text-white p-2.5 cursor-pointer bg-yellow-400 hover:bg-yellow-500 transition font-medium disabled:opacity-50 disabled:cursor-wait">
                                    <span x-text="loadingRegions ? 'Loading...' : 'Save Changes'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- delete address modal --}}
            <div x-show="deleteOpen" x-cloak x-transition.opacity
                x-on:keydown.escape.window="if (deleteOpen) closeDelete()"
                class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 p-4">

                <div x-show="deleteOpen" x-transition.scale @click.outside="closeDelete()" role="dialog"
                    aria-modal="true" aria-labelledby="deleteAddressTitle"
                    class="w-full max-w-md rounded-2xl bg-white shadow-xl">

                    <div class="px-6 pt-5 pb-4 text-center">
                        <h2 id="deleteAddressTitle" class="text-lg font-semibold text-slate-900">Delete Address?</h2>

                        <p class="mt-2 text-sm text-slate-500">Are you sure you want to delete this address?</p>
                    </div>

                    <div class="px-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-semibold text-slate-900" x-text="deleteData.receiver_name"></span>

                                <span class="text-slate-300">|</span>

                                <span class="text-sm text-slate-600" x-text="deleteData.phone"></span>
                                <template x-if="deleteData.is_default">

                                    <span class=" rounded-md bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                                        Main Address
                                    </span>
                                </template>
                            </div>


                            <p x-show="deleteData.full_address" x-text="deleteData.full_address"
                                class="mt-3 break-words text-sm leading-5 text-slate-700">
                            </p>

                            <p x-show="deleteData.region" x-text="deleteData.region"
                                class="mt-1 break-words text-xs uppercase leading-5 text-slate-500">
                            </p>

                            <p x-show="deleteData.postal_code" x-text="deleteData.postal_code"
                                class="mt-1 text-xs text-slate-500">
                            </p>
                        </div>

                        <p class="mt-3 text-xs text-slate-400">This action cannot be undone. </p>
                    </div>
                    <div class="mt-4 flex gap-3 border-t border-slate-200 px-6 py-4">
                        <button type="button" @click="closeDelete()"
                            class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>

                        <form method="POST" :action="deleteAction" class="flex-1">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const api = async (url) => {
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            return response.json();
        };

        document.addEventListener('DOMContentLoaded', () => {
            const province = document.getElementById('province');
            const city = document.getElementById('city');
            const district = document.getElementById('district');
            const village = document.getElementById('village');

            // Stop if any required dropdown is missing
            if (!province || !city || !district || !village) {
                return;
            }

            const resetCity = () => {
                city.innerHTML =
                    '<option value="">Choose City / Regency</option>';
            };

            const resetDistrict = () => {
                district.innerHTML =
                    '<option value="">Choose District</option>';
            };

            const resetVillage = () => {
                village.innerHTML =
                    '<option value="">Choose Village / Subdistrict</option>';
            };

            // Load cities when the province changes.
            province.addEventListener('change', async function() {
                resetCity();
                resetDistrict();
                resetVillage();

                if (!this.value) return;

                city.innerHTML = '<option value="">Loading...</option>';

                try {
                    const data = await api('/get-cities/' + this.value);

                    city.innerHTML =
                        '<option value="">Choose City / Regency</option>';

                    data.forEach(item => {
                        city.add(new Option(item.name, item.code));
                    });
                } catch (error) {
                    console.error('Failed to load cities:', error);
                    city.innerHTML =
                        '<option value="">Failed to load cities</option>';
                }
            });

            city.addEventListener('change', async function() {
                resetDistrict();
                resetVillage();

                if (!this.value) return;

                district.innerHTML = '<option value="">Loading...</option>';

                try {
                    const data = await api('/get-districts/' + this.value);

                    district.innerHTML =
                        '<option value="">Choose District</option>';

                    data.forEach(item => {
                        district.add(new Option(item.name, item.code));
                    });
                } catch (error) {
                    console.error('Failed to load districts:', error);
                    district.innerHTML =
                        '<option value="">Failed to load districts</option>';
                }
            });

            district.addEventListener('change', async function() {
                resetVillage();

                if (!this.value) return;

                village.innerHTML = '<option value="">Loading...</option>';

                try {
                    const data = await api('/get-villages/' + this.value);

                    village.innerHTML =
                        '<option value="">Choose Village / Subdistrict</option>';

                    data.forEach(item => {
                        village.add(new Option(item.name, item.code));
                    });
                } catch (error) {
                    console.error('Failed to load villages:', error);
                    village.innerHTML =
                        '<option value="">Failed to load villages</option>';
                }
            });
        });
    </script>
@endsection
