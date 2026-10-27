<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $variant = ProductVariant::where('id', $request->variant_id)->where('product_id', $product->id)->where('is_active', true)->firstOrFail();

        $user = Auth::user();

        if ($product->status !== 'active') {
            return back()->with('error', 'This product is no longer available.');
        }

        if ($product->collection && $product->collection->status !== 'active') {
            return back()->with('error', 'This product is no longer available.');
        }

        if (!in_array($product->availability, ['ready', 'preorder'], true)) {
            return back()->with('error', 'This product is no longer available.');
        }

        $cart = CartItem::firstOrNew([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        $currentQuantity = $cart->exists ? (int) $cart->quantity : 0;

        // a null limit means there is no additional per-order cap
        // for preorder, the effective cap also includes remaining total capacity
        $maxAllowed = $this->getEffectiveCartLimit($variant, $product);

        if ($maxAllowed !== null && $currentQuantity + 1 > $maxAllowed) {
            // D=ao not add beyond the limit
            // The cart page will also normalize outdated quantities
            return back();
        }

        $cart->quantity = $currentQuantity + 1;
        $cart->save();

        return back()->with('success', 'Add to Cart');
    }

    public function index()
    {
        $user = Auth::user();

        $cartItems = CartItem::with(['productVariant.product.images', 'productVariant.optionValues.image', 'productVariant.product.collection'])
            ->where('user_id', $user->id)
            ->get();

        // gather variants with a finite total preorder limit
        $preorderVariantIds = $cartItems
            ->filter(function ($item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                return $variant && $variant->is_active && $product?->availability === 'preorder' && $variant->preorder_limit !== null;
            })
            ->map(fn($item) => $item->productVariant->id)
            ->unique()
            ->values();

        $preorderTotals = collect();

        // count qualifying preorder quantities in one aggregate query.
        if ($preorderVariantIds->isNotEmpty()) {
            $preorderTotals = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_variant_id', $preorderVariantIds)
                ->where('order_items.fulfillment_type', 'preorder')
                ->where('orders.status', '!=', 'cancelled')
                ->where(function ($query) {
                    $query->whereNull('orders.payment_status')->orWhereNotIn('orders.payment_status', ['failed', 'refunded']);
                })
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
        }

        $total = 0;

        foreach ($cartItems as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;

            $item->cartStatus = 'available';
            $item->cartMessage = null;
            $item->preorderRemaining = null;
            $item->maxAllowedQuantity = null;

            // variant no longer exists
            if (!$variant) {
                $item->cartStatus = 'unavailable';
                $item->cartMessage = 'Variant is no longer available.';
            }

            // variant inactive
            elseif (!$variant->is_active) {
                $item->cartStatus = 'unavailable';
                $item->cartMessage = 'This variant is no longer available.';
            }

            // product inactive
            elseif (!$product || $product->status !== 'active') {
                $item->cartStatus = 'unavailable';
                $item->cartMessage = 'This product is no longer available.';
            }

            // collection inactive
            elseif ($product->collection && $product->collection->status !== 'active') {
                $item->cartStatus = 'unavailable';
                $item->cartMessage = 'This product is no longer available.';
            } elseif (!in_array($product->availability, ['ready', 'preorder'], true)) {
                $item->cartStatus = 'unavailable';
                $item->cartMessage = 'This product is no longer available.';
            } else {
                $totalPreorder = 0;

                // Calculate remaining global preorder capacity
                if ($product->availability === 'preorder' && $variant->preorder_limit !== null) {
                    $totalPreorder = (int) ($preorderTotals[$variant->id] ?? 0);

                    $remaining = max(0, (int) $variant->preorder_limit - $totalPreorder);

                    $item->preorderRemaining = $remaining;

                    if ($remaining === 0) {
                        $item->cartStatus = 'preorder_closed';
                        $item->cartMessage = 'The pre-order limit for this variant has been reached.';
                    }
                }

                if ($item->cartStatus === 'available') {
                    $maxAllowed = $this->getEffectiveCartLimit($variant, $product, $totalPreorder);

                    $item->maxAllowedQuantity = $maxAllowed;

                    if ($maxAllowed !== null && $maxAllowed <= 0) {
                        $item->cartStatus = 'unavailable';
                        $item->cartMessage = 'This variant cannot currently be purchased.';
                    } elseif ($maxAllowed !== null && (int) $item->quantity > $maxAllowed) {
                        if ($product->availability === 'preorder' && $item->preorderRemaining !== null && (int) $item->quantity > $item->preorderRemaining) {
                            $item->cartStatus = 'unavailable';
                            $item->cartMessage = 'The selected quantity exceeds the remaining pre-order limit. Please reduce the quantity.';
                        } else {
                            // Normalize quantities when stock or the per-order limit changes.
                            CartItem::whereKey($item->id)->update([
                                'quantity' => $maxAllowed,
                            ]);

                            $item->quantity = $maxAllowed;
                        }
                    }
                }
            }

            // unselect items that are no longer available
            if ($item->cartStatus !== 'available' && $item->is_selected) {
                CartItem::whereKey($item->id)->update([
                    'is_selected' => false,
                ]);

                $item->is_selected = false;
            }

            // only valid, selected items count toward the total
            if ($variant && $item->is_selected && $item->cartStatus === 'available') {
                $total += $variant->price * $item->quantity;
            }
        }

        return view('commerce.cart.index', compact('cartItems', 'total'));
    }

    public function remove($id)
    {
        $user = Auth::user();

        CartItem::where('user_id', $user->id)->where('id', $id)->delete();

        return back()->with('success', 'Item removed');
    }

    public function increase($id)
    {
        $cart = CartItem::with('productVariant.product.collection')->where('user_id', Auth::id())->where('id', $id)->firstOrFail();

        $variant = $cart->productVariant;
        $product = $variant?->product;

        if (!$variant || !$variant->is_active || !$product || $product->status !== 'active' || ($product->collection && $product->collection->status !== 'active')) {
            return back();
        }

        if (!in_array($product->availability, ['ready', 'preorder'], true)) {
            return back();
        }

        $maxAllowed = $this->getEffectiveCartLimit($variant, $product);

        if ($maxAllowed !== null && (int) $cart->quantity + 1 > $maxAllowed) {
            // do not increase beyond the effective maximum
            return back();
        }

        $cart->increment('quantity');

        return back();
    }

    public function decrease($id)
    {
        $user = Auth::user();

        $cart = CartItem::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        if ($cart->quantity > 1) {
            $cart->decrement('quantity');
        }

        return back();
    }

    public function selectAll(Request $request)
    {
        $request->validate([
            'is_selected' => ['required', 'boolean'],
        ]);

        $cartItems = CartItem::with(['productVariant.product.collection'])
            ->where('user_id', Auth::id())
            ->get();

        $isSelected = $request->boolean('is_selected');

        foreach ($cartItems as $item) {
            $item->is_selected = $isSelected && $this->isSelectableCartItem($item);

            $item->save();
        }

        return response()->json(['success' => true]);
    }

    public function toggleSelect(Request $request, $id)
    {
        $request->validate([
            'is_selected' => ['required', 'boolean'],
        ]);

        $item = CartItem::with(['productVariant.product.collection'])
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        if (!$this->isSelectableCartItem($item)) {
            $item->is_selected = false;
            $item->save();

            return response()->json(
                [
                    'success' => false,
                    'message' => 'This item cannot be selected.',
                ],
                422,
            );
        }

        $item->is_selected = $request->boolean('is_selected');
        $item->save();

        return response()->json(['success' => true]);
    }

    private function isSelectableCartItem(CartItem $item): bool
    {
        $variant = $item->productVariant;
        $product = $variant?->product;

        if (!$variant || !$variant->is_active || !$product || $product->status !== 'active') {
            return false;
        }
        if ($product->collection && $product->collection->status !== 'active') {
            return false;
        }
        if (!in_array($product->availability, ['ready', 'preorder'], true)) {
            return false;
        }
        $maxAllowed = $this->getEffectiveCartLimit($variant, $product);

        if ($maxAllowed !== null && ($maxAllowed <= 0 || (int) $item->quantity > $maxAllowed)) {
            return false;
        }

        return true;
    }

    //   Return the total qualifying quantity already recorded for this preorder variant. Cart quantities are not counted here because they have not yet become orders
    private function getTotalPreorderQuantity(ProductVariant $variant): int
    {
        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_variant_id', $variant->id)
            ->where('order_items.fulfillment_type', 'preorder')
            ->where('orders.status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('orders.payment_status')->orWhereNotIn('orders.payment_status', ['failed', 'refunded']);
            })
            ->whereNull('orders.deleted_at')
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
            ->value('total');
    }

    //   Calculate the maximum quantity permitted for this variant
    //  in one user's cart/order line
    //  Ready stock:
    //    stock, optionally limited by max_purchase_per_order.
    //   Preorder:
    //    remaining preorder_limit, optionally limited by
    //    max_purchase_per_order

    //  A null result means there is no applicable quantity cap.
    private function getEffectiveCartLimit(ProductVariant $variant, Product $product, ?int $knownPreorderTotal = null): ?int
    {
        $limits = [];

        if ($product->availability === 'ready') {
            $limits[] = max(0, (int) $variant->stock);
        } elseif ($product->availability === 'preorder') {
            if ($variant->preorder_limit !== null) {
                $totalPreorder = $knownPreorderTotal ?? $this->getTotalPreorderQuantity($variant);

                $remaining = max(0, (int) $variant->preorder_limit - $totalPreorder);

                $limits[] = $remaining;
            }
        } else {
            return 0;
        }

        if ($variant->max_purchase_per_order !== null) {
            $limits[] = max(0, (int) $variant->max_purchase_per_order);
        }

        if ($limits === []) {
            return null;
        }

        return min($limits);
    }
}
