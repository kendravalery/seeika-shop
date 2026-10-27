<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function continueToPayment(Request $request)
    {
        $validated = $request->validate([
            'shipping' => ['required', 'in:jne_reg,jnt,sicepat'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first();

        if (!$defaultAddress) {
            return back()->with('error', 'Please select a shipping address.')->withInput();
        }

        $cartItems = $this->getSelectedCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one product.');
        }

        $invalidItems = $this->findInvalidItems($cartItems);

        if ($invalidItems->isNotEmpty()) {
            $this->deselectInvalidItems($invalidItems);

            return redirect()->route('cart.index')->with('error', 'Some selected items are unavailable or their preorder quota is insufficient. Please review your cart.');
        }

        // Save validated checkout details for the payment page.
        session([
            'checkout_note' => $validated['note'] ?? '',
            'shipping' => $validated['shipping'],
        ]);

        return redirect()->route('payment.index');
    }

    public function index()
    {
        $user = Auth::user();

        $shipping = session('shipping');

        // Ensure checkout details have been submitted.
        if (!in_array($shipping, ['jne_reg', 'jnt', 'sicepat'], true)) {
            return redirect()->route('checkout')->with('error', 'Please select a shipping method.');
        }

        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first();

        if (!$defaultAddress) {
            return redirect()->route('checkout')->with('error', 'Please select a shipping address.');
        }

        // Retrieve the selected cart items.
        $cartItems = $this->getSelectedCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one product.');
        }

        // Revalidate stock and preorder quotas before displaying payment.
        $invalidItems = $this->findInvalidItems($cartItems);

        if ($invalidItems->isNotEmpty()) {
            $this->deselectInvalidItems($invalidItems);

            return redirect()->route('cart.index')->with('error', 'Some selected items are unavailable or their preorder quota is insufficient. Please review your cart.');
        }

        // Calculate the subtotal from variant prices.
        $total = $cartItems->sum(function ($item) {
            return $item->productVariant->price * $item->quantity;
        });

        $shippingCosts = [
            'jne_reg' => 18000,
            'jnt' => 20000,
            'sicepat' => 19000,
        ];

        $shippingCost = $shippingCosts[$shipping];
        $grandTotal = $total + $shippingCost;

        return view('commerce.payment.index', compact('cartItems', 'total', 'shipping', 'shippingCost', 'grandTotal', 'defaultAddress'));
    }

    public function pay()
    {
        // Add payment processing when implemented.
    }

    private function getSelectedCartItems(int $userId)
    {
        return CartItem::with(['productVariant.product.images', 'productVariant.optionValues.image', 'productVariant.product.collection'])
            ->where('user_id', $userId)
            ->where('is_selected', true)
            ->get();
    }

    private function findInvalidItems($cartItems)
    {
        $invalidItemIds = [];
        $validItems = collect();

        foreach ($cartItems as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;

            $isInvalid = !$variant || !$variant->is_active || !$product || $product->status !== 'active' || !in_array($product->availability, ['ready', 'preorder'], true) || ($product->collection && $product->collection->status !== 'active') || (int) $item->quantity < 1;

            if ($isInvalid) {
                $invalidItemIds[(int) $item->id] = true;
                continue;
            }

            $validItems->push($item);
        }

        // Group selected items by variant.
        $variantGroups = $validItems->groupBy(fn($item) => (int) $item->productVariant->id);

        foreach ($variantGroups as $items) {
            $variant = $items->first()->productVariant;
            $product = $variant->product;

            $requestedQuantity = (int) $items->sum(fn($item) => (int) $item->quantity);

            $isOverLimit = $variant->max_purchase_per_order !== null && $requestedQuantity > (int) $variant->max_purchase_per_order;

            $isOutOfStock = $product->availability === 'ready' && $requestedQuantity > max(0, (int) $variant->stock);

            if ($isOverLimit || $isOutOfStock) {
                foreach ($items as $item) {
                    $invalidItemIds[(int) $item->id] = true;
                }
            }
        }

        // Get preorder variants with a total quantity limit.
        $limitedPreorderVariantIds = $variantGroups
            ->filter(function ($items) {
                $variant = $items->first()->productVariant;
                $product = $variant->product;

                return $product->availability === 'preorder' && $variant->preorder_limit !== null;
            })
            ->keys()
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $preorderTotals = collect();

        if (!empty($limitedPreorderVariantIds)) {
            // Count quantities from existing accepted preorder orders.
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

        foreach ($variantGroups as $variantId => $items) {
            $variant = $items->first()->productVariant;
            $product = $variant->product;

            if ($product->availability !== 'preorder' || $variant->preorder_limit === null) {
                continue;
            }

            $totalPreordered = (int) data_get($preorderTotals->get($variantId), 'total', 0);

            $remainingPreorder = max(0, (int) $variant->preorder_limit - $totalPreordered);

            $requestedQuantity = (int) $items->sum(fn($item) => (int) $item->quantity);

            // Reject all selected items for a variant if its quota is insufficient.
            if ($remainingPreorder <= 0 || $requestedQuantity > $remainingPreorder) {
                foreach ($items as $item) {
                    $invalidItemIds[(int) $item->id] = true;
                }
            }
        }

        return $cartItems->filter(fn($item) => isset($invalidItemIds[(int) $item->id]))->values();
    }

    private function deselectInvalidItems($invalidItems): void
    {
        if ($invalidItems->isEmpty()) {
            return;
        }

        CartItem::where('user_id', Auth::id())
            ->whereIn('id', $invalidItems->modelKeys())
            ->update([
                'is_selected' => false,
            ]);
    }
}
