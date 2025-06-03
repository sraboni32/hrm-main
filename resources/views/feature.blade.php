@extends('layouts.app')

@section('content')
    <!-- Header Start -->
    <div class="container-fluid hero-header bg-light py-5 mb-5">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 mb-3 animated slideInDown">Features</h1>
                    <nav aria-label="breadcrumb animated slideInDown">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Features</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 animated fadeIn">
                    <img class="img-fluid animated pulse infinite" style="animation-duration: 3s" src="/myapps/img/hero-2.png" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Features Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 500px;">
                <h1 class="display-6">Why Us!</h1>
                <p class="text-primary fs-5 mb-5">Driven by Innovation, Focused on Security</p>
            </div>
            <div class="row g-5">
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="d-flex align-items-start">
                        <img class="img-fluid flex-shrink-0" src="/myapps/img/icon-7.png" alt="">
                        <div class="ps-4">
                            <h5 class="mb-3">Innovation-Driven Approach</h5>
                            <span>We continuously explore and integrate emerging technologies to stay ahead of the curve.</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="d-flex align-items-start">
                        <img class="img-fluid flex-shrink-0" src="/myapps/img/icon-6.png" alt="">
                        <div class="ps-4">
                            <h5 class="mb-3">User-Centric Design</h5>
                            <span>We prioritize seamless, intuitive user experiences, ensuring that our solutions are easy to implement and use.</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="d-flex align-items-start">
                        <img class="img-fluid flex-shrink-0" src="/myapps/img/icon-5.png" alt="">
                        <div class="ps-4">
                            <h5 class="mb-3">Commitment to Security</h5>
                            <span>Our systems are designed with robust security frameworks to safeguard your data and maintain trust.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->
@endsection