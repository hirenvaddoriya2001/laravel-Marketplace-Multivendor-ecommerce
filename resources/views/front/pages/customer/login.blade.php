@extends('front.layout.pages-layout')

@section('pageTitle', $pageTitle)

@section('content')
<section class="breadscrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="breadscrumb-contain">
            <h2>Customer Login</h2>

            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home-page') }}">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>

                    <li class="breadcrumb-item active">Sign in</li>
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
                        <h3>Welcome back</h3>
                        <h4>Sign in to your customer account</h4>
                    </div>

                    @if(session('fail'))
                        <div class="alert alert-danger">
                            {{ session('fail') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('customer.login.store') }}"
                          method="POST"
                          class="row g-4">

                        @csrf

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
                                autofocus
                            >

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                required
                            >

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    class="form-check-input"
                                    value="1"
                                    @checked(old('remember'))
                                >

                                <label for="remember" class="form-check-label">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit"
                                    class="btn btn-animation w-100">
                                Sign in
                            </button>
                        </div>
                    </form>

                    <div class="other-log-in mt-4">
                        <h6>New customer?</h6>
                    </div>

                    <a href="{{ route('customer.register') }}"
                       class="btn btn-light w-100 mt-3">
                        Create account
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection