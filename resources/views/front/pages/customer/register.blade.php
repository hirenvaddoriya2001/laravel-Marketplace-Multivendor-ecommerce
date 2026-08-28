@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="breadscrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="breadscrumb-contain">
            <h2>Create Customer Account</h2>

            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home-page') }}">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>

                    <li class="breadcrumb-item active">Register</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<section class="log-in-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-7 col-md-9">
                <div class="log-in-box">
                    <div class="log-in-title">
                        <h3>Welcome to LARAVECOM</h3>
                        <h4>Create your customer account</h4>
                    </div>

                    <form action="{{ route('customer.register.store') }}"
                          method="POST"
                          class="row g-4">

                        @csrf

                        <div class="col-12">
                            <label for="name" class="form-label">
                                Full name
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                required
                            >

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">
                                Email address
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label">
                                Phone number
                            </label>

                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                            >

                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">
                                Confirm password
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
                                    class="btn btn-animation w-100">
                                Create account
                            </button>
                        </div>
                    </form>

                    <div class="other-log-in mt-4">
                        <h6>Already have an account?</h6>
                    </div>

                    <a href="{{ route('customer.login') }}"
                       class="btn btn-light w-100 mt-3">
                        Sign in
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection