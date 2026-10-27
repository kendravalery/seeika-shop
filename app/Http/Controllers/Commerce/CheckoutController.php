<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout()
    {
        $user = Auth::user();

        $cartItems = CartItem::with(['productVariant.product.images', 'productVariant.optionValues.image', 'productVariant.product.collection'])
            ->where('user_id', $user->id)
            ->where('is_selected', true)
            ->get();

        // Make sure at least one item is selected.
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one product.');
        }

        // get preorder variants with a total quantity limit.
        $limitedPreorderVariantIds = $cartItems
            ->filter(function ($item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                return $variant && $product && $product->availability === 'preorder' && $variant->preorder_limit !== null;
            })
            ->map(fn($item) => (int) $item->productVariant->id)
            ->unique()
            ->values()
            ->all();

        $preorderTotals = collect();

        if (!empty($limitedPreorderVariantIds)) {
            // Count preorder quantities from existing orders.
            $preorderTotals = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_variant_id', $limitedPreorderVariantIds)
                ->where('order_items.fulfillment_type', 'preorder')
                ->where('orders.status', '!=', 'cancelled')
                ->where(function ($query) {
                    $query->whereNull('orders.payment_status')->orWhereNotIn('orders.payment_status', ['failed', 'refunded']);
                })
                ->whereNull('orders.deleted_at')
                ->select('order_items.product_variant_id')
                ->selectRaw(
                    'COALESCE(
                        SUM(
                            GREATEST(
                                order_items.quantity
                                - COALESCE(order_items.refunded_quantity, 0),
                                0
                            )
                        ),
                        0
                    ) AS total',
                )
                ->groupBy('order_items.product_variant_id')
                ->get()
                ->keyBy('product_variant_id');
        }

        $hasUnavailableItems = false;
        $hasAdjustedQuantities = false;

        foreach ($cartItems as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;

            // check whether the product can still be purchased.
            $isInvalid = !$variant || !$variant->is_active || !$product || $product->status !== 'active' || !in_array($product->availability, ['ready', 'preorder'], true) || ($product->collection && $product->collection->status !== 'active');

            if ($isInvalid || (int) $item->quantity < 1) {
                CartItem::whereKey($item->id)->update([
                    'is_selected' => false,
                ]);

                $hasUnavailableItems = true;
                continue;
            }

            $limits = [];

            if ($product->availability === 'ready') {
                // ready stock cannot exceed the available stock.
                $limits[] = max(0, (int) $variant->stock);
            } else {
                // Check the remaining preorder capacity.
                if ($variant->preorder_limit !== null) {
                    $totalPreordered = (int) ($preorderTotals[$variant->id]->total ?? 0);

                    $remainingPreorder = max(0, (int) $variant->preorder_limit - $totalPreordered);

                    $limits[] = $remainingPreorder;
                }
            }

            // Apply the per-order limit when one is set.
            if ($variant->max_purchase_per_order !== null) {
                $limits[] = max(0, (int) $variant->max_purchase_per_order);
            }

            $maxAllowed = empty($limits) ? null : min($limits);

            // Remove items that can no longer be purchased.
            if ($maxAllowed !== null && $maxAllowed <= 0) {
                CartItem::whereKey($item->id)->update([
                    'is_selected' => false,
                ]);

                $hasUnavailableItems = true;
                continue;
            }

            // update the quantity if a limit has changed.
            if ($maxAllowed !== null && (int) $item->quantity > $maxAllowed) {
                $item->quantity = $maxAllowed;
                $item->save();

                $hasAdjustedQuantities = true;
            }

            $item->cartStatus = 'available';
        }

        // return to the cart if anything has changed.
        if ($hasUnavailableItems || $hasAdjustedQuantities) {
            return redirect()->route('cart.index')->with('error', 'Some cart items or quantities have changed. Please review your cart.');
        }

        // calculate the total using variant prices.
        $total = $cartItems->sum(function ($item) {
            return $item->productVariant->price * $item->quantity;
        });

        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first();

        $addresses = Address::where('user_id', $user->id)->get();

        return view('commerce.checkout.index', compact('cartItems', 'total', 'addresses', 'defaultAddress'));
    }
}
