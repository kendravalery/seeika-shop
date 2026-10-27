@extends('layouts.layout_customer')
@section('title', 'ABOUT SEEIKA')
@section('ContentCustomer')
    <main class="min-h-[60vh] px-4 py-10 sm:px-6 sm:py-14">
        <div class="mx-auto max-w-4xl">
            <section class="text-center">
                <h1 class="font-semibold text-3xl p-3 mb-3">
                    Seeika Shop
                </h1>
                <p class="mb-2">A small e-commerce </p>
                <p class="mx-auto max-w-2xl text-sm leading-7 text-slate-600">
                    Seeika Shop is a personal project that I developed on my own to explore creating e-commerce
                    features
                </p>
            </section>

            <section class="mx-auto mt-12 max-w-lg border-t border-slate-200 pt-8">
                <div class="flex flex-col items-center justify-center gap-4 text-center">

                    <img src="{{ asset('profile/6abce77k67.jpeg') }}" alt="Kendra VM"
                        class="h-28 w-28 rounded-xl object-cover">

                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Kendra VM</h2>

                        <p class="mt-1 text-sm text-slate-500">Owner</p>

                        <p class="mt-1 text-sm text-slate-500">Full-Stack Developer</p>
                    </div>

                </div>
            </section>
        </div>
    </main>
@endsection
