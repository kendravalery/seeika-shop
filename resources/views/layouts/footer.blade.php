<footer class="bg-white py-8 border-t border-slate-200">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-2 max-w-2xl mx-auto mb-8">

            <div class="flex flex-col items-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 tracking-wider">INFO</h3>
                <nav class="flex flex-col gap-3 text-center text-sm text-gray-600">
                    <a href="{{ route('about') }}"
                        class="hover:text-gray-900 hover:underline transition duration-200">about seeika</a>
                    <a href="{{ route('contact.show') }}"
                        class="hover:text-gray-900 hover:underline transition duration-200">contact Us</a>
                    <a href="{{ route('faq') }}"
                        class="hover:text-gray-900 hover:underline transition duration-200">faq</a>
                </nav>
            </div>

            <div class="flex flex-col items-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 tracking-wider">SNS</h3>
                <div class="flex gap-5">
                    <a href="#" class="transition duration-200 hover:scale-110">
                        <img src="{{ asset('icon/twitter.png') }}" class="w-5 h-5" alt="twitter">
                    </a>
                    <a href="#" class="transition duration-200 hover:scale-110">
                        <img src="{{ asset('icon/instagram.png') }}" class="w-5 h-5" alt="instagram">
                    </a>
                    <a href="#" class="transition duration-200  hover:scale-110">
                        <img src="{{ asset('icon/facebook-app-symbol.png') }}" class="w-5 h-5" alt="facebook">
                    </a>
                </div>
            </div>
        </div>

    </div>
    <div class="text-center pt-6 border-t border-gray-100">
        <p class="font-mono text-sm text-gray-500">
            ©{{ date('Y') }} SEEIKA SHOP, All rights reserved.
        </p>
    </div>

</footer>
