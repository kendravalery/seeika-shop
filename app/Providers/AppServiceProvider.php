<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Provide cart and category data used by the customer header.
        View::composer('layouts.header', function ($view) {
            $cartCount = 0;

            if (Auth::check()) {
                $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
            }

            // Load parent categories with their children for the Shop navigation.
            $categories = Category::with('children')->whereNull('parent_id')->get();

            $collections = Collection::where('status', 'active')->get();

            $view->with(['cartCount' => $cartCount, 'categories' => $categories,'collections' => $collections ]);
        });
    }
}
