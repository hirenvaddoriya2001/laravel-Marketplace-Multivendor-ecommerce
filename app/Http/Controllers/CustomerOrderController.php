<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(
        Request $request
    ): View {
        $orders = Order::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view(
            'front.pages.customer.orders.index',
            [
                'pageTitle' =>
                    'My Orders | LARAVECOM',

                'orders' => $orders,
            ]
        );
    }

    public function show(
        Request $request,
        Order $order
    ): View {
        /*
         * Prevent customers from viewing
         * another customer's order.
         */
        abort_unless(
            $order->user_id
                === $request->user()->id,
            404
        );

        $order->load('items');

        return view(
            'front.pages.customer.orders.show',
            [
                'pageTitle' =>
                    'Order '
                    .$order->order_number
                    .' | LARAVECOM',

                'order' => $order,
            ]
        );
    }
}