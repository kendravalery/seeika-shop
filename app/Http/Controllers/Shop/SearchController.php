<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $availability = $request->input('availability', 'all');

        if (!in_array($availability, ['all', 'ready', 'preorder'], true)) {
            $availability = 'all';
        }

        $sort = $request->input('sort', 'newest');

        if (!in_array($sort, ['newest', 'price_low', 'price_high'], true)) {
            $sort = 'newest';
        }

        $activeCollectionIds = Collection::where('status', 'active')->pluck('id')->map(fn($id) => (int) $id)->all();

        $products = Product::search($q, function ($meilisearch, $query, $options) use ($availability, $activeCollectionIds, $sort) {
            $filters = ['status = active'];

            if ($availability === 'ready') {
                $filters[] = 'availability = ready';
            }

            if ($availability === 'preorder') {
                $filters[] = 'availability = preorder';
            }

            if (empty($activeCollectionIds)) {
                $filters[] = 'collection_id IS NULL';
            } else {
                $filters[] = '(collection_id IS NULL OR collection_id IN [' . implode(',', $activeCollectionIds) . '])';
            }

            $options['filter'] = implode(' AND ', $filters);

            $options['sort'] = match ($sort) {
                'price_low' => ['min_price:asc'],
                'price_high' => ['min_price:desc'],
                default => ['created_at:desc'],
            };

            return $meilisearch->search($query, $options);
        })
            ->query(function ($query) {
                $query->with([
                    'images' => function ($query) {
                        $query->orderByDesc('is_primary')->orderBy('position');
                    },
                    'category',
                    'collection',
                    'variants' => function ($query) {
                        $query->where('is_active', true);
                    },
                    'options',
                ]);
            })
            ->paginate(20)
            ->withQueryString();

        $this->applyPreorderClosedStatus(new EloquentCollection($products->items()));

        return view('storefront.search.index', compact('products', 'q', 'availability', 'sort'));
    }

    private function applyPreorderClosedStatus(EloquentCollection $products): void
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
