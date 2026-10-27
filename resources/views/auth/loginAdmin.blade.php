<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Admin</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    @if ($errors->any())
        <script>
            alert("{{ $errors->first() }}");
        </script>
    @endif
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-sm p-8">
            <p class="text-center mb-8">Welcome Admin</p>
            <form action="{{ route('admin.login.post') }}" class="space-y-2" method="POST">
                @csrf
                <input type="email" class="border p-2 w-full" placeholder="Email" name="email">
                <input type="password" class="border p-2 w-full" placeholder="password" name="password">
                <button type="submit"
                    class="bg-blue-500 p-2 w-full text-white hover:bg-blue-600 cursor-pointer">Login</button>
            </form>
            <div class="my-4">
                <a href="{{ route('home') }}" class="hover:text-blue-600 underline">back to home</a>
            </div>
        </div>
    </div>
</body>

</html>
