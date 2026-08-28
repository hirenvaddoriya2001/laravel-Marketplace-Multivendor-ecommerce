<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderActivityLog;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(
        Request $request
    ): View {
        $ordersQuery = Order::query()
            ->withCount('items')
            ->with('user');

        /*
         * Search order number, customer name,
         * or customer email.
         */
        $ordersQuery->when(
            $request->filled('q'),
            function ($query) use ($request) {
                $search = $request
                    ->string('q')
                    ->toString();

                $query->where(
                    function ($query) use (
                        $search
                    ) {
                        $query
                            ->where(
                                'order_number',
                                'like',
                                '%'.$search.'%'
                            )
                            ->orWhere(
                                'shipping_name',
                                'like',
                                '%'.$search.'%'
                            )
                            ->orWhere(
                                'shipping_email',
                                'like',
                                '%'.$search.'%'
                            );
                    }
                );
            }
        );

        $ordersQuery->when(
            $request->filled('status'),
            fn ($query) => $query->where(
                'status',
                $request->status
            )
        );

        $ordersQuery->when(
            $request->filled(
                'payment_status'
            ),
            fn ($query) => $query->where(
                'payment_status',
                $request->payment_status
            )
        );

        $ordersQuery->when(
            $request->filled(
                'fulfillment_status'
            ),
            function ($query) use ($request) {
                $query->whereHas(
                    'items',
                    fn ($items) =>
                        $items->where(
                            'fulfillment_status',
                            $request
                                ->fulfillment_status
                        )
                );
            }
        );

        return view(
            'back.pages.admin.orders.index',
            [
                'pageTitle' =>
                    'Order Management',

                'orders' => $ordersQuery
                    ->latest()
                    ->paginate(20)
                    ->withQueryString(),

                'stats' => [
                    'orders' =>
                        Order::count(),

                    'pending' =>
                        Order::where(
                            'status',
                            'pending'
                        )->count(),

                    'gross' =>
                        Order::where(
                            'status',
                            '!=',
                            'cancelled'
                        )->sum('total'),

                    'collected' =>
                        Order::where(
                            'payment_status',
                            'paid'
                        )->sum('total'),
                ],
            ]
        );
    }

    public function show(
        Order $order
    ): View {
        $order->load([
            'user',
            'items.seller',
            'activityLogs',
        ]);

        /*
         * Group order items by seller and calculate
         * each seller's non-cancelled gross sales.
         */
        $sellerSummaries = $order
            ->items
            ->groupBy('seller_id')
            ->map(
                function ($items) {
                    return [
                        'seller' =>
                            $items
                                ->first()
                                ->seller,

                        'items' =>
                            $items->count(),

                        'quantity' =>
                            $items->sum(
                                'quantity'
                            ),

                        'gross' =>
                            $items
                                ->where(
                                    'fulfillment_status',
                                    '!=',
                                    'cancelled'
                                )
                                ->sum(
                                    'line_total'
                                ),
                    ];
                }
            );

        return view(
            'back.pages.admin.orders.show',
            [
                'pageTitle' =>
                    'Order '
                    .$order->order_number,

                'order' => $order,

                'sellerSummaries' =>
                    $sellerSummaries,
            ]
        );
    }

    public function updateItemStatus(
        Request $request,
        OrderItem $item
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',

                Rule::in([
                    'pending',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                ]),
            ],

            /*
             * Admin overrides require an explanation.
             */
            'note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(
            function () use (
                $item,
                $validated
            ) {
                $lockedItem = OrderItem::query()
                    ->with('order')
                    ->lockForUpdate()
                    ->findOrFail($item->id);

                $oldStatus =
                    $lockedItem
                        ->fulfillment_status;

                $newStatus =
                    $validated['status'];

                if ($oldStatus === $newStatus) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'The item already has '
                            .'that status.',
                    ]);
                }

                $product = null;

                if ($lockedItem->product_id) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find(
                            $lockedItem
                                ->product_id
                        );
                }

                /*
                 * Cancelling an active item restores
                 * tracked inventory one time.
                 */
                if (
                    $newStatus === 'cancelled'
                    && ! $lockedItem
                        ->stock_restored_at
                ) {
                    if (
                        $product
                        && $product
                            ->manage_stock
                    ) {
                        $product->increment(
                            'stock_quantity',
                            $lockedItem
                                ->quantity
                        );
                    }

                    $lockedItem
                        ->stock_restored_at =
                            now();
                }

                /*
                 * Reversing a cancellation deducts
                 * inventory again.
                 */
                if (
                    $oldStatus === 'cancelled'
                    && $newStatus !== 'cancelled'
                ) {
                    if (! $product) {
                        throw ValidationException::withMessages([
                            'status' =>
                                'The cancellation '
                                .'cannot be reversed '
                                .'because the original '
                                .'product no longer exists.',
                        ]);
                    }

                    if (
                        $product->manage_stock
                    ) {
                        if (
                            $product
                                ->stock_quantity
                            < $lockedItem
                                ->quantity
                        ) {
                            throw ValidationException::withMessages([
                                'status' =>
                                    'The cancellation '
                                    .'cannot be reversed '
                                    .'because stock is '
                                    .'no longer sufficient.',
                            ]);
                        }

                        $product->decrement(
                            'stock_quantity',
                            $lockedItem
                                ->quantity
                        );
                    }

                    $lockedItem
                        ->stock_restored_at =
                            null;
                }

                $lockedItem->update([
                    'fulfillment_status' =>
                        $newStatus,

                    'status_updated_at' =>
                        now(),

                    'stock_restored_at' =>
                        $lockedItem
                            ->stock_restored_at,
                ]);

                OrderActivityLog::create([
                    'order_id' =>
                        $lockedItem->order_id,

                    'order_item_id' =>
                        $lockedItem->id,

                    'actor_type' => 'admin',

                    'actor_id' =>
                        auth('admin')->id(),

                    'action' =>
                        'fulfillment_status_changed',

                    'from_value' =>
                        $oldStatus,

                    'to_value' =>
                        $newStatus,

                    'note' =>
                        $validated['note'],
                ]);

                $this->refreshOrderStatus(
                    $lockedItem->order
                );
            },
            3
        );

        return back()->with(
            'success',
            'Item fulfillment status updated.'
        );
    }

    public function updatePaymentStatus(
        Request $request,
        Order $order
    ): RedirectResponse {
        $validated = $request->validate([
            'payment_status' => [
                'required',

                Rule::in([
                    'unpaid',
                    'paid',
                    'refunded',
                ]),
            ],

            'note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(
            function () use (
                $order,
                $validated
            ) {
                $lockedOrder = Order::query()
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                $oldStatus =
                    $lockedOrder
                        ->payment_status;

                if (
                    $oldStatus
                    === $validated[
                        'payment_status'
                    ]
                ) {
                    throw ValidationException::withMessages([
                        'payment_status' =>
                            'The order already has '
                            .'that payment status.',
                    ]);
                }

                $lockedOrder->update([
                    'payment_status' =>
                        $validated[
                            'payment_status'
                        ],
                ]);

                OrderActivityLog::create([
                    'order_id' =>
                        $lockedOrder->id,

                    'actor_type' => 'admin',

                    'actor_id' =>
                        auth('admin')->id(),

                    'action' =>
                        'payment_status_changed',

                    'from_value' =>
                        $oldStatus,

                    'to_value' =>
                        $validated[
                            'payment_status'
                        ],

                    'note' =>
                        $validated['note'],
                ]);
            },
            3
        );

        return back()->with(
            'success',
            'Payment status updated.'
        );
    }

    private function refreshOrderStatus(
        Order $order
    ): void {
        $statuses = $order->items()
            ->pluck('fulfillment_status');

        if (
            $statuses->every(
                fn ($status) =>
                    $status === 'cancelled'
            )
        ) {
            $status = 'cancelled';
        } elseif (
            $statuses->every(
                fn ($status) => in_array(
                    $status,
                    [
                        'delivered',
                        'cancelled',
                    ],
                    true
                )
            )
        ) {
            $status = 'delivered';
        } elseif (
            $statuses->contains('shipped')
        ) {
            $status = 'shipped';
        } elseif (
            $statuses->contains('processing')
            || $statuses->contains(
                'delivered'
            )
        ) {
            $status = 'processing';
        } else {
            $status = 'pending';
        }

        /*
         * Do not automatically overwrite payment status.
         * Payment changes belong to the separate admin
         * payment action.
         */
        $order->update([
            'status' => $status,
        ]);
    }
}