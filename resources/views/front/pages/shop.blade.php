@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="title">
            <h2>Shop</h2>

            @if(request('q'))
                <p>
                    Results for:
                    <strong>
                        {{ request('q') }}
                    </strong>
                </p>
            @endif
        </div>

        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">
                            Filters
                        </h4>

                        <form
                            action="{{ route(
                                'shop.index'
                            ) }}"
                            method="GET"
                        >
                            <div class="mb-3">
                                <label
                                    for="q"
                                    class="form-label"
                                >
                                    Search
                                </label>

                                <input
                                    id="q"
                                    name="q"
                                    type="search"
                                    class="form-control"
                                    value="{{ request('q') }}"
                                    placeholder="Product name or SKU"
                                >
                            </div>

                            <div class="mb-3">
                                <label
                                    for="category"
                                    class="form-label"
                                >
                                    Category
                                </label>

                                <select
                                    id="category"
                                    name="category"
                                    class="form-control"
                                >
                                    <option value="">
                                        All categories
                                    </option>

                                    @foreach(
                                        $categories
                                        as $category
                                    )
                                        <option
                                            value="{{ $category->category_slug }}"
                                            @selected(
                                                request(
                                                    'category'
                                                )
                                                === $category
                                                    ->category_slug
                                            )
                                        >
                                            {{ $category
                                                ->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label
                                        for="min_price"
                                        class="form-label"
                                    >
                                        Min price
                                    </label>

                                    <input
                                        id="min_price"
                                        name="min_price"
                                        type="number"
                                        class="form-control"
                                        min="0"
                                        step="0.01"
                                        value="{{ request(
                                            'min_price'
                                        ) }}"
                                    >
                                </div>

                                <div class="col-6">
                                    <label
                                        for="max_price"
                                        class="form-label"
                                    >
                                        Max price
                                    </label>

                                    <input
                                        id="max_price"
                                        name="max_price"
                                        type="number"
                                        class="form-control"
                                        min="0"
                                        step="0.01"
                                        value="{{ request(
                                            'max_price'
                                        ) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input
                                    id="in_stock"
                                    name="in_stock"
                                    type="checkbox"
                                    value="1"
                                    class="form-check-input"
                                    @checked(
                                        request('in_stock')
                                    )
                                >

                                <label
                                    for="in_stock"
                                    class="form-check-label"
                                >
                                    In-stock products only
                                </label>
                            </div>

                            <div class="mb-3">
                                <label
                                    for="sort"
                                    class="form-label"
                                >
                                    Sort
                                </label>

                                <select
                                    id="sort"
                                    name="sort"
                                    class="form-control"
                                >
                                    <option value="">
                                        Newest
                                    </option>

                                    <option
                                        value="price_asc"
                                        @selected(
                                            request('sort')
                                            === 'price_asc'
                                        )
                                    >
                                        Price: low to high
                                    </option>

                                    <option
                                        value="price_desc"
                                        @selected(
                                            request('sort')
                                            === 'price_desc'
                                        )
                                    >
                                        Price: high to low
                                    </option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-animation w-100"
                            >
                                Apply filters
                            </button>

                            <a
                                href="{{ route(
                                    'shop.index'
                                ) }}"
                                class="btn btn-outline-secondary w-100 mt-2"
                            >
                                Clear filters
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="row g-4">
                    @forelse(
                        $products as $product
                    )
                        <div
                            class="col-xl-4 col-md-6"
                        >
                            <div class="product-box h-100">
                                <div class="product-image">
                                    <a
                                        href="{{ route(
                                            'products.show',
                                            $product
                                        ) }}"
                                    >
                                        <img
                                            src="{{ asset(
                                                'images/products/'
                                                .$product
                                                    ->product_image
                                            ) }}"
                                            class="img-fluid"
                                            alt="{{ $product->name }}"
                                        >
                                    </a>
                                </div>

                                <div class="product-detail">
                                    <a
                                        href="{{ route(
                                            'products.show',
                                            $product
                                        ) }}"
                                    >
                                        <h5>
                                            {{ $product->name }}
                                        </h5>
                                    </a>

                                    <p class="mb-1">
                                        <strong
                                            class="theme-color"
                                        >
                                            ${{ number_format(
                                                $product->price,
                                                2
                                            ) }}
                                        </strong>
                                    </p>

                                    <p class="small text-muted">
                                        Rating:
                                        {{ number_format(
                                            $product
                                                ->reviews_avg_rating
                                                ?? 0,
                                            1
                                        ) }}
                                        / 5
                                        ({{ $product
                                            ->reviews_count }})
                                    </p>

                                    @if(
                                        ! $product
                                            ->isInStock()
                                    )
                                        <span
                                            class="badge bg-danger mb-2"
                                        >
                                            Out of stock
                                        </span>
                                    @elseif(
                                        $product
                                            ->isLowStock()
                                    )
                                        <span
                                            class="badge bg-warning text-dark mb-2"
                                        >
                                            Only
                                            {{ $product
                                                ->stock_quantity }}
                                            left
                                        </span>
                                    @endif

                                    <form
                                        action="{{ route(
                                            'cart.store',
                                            $product
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1"
                                        >

                                        <button
                                            class="btn btn-dark w-100"
                                            @disabled(
                                                ! $product
                                                    ->isInStock()
                                            )
                                        >
                                            Add to cart
                                        </button>
                                    </form>

                                    @auth
                                        <form
                                            action="{{ route(
                                                'wishlist.store',
                                                $product
                                            ) }}"
                                            method="POST"
                                            class="mt-2"
                                        >
                                            @csrf

                                            <button
                                                class="btn btn-outline-danger w-100"
                                            >
                                                Add to wishlist
                                            </button>
                                        </form>
                                    @else
                                        <a
                                            href="{{ route(
                                                'customer.login'
                                            ) }}"
                                            class="btn btn-outline-danger w-100 mt-2"
                                        >
                                            Sign in to wishlist
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                No products match your filters.
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection