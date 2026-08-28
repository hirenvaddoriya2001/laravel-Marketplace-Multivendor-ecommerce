@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >
            <div>
                <h2>
                    Order {{ $order->order_number }}
                </h2>

                <p class="text-muted mb-0">
                    Placed
                    {{ $order->created_at->format(
                        'd M Y, H:i'
                    ) }}
                </p>
            </div>

            <a
                href="{{ route(
                    'customer.orders.index'
                ) }}"
                class="btn btn-outline-secondary"
            >
                All orders
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">
                            Ordered products
                        </h4>

                        <div class="table-responsive">
                           @php
                            $statusColors = [
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info text-dark',
                                'shipped' => 'bg-primary',
                                'delivered' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ];
                        @endphp

                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->product_image)
                                                <img
                                                    src="{{ asset(
                                                        'images/products/'
                                                        .$item->product_image
                                                    ) }}"
                                                    width="64"
                                                    class="rounded me-2"
                                                    alt="{{ $item->product_name }}"
                                                >
                                            @endif

                                            {{ $item->product_name }}

                                            @if($item->product_sku)
                                                <small
                                                    class="d-block text-muted"
                                                >
                                                    SKU:
                                                    {{ $item->product_sku }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            ${{ number_format(
                                                $item->unit_price,
                                                2
                                            ) }}
                                        </td>

                                        <td>
                                            {{ $item->quantity }}
                                        </td>

                                        <td>
                                            <span
                                                class="badge {{ $statusColors[$item->fulfillment_status] ?? 'bg-secondary' }}"
                                            >
                                                {{ ucfirst(
                                                    $item->fulfillment_status
                                                        ?? 'pending'
                                                ) }}
                                            </span>
                                        </td>

                                        <td>
                                            ${{ number_format(
                                                $item->line_total,
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="mb-3">
                            Order summary
                        </h4>

                        <div
                            class="d-flex justify-content-between mb-2"
                        >
                            <span>Status</span>

                            <strong>
                                {{ ucfirst($order->status) }}
                            </strong>
                        </div>

                        <div
                            class="d-flex justify-content-between mb-2"
                        >
                            <span>Payment</span>
                            <strong>Cash on Delivery</strong>
                        </div>

                        <div
                            class="d-flex justify-content-between mb-2"
                        >
                            <span>Payment status</span>

                            <strong>
                                {{ ucfirst(
                                    $order->payment_status
                                ) }}
                            </strong>
                        </div>

                        <hr>

                        <div
                            class="d-flex justify-content-between"
                        >
                            <span>Total</span>

                            <strong class="theme-color">
                                ${{ number_format(
                                    $order->total,
                                    2
                                ) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">
                            Delivery details
                        </h4>

                        <p class="mb-1">
                            <strong>
                                {{ $order->shipping_name }}
                            </strong>
                        </p>

                        <p class="mb-1">
                            {{ $order->shipping_email }}
                        </p>

                        <p class="mb-1">
                            {{ $order->shipping_phone }}
                        </p>

                        <p class="mb-0">
                            {{ $order->shipping_address }},
                            {{ $order->shipping_city }}
                        </p>

                        @if($order->notes)
                            <hr>

                            <p class="mb-0">
                                <strong>Notes:</strong>
                                {{ $order->notes }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection