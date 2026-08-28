@extends('back.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<div class="page-header">
    <div class="title">
        <h4>
            Order {{ $order->order_number }}
        </h4>
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

            <li class="breadcrumb-item">
                <a href="{{ route(
                    'admin.orders.index'
                ) }}">
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
                All order items
            </h4>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product / seller</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Admin override</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(
                            $order->items as $item
                        )
                            <tr>
                                <td>
                                    {{ $item
                                        ->product_name }}

                                    <small
                                        class="d-block text-muted"
                                    >
                                        Seller:
                                        {{ $item->seller?->name
                                            ?? 'Unavailable' }}

                                        @if(
                                            $item->product_sku
                                        )
                                            | SKU:
                                            {{ $item
                                                ->product_sku }}
                                        @endif
                                    </small>
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
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.orders.items.status',
                                            $item
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="status"
                                            class="form-control form-control-sm mb-1"
                                            required
                                        >
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
                                                        $item
                                                            ->fulfillment_status
                                                        === $status
                                                    )
                                                >
                                                    {{ ucfirst(
                                                        $status
                                                    ) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input
                                            name="note"
                                            class="form-control form-control-sm mb-1"
                                            maxlength="1000"
                                            placeholder="Required reason"
                                            required
                                        >

                                        <button
                                            class="btn btn-sm btn-warning"
                                            type="submit"
                                        >
                                            Override
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-30">
        <div class="card-box p-4 mb-3">
            <h4 class="mb-3">
                Order and payment
            </h4>

            <p>
                <strong>Overall status:</strong>
                {{ ucfirst($order->status) }}
            </p>

            <p>
                <strong>Payment method:</strong>
                {{ strtoupper(
                    $order->payment_method
                ) }}
            </p>

            <p>
                <strong>Payment status:</strong>
                {{ ucfirst(
                    $order->payment_status
                ) }}
            </p>

            <form
                method="POST"
                action="{{ route(
                    'admin.orders.payment.status',
                    $order
                ) }}"
            >
                @csrf
                @method('PATCH')

                <select
                    name="payment_status"
                    class="form-control mb-2"
                    required
                >
                    @foreach([
                        'unpaid',
                        'paid',
                        'refunded',
                    ] as $status)
                        <option
                            value="{{ $status }}"
                            @selected(
                                $order->payment_status
                                === $status
                            )
                        >
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>

                <textarea
                    name="note"
                    class="form-control mb-2"
                    maxlength="1000"
                    placeholder="Required reason"
                    required
                ></textarea>

                <button
                    class="btn btn-primary btn-sm"
                >
                    Update payment
                </button>
            </form>
        </div>

        <div class="card-box p-4">
            <h4>Customer and delivery</h4>

            <p>
                <strong>
                    {{ $order->shipping_name }}
                </strong>
            </p>

            <p>{{ $order->shipping_email }}</p>
            <p>{{ $order->shipping_phone }}</p>

            <p>
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
    </div>
</div>

<div class="card-box p-4 mb-30">
    <h4 class="mb-3">
        Seller earnings summary
    </h4>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Lines</th>
                    <th>Quantity</th>
                    <th>Non-cancelled gross</th>
                </tr>
            </thead>

            <tbody>
                @foreach(
                    $sellerSummaries
                    as $summary
                )
                    <tr>
                        <td>
                            {{ $summary[
                                'seller'
                            ]?->name
                                ?? 'Unavailable seller' }}
                        </td>

                        <td>
                            {{ $summary['items'] }}
                        </td>

                        <td>
                            {{ $summary['quantity'] }}
                        </td>

                        <td>
                            ${{ number_format(
                                $summary['gross'],
                                2
                            ) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card-box p-4 mb-30">
    <h4 class="mb-3">
        Activity log
    </h4>

    @if($order->activityLogs->isEmpty())
        <p class="text-muted">
            No recorded activity yet.
        </p>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Change</th>
                        <th>Note</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $order->activityLogs
                        as $log
                    )
                        <tr>
                            <td>
                                {{ $log->created_at
                                    ->format(
                                        'd M Y, H:i:s'
                                    ) }}
                            </td>

                            <td>
                                {{ ucfirst(
                                    $log->actor_type
                                ) }}
                                #{{ $log->actor_id }}
                            </td>

                            <td>
                                {{ str_replace(
                                    '_',
                                    ' ',
                                    ucfirst(
                                        $log->action
                                    )
                                ) }}
                            </td>

                            <td>
                                {{ $log->from_value
                                    ?? '—' }}
                                →
                                {{ $log->to_value
                                    ?? '—' }}
                            </td>

                            <td>
                                {{ $log->note }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection