<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())
            ->with(['product.images', 'product.variants', 'product.options'])
            ->paginate(8)
            ->withQueryString();

        // Get unique products from the current wishlist page.
        $products = $wishlists->getCollection()->pluck('product')->filter()->unique('id')->values();

        // Calculate preorder status using the existing quota rules.
        $this->applyPreorderClosedStatus($products);

        return view('customer.wishlist.index', compact('wishlists'));
    }

    public function wishlistAdd($id)
    {
        Wishlist::firstOrCreate(['user_id' => auth::id(), 'product_id' => $id]);
        return back();
    }

    public function wishlistDelete($id)
    {
        Wishlist::where('user_id', Auth::id())->where('product_id', $id)->delete();
        return back();
    }

    public function toggle($id)
    {
        $user = Auth::user();
        $wishlist = Wishlist::where('user_id', $user->id)->where('product_id', $id)->first();
        if ($wishlist) {
            $wishlist->delete();
        } else {
            Wishlist::create(['user_id' => Auth::id(), 'product_id' => $id]);
        }
        return back();
    }

    private function applyPreorderClosedStatus(Collection $products): void
    {
        $variantIds = $products->filter(fn(Product $product) => $product->availability === 'preorder')->flatMap(fn(Product $product) => $product->variants->where('is_active', true)->pluck('id'))->unique()->values();

        $totals = collect();
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

        foreach ($products as $product) {
            $activeVariants = $product->availability === 'preorder' ? $product->variants->where('is_active', true) : collect();
            $closed = $activeVariants->isNotEmpty();

            foreach ($activeVariants as $variant) {
                $total = (int) ($totals[$variant->id] ?? 0);

                if ($variant->preorder_limit === null || $total < (int) $variant->preorder_limit) {
                    $closed = false;
                    break;
                }
            }

            $product->setAttribute('preorder_closed', $closed);
        }
    }
}
