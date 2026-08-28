@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="breadscrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="breadscrumb-contain">
            <h2>My Profile</h2>

            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home-page') }}">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>

                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<section class="user-dashboard-section section-b-space">
    <div class="container-fluid-lg">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="dashboard-left-sidebar">
                    <div class="profile-box">
                        <div class="cover-image"></div>

                        <div class="profile-contain">
                            <div class="profile-image">
                                <img
                                    src="{{ $customer->avatar_url }}"
                                    class="img-fluid blur-up lazyload"
                                    alt="{{ $customer->name }}"
                                >
                            </div>

                            <div class="profile-name">
                                <h3>{{ $customer->name }}</h3>
                                <h6 class="text-content">
                                    {{ $customer->email }}
                                </h6>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('customer.logout') }}"
                          method="POST"
                          class="mt-3">

                        @csrf

                        <button type="submit"
                                class="btn btn-outline-danger w-100">
                            <i class="fa fa-sign-out me-2"></i>
                            Sign out
                        </button>
                    </form>
                    <a
                        href="{{ route('customer.orders.index') }}"
                        class="btn btn-outline-primary w-100 mt-2"
                    >
                        <i class="fa fa-list me-2"></i>
                        My orders
                    </a>
                    <a
                            href="{{ route('wishlist.index') }}"
                            class="btn btn-outline-danger w-100 mt-2"
                        >
                            <i class="fa fa-heart me-2"></i>
                            My wishlist
                    </a>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="dashboard-right-sidebar">
                    <div class="dashboard-bg-box">
                        <div class="dashboard-title mb-4">
                            <h3>Profile information</h3>
                        </div>

                        <form
                            action="{{ route('customer.profile.update') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="row g-3"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Full name
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $customer->name) }}"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email address
                                </label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $customer->email) }}"
                                    required
                                >

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Phone number
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="text"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $customer->phone) }}"
                                >

                                @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="avatar" class="form-label">
                                    Profile image
                                </label>

                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    class="form-control @error('avatar') is-invalid @enderror"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                @error('avatar')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit"
                                        class="btn btn-animation">
                                    Save profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="dashboard-bg-box mt-4">
                        <div class="dashboard-title mb-4">
                            <h3>Change password</h3>
                        </div>

                        <form action="{{ route('customer.password.update') }}"
                              method="POST"
                              class="row g-3">

                            @csrf
                            @method('PATCH')

                            <div class="col-12">
                                <label for="current_password"
                                       class="form-label">
                                    Current password
                                </label>

                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password"
                                    required
                                >

                                @error('current_password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="new_password"
                                       class="form-label">
                                    New password
                                </label>

                                <input
                                    id="new_password"
                                    name="password"
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="new-password"
                                    required
                                >

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation"
                                       class="form-label">
                                    Confirm new password
                                </label>

                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <button type="submit"
                                        class="btn btn-animation">
                                    Change password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection