@extends('layouts.layout_customer')
@section('title', 'Frequently Asked Questions')

@section('ContentCustomer')
    <main class="bg-slate-50">
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">

            <header class="mx-auto mb-12 max-w-2xl text-center">

                <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Frequently Asked Questions
                </h1>

                <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
                    Find answers to common questions about ordering, products,
                    pre-orders, payments, and your account.
                </p>
            </header>

            <div class="mx-auto max-w-3xl space-y-10">

                {{-- Ordering & Checkout --}}
                <section aria-labelledby="ordering-heading">
                    <div class="mb-4">
                        <h2 id="ordering-heading" class="text-lg font-semibold text-slate-900">
                            Ordering & Checkout
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Everything you need to know about placing an order.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>How do I place an order?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Browse our products and select your preferred variant
                                and quantity. Add the items to your cart, select the
                                items you want to purchase, and continue to checkout
                                to complete the next steps.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Can I change the quantity in my cart?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Yes. You can adjust the quantity in your cart, subject to
                                availability and any applicable purchase limits. Ready-stock
                                quantities depend on available stock, while pre-order quantities
                                depend on the remaining pre-order availability. A maximum quantity
                                per order may also apply to the selected variant.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Why is an item no longer available at checkout?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Availability may change as other customers place orders. An item
                                may become unavailable if its stock or remaining pre-order quantity
                                is insufficient. If this happens, review the affected item in your
                                cart and check its availability before continuing.
                            </div>
                        </details>
                    </div>
                </section>

                {{-- Products & Pre-orders --}}
                <section aria-labelledby="products-heading">
                    <div class="mb-4">
                        <h2 id="products-heading" class="text-lg font-semibold text-slate-900">
                            Products & Pre-orders
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Learn about product availability and pre-order limits.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>What is the difference between ready stock and pre-order?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Ready-stock items are available from existing inventory while
                                supplies last. Pre-order items can be ordered in advance and are
                                subject to the remaining pre-order availability and any purchase
                                limits for the selected variant.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Does the pre-order release estimate guarantee a shipping date?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                No. The pre-order release estimate is provided for reference only
                                and does not guarantee when your order will be shipped or arrive.
                                Actual shipping and delivery times may vary.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Are there limits on pre-order quantities?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Some pre-order variants have a limited total quantity available
                                across all orders, and a separate maximum quantity per order may
                                also apply. The quantity you can purchase depends on the remaining
                                pre-order availability and the limits for your selected variant.
                            </div>
                        </details>
                    </div>
                </section>

                {{-- Payments & Account --}}
                <section aria-labelledby="account-heading">
                    <div class="mb-4">
                        <h2 id="account-heading" class="text-lg font-semibold text-slate-900">
                            Payments & Account
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Information about payments and managing your account.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>How do I complete my payment?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Continue from checkout to the payment page and follow
                                the instructions provided for your order. Please
                                review the displayed payment details carefully
                                before completing your payment.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>How do I update my saved address?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Sign in to your account, open the account menu, and select
                                Address to review or update your saved address information.
                            </div>
                        </details>
                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Will updating my saved address change an existing order?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Updating your saved address does not automatically change
                                the shipping address for an order you have already placed.
                                If you need to request a change, please contact us as soon
                                as possible. Changes depend on the order status.
                            </div>
                        </details>

                        <details class="group rounded-xl border border-slate-200 bg-white">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-slate-800">
                                <span>Can I change or cancel an order after placing it?</span>

                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600">
                                Please contact the shop as soon as possible through
                                the Contact Us page. Changes or cancellations depend
                                on the order status and the shop's applicable policy.
                            </div>
                        </details>
                    </div>
                </section>

                {{-- Support --}}
                <section aria-labelledby="support-heading">
                    <div class="mb-4">
                        <h2 id="support-heading" class="text-lg font-semibold text-slate-900">
                            Support
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Need more help? Here's how to reach us.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                        <h3 class="text-base font-semibold text-slate-900">
                            Still have questions?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            If you need help with a product or order, please reach
                            out through our Contact Us page.
                        </p>

                        <a href="{{ route('contact.show') }}"
                            class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            Contact Us

                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M7 17 17 7M7 7h10v10" />
                            </svg>
                        </a>
                    </div>
                </section>

            </div>
        </div>
    </main>
@endsection
