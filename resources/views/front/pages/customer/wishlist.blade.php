@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="title">
            <h2>My Wishlist</h2>
        </div>

        <div class="row g-4">
            @forelse(
                $wishlists as $wishlist
            )
                @if($wishlist->product)
                    <div
                        class="col-xl-3 col-md-6"
                    >
                        <div class="card h-100">
                            <img
                                src="{{ asset(
                                    'images/products/'
                                    .$wishlist->product
                                        ->product_image
                                ) }}"
                                class="card-img-top"
                                alt="{{ $wishlist
                                    ->product->name }}"
                            >

                            <div class="card-body">
                                <h5>
                                    {{ $wishlist
                                        ->product->name }}
                                </h5>

                                <p>
                                    ${{ number_format(
                                        $wishlist->product
                                            ->price,
                                        2
                                    ) }}
                                </p>

                                <a
                                    href="{{ route(
                                        'products.show',
                                        $wishlist->product
                                    ) }}"
                                    class="btn btn-primary w-100"
                                >
                                    View product
                                </a>

                                <form
                                    action="{{ route(
                                        'wishlist.destroy',
                                        $wishlist->product
                                    ) }}"
                                    method="POST"
                                    class="mt-2"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-outline-danger w-100"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        Your wishlist is empty.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $wishlists->links() }}
        </div>
    </div>
</section>
@endsection