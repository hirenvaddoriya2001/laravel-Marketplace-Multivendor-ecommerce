@extends('back.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-12">
            <div class="title">
                <h4>Order management</h4>
            </div>

            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route(
                            'admin.home'
                        ) }}">
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

<div class="row mb-30">
    <div class="col-md-3 mb-3">
        <div class="card-box p-3">
            <small>Total orders</small>
            <h3>{{ $stats['orders'] }}</h3>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card-box p-3">
            <small>Pending</small>
            <h3>{{ $stats['pending'] }}</h3>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card-box p-3">
            <small>Gross sales</small>

            <h3>
                ${{ number_format(
                    $stats['gross'],
                    2
                ) }}
            </h3>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card-box p-3">
            <small>COD collected</small>

            <h3>
                ${{ number_format(
                    $stats['collected'],
                    2
                ) }}
            </h3>
        </div>
    </div>
</div>

<div class="card-box mb-30 p-3">
    <form
        method="GET"
        action="{{ route(
            'admin.orders.index'
        ) }}"
        class="row"
    >
        <div class="col-md-4 mb-2">
            <input
                name="q"
                class="form-control"
                value="{{ request('q') }}"
                placeholder="Order number, customer, or email"
            >
        </div>

        <div class="col-md-2 mb-2">
            <select
                name="status"
                class="form-control"
            >
                <option value="">
                    All order statuses
                </option>

                @foreach([
                    'pending',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                ] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(
                            request('status')
                            === $status
                        )
                    >
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <select
                name="payment_status"
                class="form-control"
            >
                <option value="">
                    All payments
                </option>

                @foreach([
                    'unpaid',
                    'paid',
                    'refunded',
                ] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(
                            request(
                                'payment_status'
                            ) === $status
                        )
                    >
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <select
                name="fulfillment_status"
                class="form-control"
            >
                <option value="">
                    All item statuses
                </option>

                @foreach([
                    'pending',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                ] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(
                            request(
                                'fulfillment_status'
                            ) === $status
                        )
                    >
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <button class="btn btn-primary">
                Filter
            </button>

            <a
                href="{{ route(
                    'admin.orders.index'
                ) }}"
                class="btn btn-outline-secondary"
            >
                Clear
            </a>
        </div>
    </form>
</div>

<div class="card-box mb-30">
    <div class="pd-20">
        <h4 class="text-blue h4">
            Marketplace orders
        </h4>
    </div>

    <div class="pb-20">
        @if($orders->isEmpty())
            <div class="px-4 pb-4 text-muted">
                No orders match the filters.
            </div>
        @else
            <div class="table-responsive">
                <table class="table hover nowrap">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Order status</th>
                            <th>Payment</th>
                            <th>Total</th>
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

                                    <small
                                        class="d-block text-muted"
                                    >
                                        {{ $order
                                            ->shipping_email }}
                                    </small>
                                </td>

                                <td>
                                    {{ $order->items_count }}
                                </td>

                                <td>
                                    <span
                                        class="badge badge-info"
                                    >
                                        {{ ucfirst(
                                            $order->status
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge badge-secondary"
                                    >
                                        {{ ucfirst(
                                            $order
                                                ->payment_status
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    ${{ number_format(
                                        $order->total,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route(
                                            'admin.orders.show',
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