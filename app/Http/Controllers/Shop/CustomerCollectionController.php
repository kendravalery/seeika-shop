<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CustomerCollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::query()->where('status', 'active')->latest()->paginate(12);

        return view('storefront.collections.index', compact('collections'));
    }

    public function newItems()
    {
        $sort = request()->query('sort', 'newest');

        if (!in_array($sort, ['newest', 'price_low', 'price_high'], true)) {
            $sort = 'newest';
        }
        $availability = request()->query('availability', 'all');

        if (!in_array($availability, ['all', 'ready', 'preorder'], true)) {
            $availability = 'all';
        }

        $productsQuery = Product::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('collection_id')->orWhereHas('collection', function ($collectionQuery) {
                    $collectionQuery->where('status', 'active');
                });
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['images', 'variants', 'options']);

        if ($availability !== 'all') {
            $productsQuery->where('availability', $availability);
        }

        $this->applyProductSorting($productsQuery, $sort);

        $products = $productsQuery->paginate(12)->withQueryString();

        $this->applyPreorderClosedStatus($products);

        return view('storefront.collections.show', [
            'title' => 'New Items',
            'banner' => null,
            'description' => null,
            'products' => $products,
            'availability' => $availability,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug)
    {
        $sort = request()->query('sort', 'newest');

        if (!in_array($sort, ['newest', 'price_low', 'price_high'], true)) {
            $sort = 'newest';
        }
        $availability = request()->query('availability', 'all');

        if (!in_array($availability, ['all', 'ready', 'preorder'], true)) {
            $availability = 'all';
        }

        // Find an active collection first.
        $collection = Collection::query()->where('slug', $slug)->where('status', 'active')->first();

        if ($collection) {
            $productsQuery = $collection
                ->products()
                ->where('status', 'active')
                ->with(['images', 'variants', 'options']);

            if ($availability !== 'all') {
                $productsQuery->where('availability', $availability);
            }
            $this->applyProductSorting($productsQuery, $sort);

            $products = $productsQuery->paginate(12)->withQueryString();

            $this->applyPreorderClosedStatus($products);

            return view('storefront.collections.show', [
                'title' => $collection->name,
                'banner' => $collection->banner,
                'description' => $collection->description,
                'products' => $products,
                'availability' => $availability,
                'sort' => $sort,
            ]);
        }

        // If no active collection matches, find a category.
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $productsQuery = $category
            ->products()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('collection_id')->orWhereHas('collection', function ($collectionQuery) {
                    $collectionQuery->where('status', 'active');
                });
            })
            ->with(['images', 'variants', 'options']);

        if ($availability !== 'all') {
            $productsQuery->where('availability', $availability);
        }

        $this->applyProductSorting($productsQuery, $sort);

        $products = $productsQuery->paginate(12)->withQueryString();

        $this->applyPreorderClosedStatus($products);

        return view('storefront.collections.show', [
            'title' => $category->name,
            'banner' => null,
            'description' => null,
            'products' => $products,
            'availability' => $availability,
            'sort' => $sort,
        ]);
    }

    private function applyPreorderClosedStatus($products): void
    {
        $productCollection = $products->getCollection();

        // Only preorder products need their preorder quota checked.
        $preorderProducts = $productCollection->where('availability', 'preorder');

        $variantIds = $preorderProducts->flatMap(fn($product) => $product->variants->where('is_active', true)->pluck('id'))->unique()->values();

        if ($variantIds->isEmpty()) {
            foreach ($productCollection as $product) {
                $product->setAttribute('preorder_closed', false);
            }

            return;
        }

        // Count accepted preorder quantities, subtracting refunded quantities.
        $orderedQuantities = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.product_variant_id', $variantIds)
            ->where('order_items.fulfillment_type', 'preorder')
            ->whereNull('orders.deleted_at')
            ->where(function ($query) {
                $query->whereNull('orders.status')->orWhere('orders.status', '!=', 'cancelled');
            })
            ->where(function ($query) {
                $query->whereNull('orders.payment_status')->orWhereNotIn('orders.payment_status', ['failed', 'refunded']);
            })
            ->selectRaw(
                'order_items.product_variant_id,
                SUM(GREATEST(
                    order_items.quantity
                    - COALESCE(order_items.refunded_quantity, 0),
                    0
                )) AS ordered_quantity',
            )
            ->groupBy('order_items.product_variant_id')
            ->get()
            ->keyBy('product_variant_id')
            ->map(fn($row) => (int) $row->ordered_quantity);

        foreach ($productCollection as $product) {
            $activeVariants = $product->variants->where('is_active', true);

            $preorderClosed =
                $product->availability === 'preorder' &&
                $activeVariants->isNotEmpty() &&
                $activeVariants->every(function ($variant) use ($orderedQuantities) {
                    $limit = $variant->preorder_limit;

                    return $limit !== null && $orderedQuantities->get($variant->id, 0) >= $limit;
                });

            $product->setAttribute('preorder_closed', $preorderClosed);
        }
    }
    private function applyProductSorting($query, string $sort)
    {
        $query->withMin(
            [
                'variants as min_price' => fn($variantQuery) => $variantQuery->where('is_active', true),
            ],
            'price',
        );

        return match ($sort) {
            'price_low' => $query->orderByRaw('min_price IS NULL ASC')->orderBy('min_price', 'asc')->orderByDesc('created_at'),

            'price_high' => $query->orderByRaw('min_price IS NULL ASC')->orderBy('min_price', 'desc')->orderByDesc('created_at'),

            default => $query->latest('created_at'),
        };
    }
}
