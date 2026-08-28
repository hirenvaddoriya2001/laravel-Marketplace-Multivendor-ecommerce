@extends('back.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-12">
            <div class="title">
                <h4>My orders</h4>
            </div>

            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a
                            href="{{ route(
                                'seller.home'
                            ) }}"
                        >
                            Home
                        </a>
                    </li>

                    <li
                        class="breadcrumb-item active"
                    >
                        Orders
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="card-box mb-30">
    <div class="pd-20">
        <h4 class="text-blue h4">
            Customer orders containing
            your products
        </h4>
    </div>

    <div class="pb-20">
        @if($orders->isEmpty())
            <div class="px-4 pb-4 text-muted">
                No orders have been placed
                for your products.
            </div>
        @else
            <div class="table-responsive">
                <table class="table hover nowrap">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Your items</th>
                            <th>Your total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    {{ $order->order_number }}
                                </td>

                                <td>
                                    {{ $order->created_at
                                        ->format(
                                            'd M Y, H:i'
                                        ) }}
                                </td>

                                <td>
                                    {{ $order->shipping_name }}
                                </td>

                                <td>
                                    {{ $order->items
                                        ->sum('quantity') }}
                                </td>

                                <td>
                                    ${{ number_format(
                                        $order->items
                                            ->sum(
                                                'line_total'
                                            ),
                                        2
                                    ) }}
                                </td>

                                <td>
                                    @foreach(
                                        $order->items
                                            ->groupBy(
                                                'fulfillment_status'
                                            )
                                        as $status => $statusItems
                                    )
                                        <span
                                            class="badge badge-secondary"
                                        >
                                            {{ ucfirst(
                                                $status
                                            ) }}:
                                            {{ $statusItems
                                                ->count() }}
                                        </span>
                                    @endforeach
                                </td>

                                <td>
                                    <a
                                        href="{{ route(
                                            'seller.orders.show',
                                            $order
                                        ) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection