<div class="w-full lg:w-72 lg:shrink-0">
    <aside class="w-full bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-24">

        <div class="flex items-center gap-3 p-4 sm:p-5 hidden md:flex">
            <div class="shrink-0">
                @if (auth()->user()->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}"
                        class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover border border-slate-200">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                        alt="{{ auth()->user()->name }}"
                        class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover border border-slate-200">
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <h2 class="text-sm sm:text-base font-bold text-gray-900 truncate">{{ auth()->user()->name }}</h2>

                <p class="text-xs sm:text-sm text-gray-400 truncate mt-1">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <div class="border-t border-slate-100"></div>

        <nav aria-label="Account navigation" class="flex gap-1 overflow-x-auto p-2 lg:flex-col lg:overflow-visible">
            <a href="{{ route('customer.myAccount') }}" @class([
                'shrink-0 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm transition',
                'bg-blue-100 text-blue-700 font-semibold' => request()->routeIs(
                    'customer.myAccount'),
                'text-gray-600 hover:bg-slate-50 hover:text-gray-900' => !request()->routeIs(
                    'customer.myAccount'),
            ])>
                My Account
            </a>

            <a href="{{ route('customer.manage.account') }}" @class([
                'shrink-0 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm transition',
                'bg-blue-100 text-blue-700 font-semibold' => request()->routeIs(
                    'customer.manage.account'),
                'text-gray-600 hover:bg-slate-50 hover:text-gray-900' => !request()->routeIs(
                    'customer.manage.account'),
            ])>
                Manage Account
            </a>

            <a href="{{ route('customer.address') }}" @class([
                'shrink-0 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm transition',
                'bg-blue-100 text-blue-700 font-semibold' => request()->routeIs(
                    'customer.address'),
                'text-gray-600 hover:bg-slate-50 hover:text-gray-900' => !request()->routeIs(
                    'customer.address'),
            ])>
                Address
            </a>

            <a href="{{ route('Wishlist.index') }}" @class([
                'shrink-0 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm transition',
                'bg-blue-100 text-blue-700 font-semibold' => request()->routeIs(
                    'Wishlist.index'),
                'text-gray-600 hover:bg-slate-50 hover:text-gray-900' => !request()->routeIs(
                    'Wishlist.index'),
            ])>
                Wishlist
            </a>

            <a href="#"
                class="shrink-0 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm text-gray-600 hover:bg-slate-50 hover:text-gray-900 transition">
                Orders
            </a>
        </nav>
    </aside>
</div>
