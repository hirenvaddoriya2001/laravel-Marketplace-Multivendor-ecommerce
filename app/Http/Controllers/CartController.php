<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view(
            'front.pages.cart',
            array_merge(
                [
                    'pageTitle' => 'Shopping Cart | LARAVECOM',
                ],
                $this->cartData()
            )
        );
    }

    public function store(
        Request $request,
        Product $product
    ): RedirectResponse {
        abort_unless($product->visibility, 404);

        $validated = $request->validate([
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:99',
            ],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $cart = session('cart', []);

        $currentQuantity = (int) (
            $cart[$product->id] ?? 0
        );

        $newQuantity = $currentQuantity + $quantity;

        if (! $product->isInStock()) {
            return back()->with(
                'fail',
                'This product is currently out of stock.'
            );
        }

        if (! $product->hasEnoughStock($newQuantity)) {
            return back()->with(
                'fail',
                'Only '.$product->stock_quantity
                .' unit(s) of '.$product->name
                .' are available.'
            );
        }

        $cart[$product->id] = $newQuantity;

        session(['cart' => $cart]);

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                $product->name.' was added to your cart.'
            );
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
        ]);

        $cart = session('cart', []);

        if (! array_key_exists($product->id, $cart)) {
            return back()->with(
                'fail',
                'The cart item was not found.'
            );
        }

        $quantity = (int) $validated['quantity'];

        if (! $product->isInStock()) {
            unset($cart[$product->id]);

            session(['cart' => $cart]);

            return back()->with(
                'fail',
                $product->name
                .' is out of stock and was removed.'
            );
        }

        if (! $product->hasEnoughStock($quantity)) {
            return back()->with(
                'fail',
                'Only '.$product->stock_quantity
                .' unit(s) of '.$product->name
                .' are available.'
            );
        }

        $cart[$product->id] = $quantity;

        session(['cart' => $cart]);

        return back()->with(
            'success',
            'Cart quantity updated.'
        );
    }

    public function destroy(
        Product $product
    ): RedirectResponse {
        $cart = session('cart', []);

        unset($cart[$product->id]);

        session(['cart' => $cart]);

        return back()->with(
            'success',
            'Product removed from your cart.'
        );
    }

    private function cartData(): array
    {
        $cart = session('cart', []);

        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('visibility', 1)
            ->get();

        $validCart = [];
        $items = collect();

        foreach ($products as $product) {
            $requestedQuantity = max(
                1,
                (int) ($cart[$product->id] ?? 1)
            );

            if (! $product->isInStock()) {
                continue;
            }

            $quantity = $product->manage_stock
                ? min(
                    $requestedQuantity,
                    $product->stock_quantity
                )
                : min($requestedQuantity, 99);

            if ($quantity < 1) {
                continue;
            }

            $validCart[$product->id] = $quantity;

            $product->cart_quantity = $quantity;

            $product->line_total =
                (float) $product->price * $quantity;

            $items->push($product);
        }

        if ($validCart !== $cart) {
            session(['cart' => $validCart]);
        }

        return [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ];
    }
}