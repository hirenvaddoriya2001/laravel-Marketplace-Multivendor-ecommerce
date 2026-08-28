@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section
    class="checkout-section section-b-space section-t-space"
>
    <div class="container-fluid-lg">
        <div class="title">
            <h2>Checkout</h2>
        </div>

        @if($errors->has('cart'))
            <div class="alert alert-danger">
                {{ $errors->first('cart') }}
            </div>
        @endif

        <form
            action="{{ route('checkout.store') }}"
            method="POST"
        >
            @csrf

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">
                                Shipping information
                            </h4>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label
                                        for="shipping_name"
                                        class="form-label"
                                    >
                                        Full name
                                    </label>

                                    <input
                                        id="shipping_name"
                                        name="shipping_name"
                                        type="text"
                                        class="form-control @error('shipping_name') is-invalid @enderror"
                                        value="{{ old(
                                            'shipping_name',
                                            $customer->name
                                        ) }}"
                                        required
                                    >

                                    @error('shipping_name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label
                                        for="shipping_email"
                                        class="form-label"
                                    >
                                        Email
                                    </label>

                                    <input
                                        id="shipping_email"
                                        name="shipping_email"
                                        type="email"
                                        class="form-control @error('shipping_email') is-invalid @enderror"
                                        value="{{ old(
                                            'shipping_email',
                                            $customer->email
                                        ) }}"
                                        required
                                    >

                                    @error('shipping_email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label
                                        for="shipping_phone"
                                        class="form-label"
                                    >
                                        Phone
                                    </label>

                                    <input
                                        id="shipping_phone"
                                        name="shipping_phone"
                                        type="text"
                                        class="form-control @error('shipping_phone') is-invalid @enderror"
                                        value="{{ old(
                                            'shipping_phone',
                                            $customer->phone
                                        ) }}"
                                        required
                                    >

                                    @error('shipping_phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label
                                        for="shipping_city"
                                        class="form-label"
                                    >
                                        City
                                    </label>

                                    <input
                                        id="shipping_city"
                                        name="shipping_city"
                                        type="text"
                                        class="form-control @error('shipping_city') is-invalid @enderror"
                                        value="{{ old(
                                            'shipping_city'
                                        ) }}"
                                        required
                                    >

                                    @error('shipping_city')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label
                                        for="shipping_address"
                                        class="form-label"
                                    >
                                        Delivery address
                                    </label>

                                    <textarea
                                        id="shipping_address"
                                        name="shipping_address"
                                        rows="3"
                                        class="form-control @error('shipping_address') is-invalid @enderror"
                                        required
                                    >{{ old('shipping_address') }}</textarea>

                                    @error('shipping_address')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label
                                        for="notes"
                                        class="form-label"
                                    >
                                        Order notes (optional)
                                    </label>

                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="3"
                                        class="form-control @error('notes') is-invalid @enderror"
                                    >{{ old('notes') }}</textarea>

                                    @error('notes')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">
                                Order summary
                            </h4>

                            @foreach($items as $item)
                                <div
                                    class="d-flex justify-content-between border-bottom py-2"
                                >
                                    <span>
                                        {{ $item->name }}
                                        ×
                                        {{ $item->cart_quantity }}
                                    </span>

                                    <strong>
                                        ${{ number_format(
                                            $item->line_total,
                                            2
                                        ) }}
                                    </strong>
                                </div>
                            @endforeach

                            <div
                                class="d-flex justify-content-between pt-3"
                            >
                                <strong>Total</strong>

                                <strong class="theme-color">
                                    ${{ number_format(
                                        $subtotal,
                                        2
                                    ) }}
                                </strong>
                            </div>

                            <div
                                class="alert alert-info mt-3 mb-3"
                            >
                                Payment method:
                                Cash on Delivery
                            </div>

                            <button
                                type="submit"
                                class="btn btn-animation w-100"
                            >
                                Place order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection