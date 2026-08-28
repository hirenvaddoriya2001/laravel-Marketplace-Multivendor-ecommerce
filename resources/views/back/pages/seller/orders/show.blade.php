@extends('back.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-8">
            <div class="title">
                <h4>
                    Order {{ $order->order_number }}
                </h4>
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

                    <li class="breadcrumb-item">
                        <a
                            href="{{ route(
                                'seller.orders.index'
                            ) }}"
                        >
                            Orders
                        </a>
                    </li>

                    <li
                        class="breadcrumb-item active"
                    >
                        {{ $order->order_number }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-lg-8 mb-30">
        <div class="card-box p-4">
            <h4 class="mb-3">
                Your products in this order
            </h4>

            @php
                $nextStatuses = [
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
            @endphp

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    @if(
                                        $item->product_image
                                    )
                                        <img
                                            src="{{ asset(
                                                'images/products/'
                                                .$item->product_image
                                            ) }}"
                                            width="55"
                                            class="mr-2"
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
                                    ${{ number_format(
                                        $item->line_total,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        class="badge badge-info"
                                    >
                                        {{ ucfirst(
                                            $item
                                                ->fulfillment_status
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    @if(
                                        count(
                                            $nextStatuses[
                                                $item
                                                    ->fulfillment_status
                                            ] ?? []
                                        )
                                    )
                                        <form
                                            action="{{ route(
                                                'seller.orders.items.status',
                                                $item
                                            ) }}"
                                            method="POST"
                                            class="d-flex"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <select
                                                name="status"
                                                class="form-control form-control-sm mr-2"
                                                required
                                            >
                                                <option value="">
                                                    Change status
                                                </option>

                                                @foreach(
                                                    $nextStatuses[
                                                        $item
                                                            ->fulfillment_status
                                                    ]
                                                    as $status
                                                )
                                                    <option
                                                        value="{{ $status }}"
                                                    >
                                                        {{ ucfirst(
                                                            $status
                                                        ) }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Save
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">
                                            Final
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right">
                <strong>
                    Your total:
                    ${{ number_format(
                        $sellerTotal,
                        2
                    ) }}
                </strong>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-30">
        <div class="card-box p-4 mb-3">
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

            <p class="mb-1">
                {{ $order->shipping_address }},
                {{ $order->shipping_city }}
            </p>

            @if($order->notes)
                <hr>

                <p>
                    <strong>Notes:</strong>
                    {{ $order->notes }}
                </p>
            @endif
        </div>

        <div class="card-box p-4">
            <h4 class="mb-3">
                Order information
            </h4>

            <p class="mb-1">
                <strong>Overall status:</strong>
                {{ ucfirst($order->status) }}
            </p>

            <p class="mb-1">
                <strong>Payment:</strong>
                Cash on Delivery
            </p>

            <p class="mb-0">
                <strong>Payment status:</strong>
                {{ ucfirst(
                    $order->payment_status
                ) }}
            </p>
        </div>
    </div>
</div>
@endsection