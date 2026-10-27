<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="overflow-hidden">
    <script>
        function toggleDropdown(menuId, iconId) {
            const menu = document.getElementById(menuId);
            const icon = document.getElementById(iconId);

            menu.classList.toggle('open');

            if (menu.classList.contains('open')) {
                icon.classList.add('rotate-180');
            } else {
                icon.classList.remove('rotate-180');
            }

        }

        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        fetch('{{ route('timezone.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                timezone: timezone,
            }),
        });
    </script>
    <div x-data="{
        openSearch: false,
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('adminSidebarCollapsed') === 'true',

    }" x-init="$watch('sidebarCollapsed', value => {
        localStorage.setItem('adminSidebarCollapsed', value);
    })"
        @keydown.window.ctrl.k.prevent="
        if (!['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            openSearch = true;
            $nextTick(() => $refs.search.focus());
        }
    "
        @keydown.escape.window="sidebarOpen = false" class="flex h-dvh overflow-hidden bg-gray-100">

        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity
            class="fixed inset-0 z-40 bg-black/40 lg:hidden">
        </div>
        <aside x-cloak
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
            ]"
            class="fixed inset-y-0 left-0 z-50 flex h-dvh w-64 shrink-0 flex-col border-r border-slate-200 bg-white transform transition-all duration-300 lg:static lg:translate-x-0">
            <div class="mb-8 border-b border-gray-300 p-5" :class="sidebarCollapsed ? 'lg:px-2' : ''">
                <a href="{{ route('admin.dashboard') }}" :class="sidebarCollapsed ? 'lg:justify-center' : ''"
                    class="flex items-center gap-1">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-500 font-bold text-white">
                        Se
                    </div>

                    <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="flex flex-col">
                        <p class="text-lg font-semibold tracking-light">Seeika</p>
                        <p class="text-sm text-gray-400">Admin Dashboard</p>
                    </div>
                </a>
            </div>

            <nav :class="sidebarCollapsed ? 'lg:px-2' : 'lg:px-4'"
                class="flex-1 overflow-y-auto space-y-3 sidebar-scroll px-4">
                <p :class="sidebarCollapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-semibold text-slate-400 uppercase mb-4 px-3 tracking-wider">Overview
                </p>

                <a href="{{ route('admin.dashboard') }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                    class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                    <div class="flex items-center gap-3 py-1">
                        <i class='bx bx-dashboard text-lg'></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                            Dashboard
                        </span>
                    </div>
                </a>

                <div>
                    <button onclick="toggleDropdown('product-menu', 'product-icon')"
                        :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        class="w-full flex items-center justify-between px-3 py-2">
                        <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Ecommerce
                        </span>
                        <i id="product-icon"
                            class="bx bx-chevron-down text-blue-600 text-xl transition duration-300 rotate-180"></i>
                    </button>

                    <div class="space-y-2">
                        {{-- DropDown Content --}}
                        <div id="product-menu" class="dropdown-content open space-y-1">
                            <a href="{{ route('admin.products.index') }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="{{ request()->routeIs('admin.products*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">

                                <div class="flex items-center gap-3 py-1">
                                    <i class="bx bx-box text-lg"></i>
                                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                        Products
                                    </span>
                                </div>
                            </a>
                            <a href="{{ route('admin.categories.index') }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="{{ request()->routeIs('admin.categories.*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                                <div class="flex items-center gap-3 py-1">
                                    <i class="bx bx-categories text-lg"></i>
                                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                        Categories
                                    </span>
                                </div>
                            </a>
                            <a href="{{ route('admin.collections.index') }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="{{ request()->routeIs('admin.collections.*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                                <div class="flex items-center gap-3 py-1">
                                    <i class="bx bx-layers text-lg"></i>
                                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                        Collections
                                    </span>
                                </div>
                            </a>

                            <a href="{{ route('admin.orders.index') }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="{{ request()->routeIs('admin.orders.*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100 mb-1">
                                <div class="flex items-center gap-3 py-1">
                                    <i class="bx bx-cart text-lg"></i>
                                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                        Orders
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div>
                    <p :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        class="text-[10px] font-semibold text-slate-400 uppercase mb-4 px-3 tracking-wider">
                        Content Management System
                    </p>
                    <div class="space-y-2">
                        <a href="{{ route('admin.reviews.index') }}"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="{{ request()->routeIs('admin.reviews.*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                            <div class="flex items-center gap-3 py-1">
                                <i class="bx bx-message-circle-detail text-lg"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                    Reviews
                                </span>
                            </div>
                        </a>
                        <a href="#" :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                            <div class="flex items-center gap-3 py-1">
                                <i class="bx bx-user text-lg"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                    Users
                                </span>
                            </div>
                        </a>
                        <a href="{{ route('admin.banners.index') }}"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="{{ request()->routeIs('admin.banners.*') ? 'bg-blue-100 text-blue-600 font-semibold border border-blue-600' : 'hover:bg-gray-50' }} w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                            <div class="flex items-center gap-3 py-1">
                                <i class="bx bx-gallery-horizontal text-lg"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                    Carousel Banners
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
                <div>
                    <p :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        class="text-[10px] font-semibold text-slate-400 uppercase mb-4 px-3 tracking-wider">
                        System
                    </p>
                    <div class="space-y-2">
                        <a href="#" :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                            <div class="flex items-center gap-3 py-1">
                                <i class="bx bx-bell text-lg"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                    Notifications
                                </span>
                            </div>
                        </a>

                        <a href="#" :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="w-full flex items-center justify-between px-3 py-1 rounded-xl font-medium cursor-pointer  hover:shadow-sm transition-all duration-100">
                            <div class="flex items-center gap-3 py-1">
                                <i class="bx bx-cog text-lg"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">
                                    Settings
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </nav>

        </aside>

        <main class="flex flex-col flex-1 h-screen">
            <header
                class="relative z-40 h-16 px-6  border-b  border-slate-200 bg-white flex items-center justify-between shrink-0">

                <div class="flex items-center gap-2">
                    <button type="button" @click="sidebarOpen = true" aria-label="Open navigation"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl hover:bg-slate-100 lg:hidden">
                        <i class="bx bx-menu text-2xl"></i>
                    </button>
                    {{--  desktop --}}
                    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                        :aria-label="sidebarCollapsed ? 'Open sidebar' : 'Collapse sidebar'"
                        class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl hover:bg-slate-100 lg:inline-flex">
                        <i class="bx bx-menu-left text-xl"></i>
                    </button>
                    <div>
                        <button @click="openSearch = true; $nextTick(() => $refs.search.focus())" aria-label="Search"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white transition hover:bg-slate-50 sm:w-72 sm:justify-start sm:gap-3 sm:px-4">
                            <i class="bx bx-search text-lg text-slate-500"></i>

                            <span class="hidden text-sm text-slate-400 sm:inline">Search Anything...</span>

                            <kbd class="ml-auto hidden rounded border px-2 py-0.5 text-xs text-slate-400 sm:inline">
                                Ctrl K
                            </kbd>
                        </button>
                    </div>
                </div>
                <div class="flex items-center  gap-4 ">
                    <div x-data="{ openNotif: false }" class="relative">
                        <button @click="openNotif = !openNotif"
                            class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-slate-100 transition">
                            <i class="bx bx-bell text-slate-600"></i>
                        </button>
                        <div x-cloak x-show="openNotif" @click.away="openNotif = false" x-transition
                            class="absolute right-0 top-full mt-2 w-80 border border-slate-200 rounded-xl shadow-lg bg-white">
                            <div class="flex flex-col border-b border-slate-200 gap-3 px-3 py-2 ">
                                Notifications
                            </div>
                            <div class="flex flex-col border-b border-slate-200 gap-3 px-3 py-2 hover:bg-slate-50">
                                ....
                            </div>

                            <a href="#"
                                class="flex flex-col  items-center  gap-3 px-3 py-2 hover:text-blue-600">view all
                                notifications
                            </a>
                        </div>
                    </div>
                    <div x-cloak x-data="{ open: false, }" class="relative flex items-center">
                        <button @click="open = !open" class="">
                            @if (Auth()->user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth()->user()->profile_photo) }}" alt=""
                                    class="w-10 h-10 rounded-full ring-2 ring-slate-100">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}"
                                    alt="avatar"class="w-10 h-10 rounded-full ring-2 ring-slate-100" />
                            @endif
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 top-full mt-2 w-52 border bg-white border-slate-200 rounded-xl shadow-lg">
                            <div class="flex flex-col border-b border-gray-300 px-3 py-2">
                                <span class="">{{ auth()->user()->name }}</span>
                                <span class="text-sm text-gray-500">{{ auth()->user()->email }}</span>
                            </div>
                            <a href="" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50"><i
                                    class="bx bx-user text-lg"></i>Profile</a>
                            <a href="" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 "><i
                                    class="bx bx-cog"></i>settings</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center px-3 gap-3 py-2 hover:bg-red-100 cursor-pointer hover:text-red-600 transition-all duration-200 group border-t border-slate-200 rounded-b-xl">
                                    <i class="bx bx-log-out text-lg group-hover:-translate-x-1 transition"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <div class="min-h-0 min-w-0 flex-1 overflow-y-auto bg-slate-50 p-3 sm:p-6">
                @yield('ContentAdmin')
            </div>
        </main>

        {{-- MODAL SEARCH --}}
        <div x-cloak x-show="openSearch" @keydown.escape.window="openSearch = false"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black/50 shadow-lg ">
            <div x-transition.scale @click.away="openSearch = false"
                class="bg-white w-full max-w-md   rounded-xl shadow-lg overflow-hidden">
                {{-- Search --}}
                <div class="relative border-b border-slate-200">
                    <i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input x-ref="search" type="text" placeholder="Search Anything.."
                        class="w-full pl-12 pr-4 py-4 outline-none">
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <div class="flex flex-col items-center justify-center px-4 py-12 text-sm text-slate-500">
                        <i class="bx bx-search-alt text-5xl  mb-3"></i>
                        <p class="text-sm">
                            no results found.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
