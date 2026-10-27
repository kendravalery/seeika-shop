<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        session()->forget(['checkout_note', 'shipping']);

        if (Auth::check() && Auth::user()->role->name === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $products = Product::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('collection_id')->orWhereHas('collection', function ($query) {
                    $query->where('status', 'active');
                });
            })
            ->with(['images', 'variants', 'options'])
            ->paginate(20);

        // Products created in the last 30 days
        $newProducts = Product::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('collection_id')->orWhereHas('collection', function ($query) {
                    $query->where('status', 'active');
                });
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['images', 'variants', 'options'])
            ->latest()
            ->take(6)
            ->get();
        $this->applyPreorderClosedStatus([$products->getCollection(), $newProducts]);

        $categories = Category::with('children')->whereNull('parent_id')->get();

        $banners = Banner::where('is_active', true)->get();

        return view('storefront.home.index', compact('products', 'categories', 'banners', 'newProducts'));
    }

    private function applyPreorderClosedStatus(array $productGroups): void
    {
        // Collect products from all three homepage sections.
        $allProducts = collect();

        foreach ($productGroups as $group) {
            $allProducts = $allProducts->concat($group);
        }

        $allProducts = $allProducts->unique('id')->values();

        // Get active variants belonging to preorder products.
        $variantIds = $allProducts->filter(fn(Product $product) => $product->availability === 'preorder')->flatMap(fn(Product $product) => $product->variants->where('is_active', true)->pluck('id'))->unique()->values();

        $totals = collect();

        // Calculate preorder quantities for all relevant variants in one query.
        if ($variantIds->isNotEmpty()) {
            $totals = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_variant_id', $variantIds)
                ->where('order_items.fulfillment_type', 'preorder')
                ->where('orders.status', '!=', 'cancelled')
                ->whereNotIn('orders.payment_status', ['failed', 'refunded'])
                ->whereNull('orders.deleted_at')
                ->selectRaw(
                    'order_items.product_variant_id AS variant_id,
                 COALESCE(
                     SUM(order_items.quantity - order_items.refunded_quantity),
                     0
                 ) AS total',
                )
                ->groupBy('order_items.product_variant_id')
                ->pluck('total', 'variant_id');
        }

        // Determine whether each product has reached its preorder limits.
        $closedStatuses = [];

        foreach ($allProducts as $product) {
            $activeVariants = $product->availability === 'preorder' ? $product->variants->where('is_active', true) : collect();

            // No active variants means preorder is not considered closed.
            $closed = $activeVariants->isNotEmpty();

            foreach ($activeVariants as $variant) {
                // A null limit means this variant has no total preorder cap.
                if ($variant->preorder_limit === null || (int) ($totals[$variant->id] ?? 0) < (int) $variant->preorder_limit) {
                    $closed = false;
                    break;
                }
            }

            $closedStatuses[$product->id] = $closed;
        }

        // Apply the computed value to every product instance in each section.
        foreach ($productGroups as $group) {
            foreach ($group as $product) {
                $product->setAttribute('preorder_closed', $closedStatuses[$product->id] ?? false);
            }
        }
    }
}
