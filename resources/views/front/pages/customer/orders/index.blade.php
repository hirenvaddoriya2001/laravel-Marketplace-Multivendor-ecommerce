@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >
            <h2>My orders</h2>

            <a
                href="{{ route('customer.profile') }}"
                class="btn btn-outline-secondary"
            >
                My profile
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="alert alert-info">
                You have not placed any orders yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Status</th>
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
                                        ->format('d M Y, H:i') }}
                                </td>

                                <td>
                                    {{ $order->items_count }}
                                </td>

                                <td>
                                    <span
                                        class="badge bg-warning text-dark"
                                    >
                                        {{ ucfirst(
                                            $order->status
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    {{ strtoupper(
                                        $order->payment_method
                                    ) }}
                                    /
                                    {{ ucfirst(
                                        $order->payment_status
                                    ) }}
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
                                            'customer.orders.show',
                                            $order
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</section>
@endsection