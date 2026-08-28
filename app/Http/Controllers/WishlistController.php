<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(
        Request $request
    ): View {
        $wishlists = Wishlist::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->with([
                'product' => function ($query) {
                    $query
                        ->withAvg(
                            'reviews',
                            'rating'
                        )
                        ->withCount(
                            'reviews'
                        );
                },
            ])
            ->latest()
            ->paginate(12);

        return view(
            'front.pages.customer.wishlist',
            [
                'pageTitle' =>
                    'My Wishlist | LARAVECOM',

                'wishlists' => $wishlists,
            ]
        );
    }

    public function store(
        Request $request,
        Product $product
    ): RedirectResponse {
        abort_unless(
            $product->visibility,
            404
        );

        Wishlist::firstOrCreate([
            'user_id' =>
                $request->user()->id,

            'product_id' =>
                $product->id,
        ]);

        return back()->with(
            'success',
            'Product added to your wishlist.'
        );
    }

    public function destroy(
        Request $request,
        Product $product
    ): RedirectResponse {
        Wishlist::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->where(
                'product_id',
                $product->id
            )
            ->delete();

        return back()->with(
            'success',
            'Product removed from your wishlist.'
        );
    }
}