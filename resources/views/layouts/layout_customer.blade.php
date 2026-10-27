<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>@yield('title', 'Seeika')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class=" min-h-screen flex flex-col bg-slate-50 text-gray-800 ">
    {{-- Header --}}
    @include('layouts.header')

    {{-- main --}}
    <main class="flex-1 ">
        @yield('ContentCustomer')
    </main>

    @include('layouts.footer')

    <script>
        const accBtn = document.getElementById('accBtn');
        const accMenu = document.getElementById('accMenu');
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

        if (accBtn && accMenu) {
            accBtn.addEventListener('click', (e) => {
                e.stopPropagation();

                accMenu.style.display =
                    accMenu.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!accMenu.contains(e.target) && !accBtn.contains(e.target)) {
                    accMenu.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>
