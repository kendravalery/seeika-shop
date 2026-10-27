@extends('layouts.layout_customer')
@section('title', 'Contact Us')

@section('ContentCustomer')
    @if (session('success'))
        <script>
            alert(@js(session('success')));
        </script>
    @endif
    <main class="flex flex-col items-center justify-center px-4 py-6 sm:px-6">
        <div class="mb-7 text-center">
            <h1 class="text-2xl font-semibold sm:text-3xl">Customer Service</h1>
        </div>

        <div class="mb-6 w-full max-w-2xl">
            <form action="{{ route('contact.store') }}" method="POST">
                @csrf

                <div class="flex flex-col items-center gap-4">

                    <div class="w-full max-w-sm">
                        <label for="name" class="mb-1 block text-center text-sm">Name</label>

                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full border-b border-gray-300 px-3 py-2 outline-none focus:border-slate-500" required>
                    </div>

                    <div class="w-full max-w-sm">
                        <label for="email" class="mb-1 block text-center text-sm">E-mail</label>

                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full border-b border-gray-300 px-3 py-2 outline-none
                                focus:border-slate-500"
                            required>
                    </div>

                    <div class="w-full max-w-sm">
                        <label for="subject" class="mb-1 block text-center text-sm">Subject</label>

                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                            class="w-full border-b border-gray-300 px-3 py-2 outline-none focus:border-slate-500" required>
                    </div>

                </div>

                <div class="mt-6 text-center">
                    <label for="message" class="mb-2 block text-sm">Message</label>

                    <textarea name="message" id="message" rows="5"
                        class="w-full resize-y rounded-lg border border-gray-300 p-3 outline-none focus:border-slate-500" required>{{ old('message') }}</textarea>

                    <button type="submit"
                        class="mt-3 cursor-pointer border-b border-gray-300 px-4 py-2 text-sm transition hover:border-slate-700">
                        Submit
                    </button>
                </div>
            </form>
        </div>

        <div class="w-full max-w-2xl space-y-4 text-center text-sm leading-6 text-gray-500">

            <div class="border-b border-slate-200 pb-4">
                <p>You're welcome to ask questions and share your thoughtsabout our website.</p>
                <p class="mt-1">If you run into any bugs or errors, please reach out to us.</p>
            </div>

            <div class="pb-2">
                <p> Anda dapat menyampaikan pertanyaan, kritik, dan saran tentang website kami.</p>
                <p class="mt-1">Jika menemukan kesalahan atau bug, jangan ragu untuk menghubungi kami.</p>
            </div>

        </div>

    </main>
@endsection
