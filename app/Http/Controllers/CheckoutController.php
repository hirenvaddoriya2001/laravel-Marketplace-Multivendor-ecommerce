<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(
        Request $request
    ): View|RedirectResponse {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'fail',
                    'Your cart is empty.'
                );
        }

        $cartData = $this->cartData($cart);

        if (
            $cartData['items']->count()
            !== count($cart)
        ) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'fail',
                    'Stock changed for an item in your cart. '
                    .'Review the cart before checkout.'
                );
        }

        return view('front.pages.checkout', [
            'pageTitle' => 'Checkout | LARAVECOM',
            'customer' => $request->user(),
            'items' => $cartData['items'],
            'subtotal' => $cartData['subtotal'],
        ]);
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'shipping_name' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_email' => [
                'required',
                'email',
                'max:255',
            ],

            'shipping_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'shipping_address' => [
                'required',
                'string',
                'max:1000',
            ],

            'shipping_city' => [
                'required',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'fail',
                    'Your cart is empty.'
                );
        }

        $order = DB::transaction(
            function () use (
                $cart,
                $request,
                $validated
            ) {
                /*
                 * Sorting ensures concurrent checkouts lock
                 * product rows in a consistent order.
                 */
                $productIds = array_map(
                    'intval',
                    array_keys($cart)
                );

                sort($productIds);

                /*
                 * Lock products until the transaction commits.
                 * This prevents two orders from buying the same
                 * final units simultaneously.
                 */
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lines = [];
                $subtotal = 0;

                foreach ($productIds as $productId) {
                    $product = $products->get($productId);

                    $quantity = (int) (
                        $cart[$productId] ?? 0
                    );

                    if (
                        ! $product
                        || ! $product->visibility
                    ) {
                        throw ValidationException::withMessages([
                            'cart' => 'A product in your cart '
                                .'is no longer available.',
                        ]);
                    }

                    if (
                        $quantity < 1
                        || ! $product->hasEnoughStock(
                            $quantity
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'cart' => $product->name
                                .' does not have enough stock.',
                        ]);
                    }

                    $unitPrice = (float) $product->price;

                    $lineTotal =
                        $unitPrice * $quantity;

                    $subtotal += $lineTotal;

                    $lines[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }

                if (empty($lines)) {
                    throw ValidationException::withMessages([
                        'cart' => 'Your cart no longer '
                            .'contains available products.',
                    ]);
                }

                $order = Order::create([
                    'order_number' => 'ORD-'
                        .now()->format('Ymd')
                        .'-'
                        .Str::upper(Str::random(8)),

                    'user_id' => $request->user()->id,
                    'status' => 'pending',
                    'payment_method' => 'cod',
                    'payment_status' => 'unpaid',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,

                    'shipping_name' =>
                        $validated['shipping_name'],

                    'shipping_email' =>
                        $validated['shipping_email'],

                    'shipping_phone' =>
                        $validated['shipping_phone'],

                    'shipping_address' =>
                        $validated['shipping_address'],

                    'shipping_city' =>
                        $validated['shipping_city'],

                    'notes' =>
                        $validated['notes'] ?? null,
                ]);

                foreach ($lines as $line) {
                    $product = $line['product'];

                    /*
                     * Save a snapshot of product information.
                     * Later product changes will not alter
                     * historical order details.
                     */
                    $order->items()->create([
                        'product_id' => $product->id,
                        'seller_id' => $product->seller_id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_image' =>
                            $product->product_image,

                        'unit_price' =>
                            $line['unit_price'],

                        'quantity' =>
                            $line['quantity'],

                        'line_total' =>
                            $line['line_total'],
                    ]);

                    /*
                     * Only tracked inventory is reduced.
                     */
                    if ($product->manage_stock) {
                        $product->decrement(
                            'stock_quantity',
                            $line['quantity']
                        );
                    }
                }

                return $order;
            },
            3
        );

        /*
         * Clear the cart only after the transaction
         * completes successfully.
         */
        session()->forget('cart');

        return redirect()
            ->route(
                'customer.orders.show',
                $order
            )
            ->with(
                'success',
                'Your order was placed successfully.'
            );
    }

    private function cartData(
        array $cart
    ): array {
        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('visibility', 1)
            ->get();

        $items = collect();

        foreach ($products as $product) {
            $quantity = (int) (
                $cart[$product->id] ?? 0
            );

            if (
                $quantity < 1
                || ! $product->hasEnoughStock(
                    $quantity
                )
            ) {
                continue;
            }

            $product->cart_quantity = $quantity;

            $product->line_total =
                (float) $product->price
                * $quantity;

            $items->push($product);
        }

        return [
            'items' => $items,
            'subtotal' => $items->sum(
                'line_total'
            ),
        ];
    }
}