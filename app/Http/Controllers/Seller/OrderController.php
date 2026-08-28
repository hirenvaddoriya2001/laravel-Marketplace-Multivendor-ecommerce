<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\OrderActivityLog;

class OrderController extends Controller
{
    public function index(): View
    {
        $sellerId = auth('seller')->id();

        /*
         * Load only orders containing products
         * belonging to the authenticated seller.
         */
        $orders = Order::query()
            ->whereHas(
                'items',
                function ($query) use ($sellerId) {
                    $query->where(
                        'seller_id',
                        $sellerId
                    );
                }
            )
            ->with([
                'items' => function ($query) use (
                    $sellerId
                ) {
                    $query->where(
                        'seller_id',
                        $sellerId
                    );
                },
            ])
            ->latest()
            ->paginate(15);

        return view(
            'back.pages.seller.orders.index',
            [
                'pageTitle' => 'My Orders',
                'orders' => $orders,
            ]
        );
    }

    public function show(Order $order): View
    {
        $sellerId = auth('seller')->id();

        /*
         * Never pass another seller's items
         * into this view.
         */
        $items = $order->items()
            ->where('seller_id', $sellerId)
            ->get();

        abort_if($items->isEmpty(), 404);

        return view(
            'back.pages.seller.orders.show',
            [
                'pageTitle' =>
                    'Order '.$order->order_number,

                'order' => $order,
                'items' => $items,

                'sellerTotal' =>
                    $items->sum('line_total'),
            ]
        );
    }

    public function updateStatus(
        Request $request,
        OrderItem $item
    ): RedirectResponse {
        abort_unless(
            (int) $item->seller_id
                === (int) auth('seller')->id(),
            404
        );

        $allowedTransitions = [
            'pending' => [
                'processing',
                'cancelled',
            ],

            'processing' => [
                'shipped',
                'cancelled',
            ],

            'shipped' => [
                'delivered',
            ],

            'delivered' => [],
            'cancelled' => [],
        ];

        $request->validate(
            [
                'status' => [
                    'required',

                    Rule::in(
                        $allowedTransitions[
                            $item->fulfillment_status
                        ] ?? []
                    ),
                ],
            ],
            [
                'status.in' =>
                    'That status change is not allowed.',
            ]
        );

        DB::transaction(
            function () use (
                $item,
                $request
            ) {
                /*
                 * Reload and lock the order item.
                 * This prevents simultaneous status changes.
                 */
                $lockedItem = OrderItem::query()
                    ->with('order')
                    ->lockForUpdate()
                    ->findOrFail($item->id);

                abort_unless(
                    (int) $lockedItem->seller_id
                        === (int) auth('seller')->id(),
                    404
                );

                /*
                 * Recheck the transition after acquiring
                 * the database lock.
                 */
                $transitions = [
                    'pending' => [
                        'processing',
                        'cancelled',
                    ],

                    'processing' => [
                        'shipped',
                        'cancelled',
                    ],

                    'shipped' => [
                        'delivered',
                    ],

                    'delivered' => [],
                    'cancelled' => [],
                ];

                $newStatus = $request
                    ->string('status')
                    ->toString();
                $oldStatus =
                 $lockedItem->fulfillment_status;

                abort_unless(
                    in_array(
                        $newStatus,
                        $transitions[
                            $lockedItem
                                ->fulfillment_status
                        ] ?? [],
                        true
                    ),
                    422
                );

                /*
                 * Restore tracked stock when cancelled.
                 * stock_restored_at prevents restoring
                 * the same quantity more than once.
                 */
                if (
                    $newStatus === 'cancelled'
                    && ! $lockedItem->stock_restored_at
                ) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find(
                            $lockedItem->product_id
                        );

                    if (
                        $product
                        && $product->manage_stock
                    ) {
                        $product->increment(
                            'stock_quantity',
                            $lockedItem->quantity
                        );
                    }

                    $lockedItem->stock_restored_at =
                        now();
                }

                $lockedItem->fulfillment_status =
                    $newStatus;

                $lockedItem->status_updated_at =
                    now();

                $lockedItem->save();

                //order activity log admin panel
                
                OrderActivityLog::create([
                'order_id' =>
                    $lockedItem->order_id,

                'order_item_id' =>
                    $lockedItem->id,

                'actor_type' => 'seller',

                'actor_id' =>
                    auth('seller')->id(),

                'action' =>
                    'fulfillment_status_changed',

                'from_value' =>
                    $oldStatus,

                'to_value' =>
                    $newStatus,

                'note' =>
                    'Seller fulfillment update.',
            ]);

                $this->refreshOrderStatus(
                    $lockedItem->order
                );
            },
            3
        );

        return back()->with(
            'success',
            'Item status updated successfully.'
        );
    }

    private function refreshOrderStatus(
        Order $order
    ): void {
        $statuses = $order->items()
            ->pluck('fulfillment_status');

        /*
         * Determine the overall order status from
         * every seller's order items.
         */
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
            $statuses->contains('delivered')
        ) {
            /*
             * Some items are delivered while
             * other sellers are still working.
             */
            $status = 'processing';
        } elseif (
            $statuses->contains('processing')
        ) {
            $status = 'processing';
        } else {
            $status = 'pending';
        }

       $changes = [
                'status' => $status,
        ];

        if (
            $order->payment_status
            !== 'refunded'
        ) {
            $changes['payment_status'] =
                $status === 'delivered'
                    ? 'paid'
                    : 'unpaid';
        }

        $order->update($changes);
    }
}