@extends('layouts.app') <!-- Assuming you have a base layout -->

@section('content')

<!-- Header Start -->
<div class="container-fluid hero-header bg-light py-5 mb-5">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 mb-3 animated slideInDown">Code the Future. Disrupt the Norm.</h1>
                <p class="animated slideInDown">
                    Onchain Software & Research is redefining what's possible with blockchain, AI, Robotics, and Emerging Technology.
                    We don’t just adapt to innovation—we create it. Break boundaries with us.
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary py-3 px-4 animated slideInDown">
                    Subscribe
                </a>
            </div>
            <div class="col-lg-6 animated fadeIn">
                <img class="img-fluid animated pulse infinite" style="animation-duration: 3s;" src="{{ asset('myapps/img/hero-1.png') }}" alt="" />
            </div>
        </div>
    </div>
</div>
<!-- Header End -->

<!-- About Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <img class="img-fluid" src="{{ asset('myapps/img/about.png') }}" alt="" />
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="h-100">
                    <h1 class="display-6">About Us</h1>
                    <p class="text-primary fs-5 mb-4">Innovating the Future, One Block at a Time</p>
                    <p>
                        In a world where innovation moves at lightning speed, staying ahead requires more than just technology—it demands mastery. That’s where we come in. By combining blockchain, AI [...]
                    </p>
                    <a href="{{ route('about') }}" class="btn btn-primary py-3 px-4">Read More</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<!-- Service Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 500px;">
            <h1 class="display-6">Services</h1>
            <p class="text-primary fs-5 mb-5">Solution</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="service-item bg-light p-5">
                    <img class="img-fluid mb-4" src="{{ asset('myapps/img/icon-7.png') }}" alt="" />
                    <h5 class="mb-3">Government-Based Services</h5>
                    <p>Empowering governments with blockchain solutions to optimize public services and build trust.</p>
                    <a href="#">Read More <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="service-item bg-light p-5">
                    <img class="img-fluid mb-4" src="{{ asset('myapps/img/icon-3.png') }}" alt="" />
                    <h5 class="mb-3">Enterprise Blockchain Solutions</h5>
                    <p>Increase ROI with enterprise-grade blockchain solutions powered by AI, Robotics, and IoT technology.</p>
                    <a href="#">Read More <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="service-item bg-light p-5">
                    <img class="img-fluid mb-4" src="{{ asset('myapps/img/icon-9.png') }}" alt="" />
                    <h5 class="mb-3">Decentralized Finance (DeFi)</h5>
                    <p>Transforming finance with blockchain-powered solutions that redefine trust and accessibility.</p>
                    <a href="#">Read More <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <!-- Add remaining services here -->
        </div>
    </div>
</div>
<!-- Service End -->

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
                    <img class="img-fluid flex-shrink-0" src="{{ asset('myapps/img/icon-7.png') }}" alt="" />
                    <div class="ps-4">
                        <h5 class="mb-3">Innovation-Driven Approach</h5>
                        <span>We continuously explore and integrate emerging technologies to stay ahead of the curve.</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="d-flex align-items-start">
                    <img class="img-fluid flex-shrink-0" src="{{ asset('myapps/img/icon-6.png') }}" alt="" />
                    <div class="ps-4">
                        <h5 class="mb-3">User-Centric Design</h5>
                        <span>We prioritize seamless, intuitive user experiences, ensuring that our solutions are easy to implement and use.</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="d-flex align-items-start">
                    <img class="img-fluid flex-shrink-0" src="{{ asset('myapps/img/icon-5.png') }}" alt="" />
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