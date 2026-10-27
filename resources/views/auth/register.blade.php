<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register | Seeika Shop</title>
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
            <h1 class="mb-5 text-center text-2xl font-bold">Register</h1>

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="mb-1 block text-slate-700">Name</label>

                    <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name"
                        class="w-full rounded-lg border-2 border-slate-200 p-3 outline-none focus:border-slate-400"
                        required>

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-slate-700">Email</label>

                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email"
                        placeholder="example@gmail.com"
                        class="w-full rounded-lg border-2 border-slate-200 p-3 outline-none focus:border-slate-400"
                        required>

                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-slate-700">Password</label>

                    <div class="relative">
                        <input type="password" name="password" id="password" autocomplete="new-password"
                            placeholder="Enter your password"
                            class="w-full rounded-lg border-2 border-slate-200 p-3 pr-12 outline-none focus:border-slate-400"
                            required>

                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 z-10 flex cursor-pointer items-center justify-center px-3 text-slate-500 hover:text-slate-800"
                            aria-label="Show password" aria-pressed="false">

                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

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

                <div>
                    <label for="password_confirmation" class="mb-1 block text-slate-700">
                        Confirm Password
                    </label>

                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            autocomplete="new-password" placeholder="Confirm your password"
                            class="w-full rounded-lg border-2 border-slate-200 p-3 pr-12 outline-none focus:border-slate-400"
                            required>

                        <button type="button" id="togglePasswordConfirmation"
                            class="absolute inset-y-0 right-0 z-10 flex cursor-pointer items-center justify-center px-3 text-slate-500 hover:text-slate-800"
                            aria-label="Show confirm password" aria-pressed="false">

                            <svg id="eyeConfirmationIcon" xmlns="http://www.w3.org/2000/svg" width="22"
                                height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            <svg id="eyeOffConfirmationIcon" xmlns="http://www.w3.org/2000/svg" width="22"
                                height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                class="hidden">
                                <path d="M3 3 21 21" />
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                                <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3 3.9" />
                                <path d="M6.6 6.6A16 16 0 0 1 2 12s3.6 7 10 7a10.8 10.8 0 0 0 4.1-.8" />
                            </svg>
                        </button>
                    </div>

                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full cursor-pointer rounded-lg bg-blue-500 p-2 text-white hover:bg-blue-600">
                    Register
                </button>
            </form>

            <p class="mt-4 text-center text-sm">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline">
                    Return to login
                </a>
            </p>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggles = [{
                    input: document.getElementById('password'),
                    button: document.getElementById('togglePassword'),
                    eye: document.getElementById('eyeIcon'),
                    eyeOff: document.getElementById('eyeOffIcon'),
                    label: 'password'
                },
                {
                    input: document.getElementById('password_confirmation'),
                    button: document.getElementById('togglePasswordConfirmation'),
                    eye: document.getElementById('eyeConfirmationIcon'),
                    eyeOff: document.getElementById('eyeOffConfirmationIcon'),
                    label: 'confirm password'
                }
            ];

            passwordToggles.forEach(function(item) {
                item.button.addEventListener('click', function() {
                    const showPassword = item.input.type === 'password';

                    item.input.type = showPassword ? 'text' : 'password';

                    item.eye.classList.toggle('hidden', showPassword);
                    item.eyeOff.classList.toggle('hidden', !showPassword);

                    item.button.setAttribute(
                        'aria-label',
                        `${showPassword ? 'Hide' : 'Show'} ${item.label}`
                    );

                    item.button.setAttribute(
                        'aria-pressed',
                        String(showPassword)
                    );
                });
            });
        });
    </script>
</body>

</html>
