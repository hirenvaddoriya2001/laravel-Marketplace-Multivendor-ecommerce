@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section
    class="product-section section-b-space section-t-space"
>
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="product-left-box">
                    <img
                        src="{{ asset(
                            'images/products/'
                            .$product->product_image
                        ) }}"
                        class="img-fluid rounded"
                        alt="{{ $product->name }}"
                    >
                </div>
            </div>

            <div class="col-lg-6">
                <div class="right-box-contain">
                    <h2 class="name">
                        {{ $product->name }}
                    </h2>

                    <div class="price-rating mb-3">
                        <h3 class="theme-color price">
                            ${{ number_format(
                                $product->price,
                                2
                            ) }}

                            @if($product->compare_price)
                                <del class="text-content ms-2">
                                    ${{ number_format(
                                        $product->compare_price,
                                        2
                                    ) }}
                                </del>
                            @endif
                        </h3>
                    </div>
{{-- product rating --}}
                    <div class="mb-3">
                        <strong>
                            {{ number_format(
                                $product->reviews_avg_rating
                                    ?? 0,
                                1
                            ) }}
                            / 5
                        </strong>

                        <span class="text-muted">
                            ({{ $product->reviews_count }}
                            reviews)
                        </span>
                    </div>
                    <div class="mb-3">
                        @if(! $product->manage_stock)
                            <span class="badge bg-success">
                                Available
                            </span>
                        @elseif($product->stock_quantity < 1)
                            <span class="badge bg-danger">
                                Out of stock
                            </span>
                        @elseif($product->isLowStock())
                            <span
                                class="badge bg-warning text-dark"
                            >
                                Only
                                {{ $product->stock_quantity }}
                                remaining
                            </span>
                        @else
                            <span class="badge bg-success">
                                In stock
                            </span>
                        @endif

                        @if($product->sku)
                            <span class="text-muted ms-2">
                                SKU: {{ $product->sku }}
                            </span>
                        @endif
                    </div>

                    <div class="product-description mb-4">
                        {!! nl2br(e($product->summary)) !!}
                    </div>

                    <form
                        action="{{ route(
                            'cart.store',
                            $product
                        ) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="row g-2 align-items-end">
                            <div class="col-sm-3">
                                <label
                                    for="quantity"
                                    class="form-label"
                                >
                                    Quantity
                                </label>

                                <input
                                    id="quantity"
                                    name="quantity"
                                    type="number"
                                    min="1"
                                    max="{{ $product->manage_stock
                                        ? max(
                                            1,
                                            $product->stock_quantity
                                        )
                                        : 99 }}"
                                    value="1"
                                    class="form-control"
                                    @disabled(
                                        ! $product->isInStock()
                                    )
                                >
                            </div>

                            <div class="col-sm-9">
                                <button
                                    type="submit"
                                    class="btn btn-animation px-5"
                                    @disabled(
                                        ! $product->isInStock()
                                    )
                                >
                                    <i
                                        class="fa fa-shopping-cart me-2"
                                    ></i>

                                    {{ $product->isInStock()
                                        ? 'Add to cart'
                                        : 'Out of stock' }}
                                </button>
                            </div>
                            {{-- whishlist product --}}

                                <div class="mt-3">
                                    @auth
                                        @if($isWishlisted)
                                            <form
                                                action="{{ route(
                                                    'wishlist.destroy',
                                                    $product
                                                ) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="btn btn-outline-danger"
                                                >
                                                    Remove from wishlist
                                                </button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route(
                                                    'wishlist.store',
                                                    $product
                                                ) }}"
                                                method="POST"
                                            >
                                                @csrf

                                                <button
                                                    class="btn btn-outline-danger"
                                                >
                                                    Add to wishlist
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a
                                            href="{{ route(
                                                'customer.login'
                                            ) }}"
                                            class="btn btn-outline-danger"
                                        >
                                            Sign in to add to wishlist
                                        </a>
                                    @endauth
                                </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Customer review --}}

<section class="section-b-space">
    <div class="container-fluid-lg">
        <h3 class="mb-4">
            Customer reviews
        </h3>

        @auth
            @if($canReview)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>
                            {{ $customerReview
                                ? 'Update your review'
                                : 'Write a review' }}
                        </h5>

                        <form
                            action="{{ route(
                                'reviews.store',
                                $product
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <div class="mb-3">
                                <label
                                    for="rating"
                                    class="form-label"
                                >
                                    Rating
                                </label>

                                <select
                                    id="rating"
                                    name="rating"
                                    class="form-control"
                                    required
                                >
                                    @foreach(
                                        range(5, 1)
                                        as $rating
                                    )
                                        <option
                                            value="{{ $rating }}"
                                            @selected(
                                                old(
                                                    'rating',
                                                    $customerReview
                                                        ?->rating
                                                )
                                                == $rating
                                            )
                                        >
                                            {{ $rating }}
                                            star{{ $rating > 1
                                                ? 's'
                                                : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label
                                    for="comment"
                                    class="form-label"
                                >
                                    Comment
                                </label>

                                <textarea
                                    id="comment"
                                    name="comment"
                                    class="form-control"
                                    rows="4"
                                    maxlength="1000"
                                    required
                                >{{ old(
                                    'comment',
                                    $customerReview
                                        ?->comment
                                ) }}</textarea>
                            </div>

                            <button
                                class="btn btn-primary"
                            >
                                Save review
                            </button>
                        </form>

                        @if($customerReview)
                            <form
                                action="{{ route(
                                    'reviews.destroy',
                                    $customerReview
                                ) }}"
                                method="POST"
                                class="mt-2"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-outline-danger"
                                >
                                    Delete review
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    Reviews are available after
                    the product is delivered.
                </div>
            @endif
        @else
            <p>
                <a href="{{ route(
                    'customer.login'
                ) }}">
                    Sign in
                </a>
                to review a purchased product.
            </p>
        @endauth

        @forelse($product->reviews as $review)
            <div class="border-bottom py-3">
                <div
                    class="d-flex justify-content-between"
                >
                    <strong>
                        {{ $review->user->name }}
                    </strong>

                    <span>
                        {{ $review->rating }} / 5
                    </span>
                </div>

                <p class="mb-1">
                    {{ $review->comment }}
                </p>

                <small class="text-muted">
                    {{ $review->created_at
                        ->format('d M Y') }}
                </small>
            </div>
        @empty
            <p class="text-muted">
                No reviews yet.
            </p>
        @endforelse
    </div>
</section>
@endsection