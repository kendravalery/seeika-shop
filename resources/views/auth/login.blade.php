<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | Seeika Shop</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white sm:bg-gray-100">

    @if ($errors->any())
        <script>
            alert(@json($errors->first()));
        </script>
    @endif

    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="w-full max-w-sm p-6 sm:rounded-2xl sm:bg-white sm:p-8 sm:shadow-lg">

            <p class="text-center mb-5 text-xl">
                Welcome to <a href="{{ route('home') }}" class="text-blue-500">Seeika Shop</a>
            </p>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-slate-700">Email</label>

                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email"
                        class="w-full rounded-lg border-2 border-slate-200 p-3 outline-none focus:border-slate-400"
                        required>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-slate-700">Password</label>

                    <div class="relative">
                        <input type="password" name="password" id="password" autocomplete="current-password"
                            class="w-full rounded-lg border-2 border-slate-200 p-3 pr-12 outline-none focus:border-slate-400"
                            required>

                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 z-10 flex cursor-pointer items-center justify-center px-3 text-slate-500 hover:text-slate-800"
                            aria-label="Show password" aria-pressed="false">

                            {{-- Eye on --}}
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            {{-- Eye off --}}
                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="hidden">
                                <path d="M3 3 21 21" />
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                                <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3 3.9" />
                                <path d="M6.6 6.6A16 16 0 0 1 2 12s3.6 7 10 7a10.8 10.8 0 0 0 4.1-.8" />
                            </svg>

                        </button>
                    </div>

                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full cursor-pointer rounded-lg bg-blue-500 p-2 text-white hover:bg-blue-600">
                    Login
                </button>
            </form>

            <p class="mt-4 text-center text-sm">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Register</a>
            </p>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            togglePassword.addEventListener('click', function() {
                const showPassword = passwordInput.type === 'password';

                passwordInput.type = showPassword ? 'text' : 'password';

                eyeIcon.classList.toggle('hidden', showPassword);
                eyeOffIcon.classList.toggle('hidden', !showPassword);

                togglePassword.setAttribute(
                    'aria-label',
                    showPassword ? 'Hide password' : 'Show password'
                );

                togglePassword.setAttribute(
                    'aria-pressed',
                    String(showPassword)
                );
            });
        });
    </script>

</body>

</html>
