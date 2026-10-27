<header x-data="{ searchOpen: false }" x-init="window.addEventListener('resize', () => {
    // if desktop,close
    if (window.innerWidth >= 1024) {
        searchOpen = false
    }
})" class="bg-white shadow-sm sticky top-0 z-40">

    <div class="max-w-7xl mx-auto px-1 sm:px-2 lg:px-2">
        <div class="flex items-center justify-between py-4 gap-4">
            <div class="flex items-center gap-4 shrink-0">

                <div x-data="{ menuOpen: false }" x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 768) { menuOpen = false } })" class="md:hidden">
                    <button type="button" @click="menuOpen = true"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100">
                        <i class="bx bx-menu text-2xl"></i>
                    </button>
                    {{-- MOBILE MENU /drawer --}}
                    <div x-cloak x-show="menuOpen" @keydown.escape.window="menuOpen = false"
                        class="fixed inset-0 z-[70]">
                        {{-- overlay --}}
                        <div x-show="menuOpen" x-transition:enter="transition-opacity ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity ease-in duration-250"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @click="menuOpen = false" class="absolute inset-0 bg-black/30">
                        </div>

                        <aside x-show="menuOpen" x-transition:enter="transition transform ease-out duration-300"
                            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                            x-transition:leave="transition transform ease-in duration-250"
                            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                            class="relative h-full w-72 max-w-[85%] overflow-y-auto bg-white shadow-xl">

                            <div class="flex items-center justify-between px-5 border-b border-slate-200 py-2">
                                <span class="font-semibold text-xl">
                                    Menu
                                </span>
                                <button type="button" @click="menuOpen = false"
                                    class="w-10 h-10 flex items-center justify-center rounded-md hover:bg-amber-100">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>
                            {{-- NAV --}}
                            <nav class="p-4 ">
                                <a href="{{ route('home') }}"
                                    class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-lg">
                                    All
                                </a>
                                {{-- Collections --}}
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-4 py-2 rounded-lg hover:bg-gray-100 text-lg">
                                        <span>Collections</span>

                                        <i class="bx bx-chevron-down text-xl transition-transform duration-300"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-cloak x-show="open"
                                        x-transition:enter="transition-all ease-out duration-300"
                                        x-transition:enter-start="opacity-0 max-h-0"
                                        x-transition:enter-end="opacity-100 max-h-96"
                                        x-transition:leave="transition-all ease-in duration-200"
                                        x-transition:leave-start="opacity-100 max-h-96"
                                        x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden pl-4">
                                        <a href="{{ route('storefront.collections') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:text-blue-600">
                                            All Collections
                                        </a>
                                        @foreach ($collections as $collection)
                                            <a href="{{ route('storefront.collections.show', ['slug' => $collection->slug]) }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:text-blue-600">{{ $collection->name }}
                                            </a>
                                        @endforeach

                                    </div>
                                </div>

                                {{-- Category --}}
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-lg">
                                        <span>
                                            Category
                                        </span>
                                        <i class="bx bx-chevron-down text-xl transition-transform duration-300"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-cloak x-show="open"
                                        x-transition:enter="transition-all ease-out duration-300"
                                        x-transition:enter-start="opacity-0 max-h-0"
                                        x-transition:enter-end="opacity-100 max-h-96"
                                        x-transition:leave="transition-all ease-in duration-200"
                                        x-transition:leave-start="opacity-100 max-h-96"
                                        x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden pl-4">
                                        {{-- Apparel --}}
                                        @foreach ($categories as $category)
                                            <div class="py-2">
                                                <p
                                                    class="px-4 text-xs font-semibold uppercase tracking-wide border-b">
                                                    {{ $category->name }}
                                                </p>
                                                @foreach ($category->children as $child)
                                                    <a href="{{ route('storefront.collections.show', ['slug' => $child->slug]) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:text-blue-600">
                                                        {{ $child->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </nav>
                        </aside>
                    </div>
                </div>

                <a href="/" class="flex items-center gap-1">
                    <div
                        class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold ">
                        Se
                    </div>
                    <span class="font-semibold text-lg tracking-light hidden sm:inline">Seeika</span>
                </a>

                {{-- LG menu --}}
                <nav class="hidden md:flex items-center gap-5">
                    <a href="{{ route('home') }}"
                        class="px-3  py-2 rounded-md text-base font-medium text-gray-700">All</a>

                    <div x-data="{
                        open: false,
                        rotate: 0,
                        timer: [],
                        toggle() {
                            this.open = !this.open;
                            this.timer.forEach(clearTimeout);
                            this.timer = [];

                            if (this.open) {
                                this.rotate = 195;

                                this.timer.push(setTimeout(() => { this.rotate = 177; }, 230));
                                this.timer.push(setTimeout(() => { this.rotate = 183; }, 360));
                                this.timer.push(setTimeout(() => { this.rotate = 180; }, 520));
                            } else {
                                this.close();
                            }
                        },
                        close() {
                            this.open = false;

                            this.timer.forEach(clearTimeout);
                            this.timer = [];

                            this.rotate = -15;
                            this.timer.push(setTimeout(() => { this.rotate = 3; }, 180));
                            this.timer.push(setTimeout(() => { this.rotate = -3; }, 360));
                            this.timer.push(setTimeout(() => { this.rotate = 0; }, 520));
                        }
                    }" class="relative" @click.outside="close()">
                        <button @click="if({{ $collections->isNotEmpty() ? 'true' : 'false ' }})    toggle()"
                            class="px-3  py-2 rounded-md text-base font-medium  hover:bg-gray-100 inline-flex items-center {{ $collections->isNotEmpty() ? 'text-gray-700' : ' text-gray-300' }}">
                            <span class="font-sans">
                                Collections
                            </span>
                            <i class="bx bx-chevron-down  text-lg transition-transform duration-150 mt-2 ml-1"
                                :style="`transform: rotate(${rotate}deg)`"></i>
                        </button>

                        <div x-cloak x-show="open" x-transition
                            class=" {{ $collections->isNotEmpty() ? 'absolute left-0 top-full mt-2 w-52 bg-white border border-slate-200 shadow-lg rounded-md py-2 z-50 text-sm' : '' }}">
                            <a href="{{ route('storefront.collections') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-50">All Collections</a>
                            @foreach ($collections as $collection)
                                <a href="{{ route('storefront.collections.show', ['slug' => $collection->slug]) }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    {{ $collection->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div x-data="{
                        open: false,
                        rotate: 0,
                        timer: [],
                        toggle() {
                            this.open = !this.open;
                            this.timer.forEach(clearTimeout);
                            this.timer = [];

                            if (this.open) {
                                this.rotate = 195;

                                this.timer.push(setTimeout(() => { this.rotate = 177; }, 230));
                                this.timer.push(setTimeout(() => { this.rotate = 183; }, 360));
                                this.timer.push(setTimeout(() => { this.rotate = 180; }, 520));
                            } else {
                                this.close();
                            }
                        },
                        close() {
                            this.open = false;

                            this.timer.forEach(clearTimeout);
                            this.timer = [];

                            this.rotate = -15;
                            this.timer.push(setTimeout(() => { this.rotate = 3; }, 180));
                            this.timer.push(setTimeout(() => { this.rotate = -3; }, 360));
                            this.timer.push(setTimeout(() => { this.rotate = 0; }, 520));
                        }
                    }" class="relative" @click.outside="close()">
                        <button @click="toggle()"
                            class="px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 inline-flex items-center ">
                            <span>
                                Category
                            </span>
                            <i class="bx bx-chevron-down text-lg transition-transform duration-150 mt-2 ml-1"
                                :style="`transform: rotate(${rotate}deg)`"></i>
                        </button>

                        <div x-cloak x-show="open" x-transition
                            class="absolute left-0 top-full mt-2 w-52 bg-white border border-slate-200 shadow-lg rounded-md py-2 z-50">
                            @foreach ($categories as $category)
                                <div class="px-4 py-2">
                                    <p class="text-sm font-semibold uppercase tracking-wide border-b">
                                        {{ $category->name }}
                                    </p>
                                    @foreach ($category->children as $child)
                                        <a href="{{ route('storefront.collections.show', ['slug' => $child->slug]) }}"
                                            class="block mt-1 py-1 px-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                            {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach

                        </div>
                    </div>
                </nav>

            </div>

            <div class="flex items-center gap-2 ">
                {{-- SEARCH --}}
                <div class="hidden lg:flex  w-64 px-1 ">
                    <form action="{{ route('search') }}" method="GET" class="max-w-xl mx-auto relative">
                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Search Products"
                            class="w-full pl-4 py-2.5 pr-20 border border-gray-200 rounded-full  focus:outline-none focus:ring-blue-300 text-sm">

                        <button type="submit"
                            class="absolute right-1 top-1/2 -translate-y-1/2 bg-blue-600 text-white px-4 py-1.5 rounded-full hover:bg-blue-700">
                            <i class="bx bx-search"></i>
                        </button>
                    </form>
                </div>
                {{-- for search in mobile responsive --}}
                <button type="button" @click="searchOpen = true"
                    class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100">
                    <i class="bx bx-search text-2xl"></i>
                </button>

                <a href="{{ route('cart.index') }}"
                    class="relative inline-flex items-center px-3 py-2 right-2  rounded-md hover:bg-gray-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 7h14l-2-7M9 21a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1 rounded-full">
                        {{ $cartCount }}
                    </span>
                </a>

                {{-- account --}}
                <div class="relative">
                    @guest
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}"
                                class="px-3 py-1 text-sm rounded-xl hover:bg-gray-100 hover:border-b-5 hover:border-slate-600 transition-all  hover:-translate-y-1 active:border-b-0 active:-translate-y-1">
                                Login
                            </a>

                            <a href="{{ route('register') }}"
                                class="px-3 py-1 border-b-6 border-blue-700 text-sm rounded-xl bg-blue-500 hover:bg-blue-400 text-white active:translate-y-1 active:border-b-0 transition-all">
                                Register
                            </a>
                        </div>
                    @endguest

                    @auth
                        <button id="accBtn"
                            class="flex items-center gap-2 px-1 lg:px-3 hover:bg-white py-1 transition duration-100 bg-slate-50 border-b-4 border-b-slate-300 rounded-xl active:border-b-white active:translate-y-1">
                            @if (auth()->user()->profile_photo)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                                    class="w-8 h-8 rounded-full border object-cover">
                                <span class="hidden sm:inline">{{ str(auth()->user()->name)->limit(8, '...') }}</span>
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}"
                                    alt="avatar"class="w-8 h-8 rounded-full " />
                                <span class="hidden sm:inline">{{ str(auth()->user()->name)->limit(8, '...') }}</span>
                            @endif
                        </button>

                        <div id="accMenu"
                            class="absolute right-0 mt-1 w-44 rounded-lg shadow-lg bg-white border border-b-3 border-gray-300 z-50  transform duration-200 outline-hidden"
                            style="display:none;">
                            <div class="border-b border-b-slate-200">
                                <a href="{{ route('customer.myAccount') }}" class="block px-4 py-2 hover:bg-gray-50">My
                                    account
                                </a>

                                <a href="{{ route('customer.address') }}"
                                    class="block px-4 py-2 hover:bg-gray-50">Address</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-50">Orders</a>

                            </div>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <input type="submit"value="Logout"
                                    class="w-full text-left px-4 text-sm hover:bg-red-50 text-red-500 py-2 cursor-pointer rounded-b-lg">
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{--  popup overlay --}}
    <div x-cloak x-show="searchOpen" x-transition.opacity @keydown.escape.window="searchOpen = false"
        class="fixed inset-0 z-[60]  bg-black/40">
        <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
            @click.outside="searchOpen = false" class="bg-white p-4 shadow-lg">

            <form action="{{ route('search') }}" method="GET">

                <div class="flex items-center gap-2">

                    <div class="relative flex-1">

                        <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>

                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Search Product.."
                            class="w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl ">
                    </div>

                    <button type="button" @click="searchOpen = false"
                        class="h-11 w-11 flex items-center justify-center rounded-xl hover:bg-gray-100">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

</header>
