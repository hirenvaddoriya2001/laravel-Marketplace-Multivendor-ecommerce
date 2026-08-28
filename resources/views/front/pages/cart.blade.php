@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section
    class="cart-section section-b-space section-t-space"
>
    <div class="container-fluid-lg">
        <div class="title">
            <h2>Shopping cart</h2>
        </div>

        @if($items->isEmpty())
            <div class="alert alert-info">
                Your cart is empty.

                <a
                    href="{{ route('home-page') }}#products"
                    class="alert-link"
                >
                    Browse products
                </a>.
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <a
                                        href="{{ route(
                                            'products.show',
                                            $item
                                        ) }}"
                                    >
                                        <img
                                            src="{{ asset(
                                                'images/products/'
                                                .$item->product_image
                                            ) }}"
                                            width="72"
                                            class="rounded me-2"
                                            alt="{{ $item->name }}"
                                        >

                                        {{ $item->name }}
                                    </a>

                                    @if($item->manage_stock)
                                        <small
                                            class="d-block text-muted"
                                        >
                                            Available:
                                            {{ $item->stock_quantity }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    ${{ number_format(
                                        $item->price,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <form
                                        action="{{ route(
                                            'cart.update',
                                            $item
                                        ) }}"
                                        method="POST"
                                        class="d-flex gap-2"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            name="quantity"
                                            class="form-control"
                                            style="width: 85px"
                                            type="number"
                                            min="1"
                                            max="{{ $item->manage_stock
                                                ? $item->stock_quantity
                                                : 99 }}"
                                            value="{{ $item->cart_quantity }}"
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            type="submit"
                                        >
                                            Update
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    ${{ number_format(
                                        $item->line_total,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <form
                                        action="{{ route(
                                            'cart.destroy',
                                            $item
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                            type="submit"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <div class="text-end">
                    <h4>
                        Subtotal:
                        ${{ number_format($subtotal, 2) }}
                    </h4>

                    <a
                        href="{{ route('checkout.create') }}"
                        class="btn btn-animation mt-3"
                    >
                        Proceed to checkout
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection