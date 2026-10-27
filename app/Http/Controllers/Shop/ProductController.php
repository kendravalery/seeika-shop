<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with(['images', 'category', 'options.values', 'variants.optionValues'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $this->applyPreorderClosedStatus($product);
        $preorderClosed = $product->preorder_closed;

        $isWishlist = false;

        if (Auth::check()) {
            $isWishlist = Wishlist::where('product_id', $product->id)->exists();
        }

        return view('storefront.products.show', compact('product', 'isWishlist','preorderClosed'));
    }

    private function applyPreorderClosedStatus(Product $product): void
    {
        $activeVariants = $product->variants->where('is_active', true);

        $closed = $product->availability === 'preorder' && $activeVariants->isNotEmpty();

        // An unlimited active variant keeps preorder open.
        if ($closed) {
            foreach ($activeVariants as $variant) {
                if ($variant->preorder_limit === null) {
                    $closed = false;
                    break;
                }
            }
        }

        // Calculate totals only when every active variant has a limit.
        if ($closed) {
            $totals = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_variant_id', $activeVariants->pluck('id'))
                ->where('order_items.fulfillment_type', 'preorder')
                ->where('orders.status', '!=', 'cancelled')
                ->whereNotIn('orders.payment_status', ['failed', 'refunded'])
                ->whereNull('orders.deleted_at')
                ->selectRaw(
                    'order_items.product_variant_id AS variant_id,
                     COALESCE(
                         SUM(
                             order_items.quantity -
                             order_items.refunded_quantity
                         ),
                         0
                     ) AS total',
                )
                ->groupBy('order_items.product_variant_id')
                ->pluck('total', 'variant_id');

            foreach ($activeVariants as $variant) {
                $totalPreorder = (int) ($totals[$variant->id] ?? 0);

                if ($totalPreorder < (int) $variant->preorder_limit) {
                    $closed = false;
                    break;
                }
            }
        }

        $product->setAttribute('preorder_closed', $closed);
    }
}
