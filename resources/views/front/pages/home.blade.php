@extends('front.layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page title')
@section('content')

<!-- Home Section Start -->
<section class="home-section pt-2">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-xl-8 ratio_65">
                <div class="home-contain h-100 ">
                    <div class="h-100">
                        <img src="/front/images/home-banner-1.png" class="bg-img blur-up lazyload" alt>
                    </div>
                    <div class="home-detail p-center-left w-75">
                        <div>
                            <h1 class="text-uppercase">Stay home &
                                delivered your <span class="daily">Daily
                                    Needs</span></h1>
                            <p class="w-75 d-none d-sm-block">Justo
                                placerat habitant vitae mollis rhoncus
                                ut
                                bibendum vivamus penatibus pretium dis
                                duis dictumst elementum cum felis.</p>
                            <button onclick="location.href = 'javascript:void(0)';"
                                class="btn btn-animation mt-xxl-4 mt-2 home-button mend-auto">Shop
                                Now <i class="fa fa-arrow-right icon"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 ratio_65">
                <div class="row g-4">
                    <div class="col-xl-12 col-md-6">
                        <div class="home-contain">
                            <img src="/front/images/home-banner-2.png" class="bg-img blur-up lazyload" alt>
                            <div class="home-detail p-center-left home-p-sm w-75">
                                <div>
                                    <h2 class="mt-0 banner-label-color">BEST
                                        <span class="discount text-title">On</span>
                                    </h2>
                                    <h3 class="theme-color">Electronics
                                        Equipment</h3>
                                    <p class="w-75">Feugiat augue porta
                                        netus cubilia litora pulvinar
                                        habitasse</p>
                                    <a href="javascript:void(0)" class="shop-button">Shop Now <i
                                            class="fa fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-md-6">
                        <div class="home-contain">
                            <img src="/front/images/home-banner-3.png" class="bg-img blur-up lazyload" alt>
                            <div class="home-detail p-center-left home-p-sm w-75">
                                <div>
                                    <h3 class="mt-0 theme-color fw-bold">Clothing
                                        & Accessories</h3>
                                    <h4 class="banner-label-color">Gravida
                                        congue</h4>
                                    <p class="organic">Hac fermentum
                                        phasellus neque sed faucibus</p>
                                    <a href="javascript:void(0)" class="shop-button">Shop Now <i
                                            class="fa fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Home Section End -->

 <!-- Product Section Start -->

<!-- Product Section End -->



<section
    class="product-section section-b-space"
    id="products"
>
    <div class="container-fluid-lg">
        <div class="title mt-5">
            <h2>Latest products</h2>
        </div>

        <div class="row g-sm-4 g-3">
            @forelse($products as $product)
                <div class="col-xxl-3 col-lg-4 col-md-6">
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
                                        .$product->product_image
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
                                <h5 class="name">
                                    {{ $product->name }}
                                </h5>
                            </a>

                            <h5 class="sold text-content">
                                <span class="theme-color price">
                                    ${{ number_format(
                                        $product->price,
                                        2
                                    ) }}
                                </span>

                                @if($product->compare_price)
                                    <del>
                                        ${{ number_format(
                                            $product->compare_price,
                                            2
                                        ) }}
                                    </del>
                                @endif
                            </h5>

                            <div class="my-2">
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
                                        left
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        In stock
                                    </span>
                                @endif
                            </div>

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
                                    type="submit"
                                    class="btn btn-md bg-dark cart-button text-white w-100 btn-bg-color"
                                    @disabled(
                                        ! $product->isInStock()
                                    )
                                >
                                    <i
                                        class="icon-copy bi bi-cart-plus-fill"
                                    ></i>

                                    {{ $product->isInStock()
                                        ? 'Add to cart'
                                        : 'Out of stock' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        No products are available yet.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</section>




<section class="section-b-space">
    <div class="container-fluid-lg">
        <div
            class="card shadow-sm mx-auto"
            style="max-width: 760px;"
        >
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">
                    AI Shopping Assistant
                </h4>

                <small>
                    Tell me what you are looking for.
                </small>
            </div>

            <div class="card-body">
                <div
                    id="assistantMessages"
                    class="border rounded p-3 mb-3"
                    style="
                        min-height: 180px;
                        max-height: 360px;
                        overflow-y: auto;
                    "
                >
                    <div class="mb-2">
                        <strong>Assistant:</strong>
                        Hi! What would you like to buy today?
                    </div>
                </div>

                <form id="assistantForm">
                    @csrf

                    <div class="input-group">
                        <input
                            type="text"
                            id="assistantInput"
                            class="form-control"
                            maxlength="500"
                            placeholder="Example: I need a laptop under $800"
                            required
                        >

                        <button
                            type="submit"
                            id="assistantButton"
                            class="btn btn-dark"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('assistantForm');
    const input = document.getElementById('assistantInput');
    const button = document.getElementById('assistantButton');
    const messages = document.getElementById(
        'assistantMessages'
    );

    function addMessage(sender, text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';

        const name = document.createElement('strong');
        name.textContent = sender + ': ';

        const content = document.createElement('span');
        content.textContent = text;

        wrapper.appendChild(name);
        wrapper.appendChild(content);
        messages.appendChild(wrapper);

        messages.scrollTop = messages.scrollHeight;
    }

    function addProducts(products) {
        if (!Array.isArray(products)) {
            return;
        }

        products.forEach(function (product) {
            const card = document.createElement('div');
            card.className =
                'border rounded p-2 mb-2 bg-light';

            const link = document.createElement('a');
            link.href = product.url;
            link.textContent =
                product.name + ' — $' + product.price;
            link.className = 'fw-bold';

            const stock = document.createElement('div');
            stock.className = product.in_stock
                ? 'text-success'
                : 'text-danger';
            stock.textContent = product.in_stock
                ? 'In stock'
                : 'Out of stock';

            card.appendChild(link);
            card.appendChild(stock);
            messages.appendChild(card);
        });

        messages.scrollTop = messages.scrollHeight;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const message = input.value.trim();

        if (!message) {
            return;
        }

        addMessage('You', message);

        input.value = '';
        input.disabled = true;
        button.disabled = true;
        button.textContent = 'Thinking...';

        try {
            const response = await fetch(
                @json(route('shopping-assistant.chat')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            @json(csrf_token())
                    },
                    body: JSON.stringify({
                        message: message
                    })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message
                    || 'Unable to contact the assistant.'
                );
            }

            addMessage('Assistant', data.reply);
            addProducts(data.products);
        } catch (error) {
            addMessage(
                'Assistant',
                error.message
                || 'Something went wrong. Please try again.'
            );
        } finally {
            input.disabled = false;
            button.disabled = false;
            button.textContent = 'Send';
            input.focus();
        }
    });
});
</script>

@endsection