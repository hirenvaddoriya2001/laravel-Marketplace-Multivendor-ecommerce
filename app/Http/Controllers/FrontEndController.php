<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\OrderItem;

class FrontEndController extends Controller
{
    public function homePage(Request $request)
    {
        $products = Product::query()
            ->where('visibility', 1)
            ->latest()
            ->paginate(12);

        return view('front.pages.home', [
            'pageTitle' => 'LARAVECOM | Online Shopping Website',
            'products' => $products,
        ]);
    }

        public function productDetails(
            Product $product
        ) {
            abort_unless(
                $product->visibility,
                404
            );

            $product->load([
                'images',
                'categoryDetails',
                'reviews.user',
            ]);

            $product->loadAvg(
                'reviews',
                'rating'
            );

            $product->loadCount(
                'reviews'
            );

            $isWishlisted = false;
            $canReview = false;
            $customerReview = null;

            if (auth()->check()) {
                $customerId = auth()->id();

                $isWishlisted = $product
                    ->wishlists()
                    ->where(
                        'user_id',
                        $customerId
                    )
                    ->exists();

                $canReview = OrderItem::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->where(
                        'fulfillment_status',
                        'delivered'
                    )
                    ->whereHas(
                        'order',
                        fn ($query) =>
                            $query->where(
                                'user_id',
                                $customerId
                            )
                    )
                    ->exists();

                $customerReview = $product
                    ->reviews()
                    ->where(
                        'user_id',
                        $customerId
                    )
                    ->first();
            }

            return view(
                'front.pages.product-details',
                [
                    'pageTitle' =>
                        $product->name
                        .' | LARAVECOM',

                    'product' => $product,

                    'isWishlisted' =>
                        $isWishlisted,

                    'canReview' =>
                        $canReview,

                    'customerReview' =>
                        $customerReview,
                ]
            );
        }
}