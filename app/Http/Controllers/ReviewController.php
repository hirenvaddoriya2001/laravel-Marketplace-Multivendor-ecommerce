<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(
        Request $request,
        Product $product
    ): RedirectResponse {
        abort_unless(
            $product->visibility,
            404
        );

        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'comment' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ]);

        /*
         * Only customers with a delivered order item
         * for this product can review it.
         */
        $hasPurchased = OrderItem::query()
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
                        $request->user()->id
                    )
            )
            ->exists();

        if (! $hasPurchased) {
            return back()->with(
                'fail',
                'Only customers who received this '
                .'product can review it.'
            );
        }

        /*
         * updateOrCreate provides one review per
         * customer/product and also allows editing.
         */
        Review::updateOrCreate(
            [
                'user_id' =>
                    $request->user()->id,

                'product_id' =>
                    $product->id,
            ],
            [
                'rating' =>
                    $validated['rating'],

                'comment' =>
                    $validated['comment'],
            ]
        );

        return back()->with(
            'success',
            'Your review was saved.'
        );
    }

    public function destroy(
        Request $request,
        Review $review
    ): RedirectResponse {
        abort_unless(
            $review->user_id
                === $request->user()->id,
            404
        );

        $review->delete();

        return back()->with(
            'success',
            'Your review was deleted.'
        );
    }
}