<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(
        Request $request
    ): View {
        $validated = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],

            'category' => [
                'nullable',
                'string',
                'max:150',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'in_stock' => [
                'nullable',
                'boolean',
            ],

            'sort' => [
                'nullable',
                'in:price_asc,price_desc',
            ],
        ]);

        $selectedCategory = null;

        if (! empty($validated['category'])) {
            $selectedCategory = Category::query()
                ->where(
                    'category_slug',
                    $validated['category']
                )
                ->first();
        }

        $products = Product::query()
            ->where('visibility', 1)
            ->with('categoryDetails')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')

            /*
             * Keyword search.
             */
            ->when(
                ! empty($validated['q']),
                function ($query) use ($validated) {
                    $search = $validated['q'];

                    $query->where(
                        function ($query) use (
                            $search
                        ) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'summary',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'sku',
                                    'like',
                                    '%'.$search.'%'
                                );
                        }
                    );
                }
            )

            /*
             * Category filter.
             */
            ->when(
                $selectedCategory,
                fn ($query) =>
                    $query->where(
                        'category',
                        $selectedCategory->id
                    )
            )

            /*
             * Minimum price.
             */
            ->when(
                isset($validated['min_price']),
                fn ($query) =>
                    $query->where(
                        'price',
                        '>=',
                        $validated['min_price']
                    )
            )

            /*
             * Maximum price.
             */
            ->when(
                isset($validated['max_price']),
                fn ($query) =>
                    $query->where(
                        'price',
                        '<=',
                        $validated['max_price']
                    )
            )

            /*
             * Tracked products require positive stock.
             * Always-available products remain included.
             */
            ->when(
                ! empty($validated['in_stock']),
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query
                                ->where(
                                    'manage_stock',
                                    false
                                )
                                ->orWhere(
                                    'stock_quantity',
                                    '>',
                                    0
                                );
                        }
                    );
                }
            );

        /*
         * Apply sorting.
         */
        if (
            ($validated['sort'] ?? null)
            === 'price_asc'
        ) {
            $products->orderBy(
                'price',
                'asc'
            );
        } elseif (
            ($validated['sort'] ?? null)
            === 'price_desc'
        ) {
            $products->orderBy(
                'price',
                'desc'
            );
        } else {
            $products->latest();
        }

        return view(
            'front.pages.shop',
            [
                'pageTitle' =>
                    'Shop | LARAVECOM',

                'products' =>
                    $products
                        ->paginate(12)
                        ->withQueryString(),

                'categories' =>
                    Category::query()
                        ->orderBy('ordering')
                        ->get(),

                'selectedCategory' =>
                    $selectedCategory,
            ]
        );
    }
}