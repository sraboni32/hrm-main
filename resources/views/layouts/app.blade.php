<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link href="/myapps/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500&family=Roboto:wght@500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="/myapps/lib/animate/animate.min.css" rel="stylesheet">
    <link href="/myapps/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/myapps/css/style.css" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top p-0 px-4 px-lg-5">
    <a href="/" class="navbar-brand d-flex align-items-center">
            <h2 class="m-0 text-primary"><img class="img-fluid me-2" src="/myapps/img/icon-1.png" alt=""
                    style="width: 150px;"></h2>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-4 py-lg-0">
                <a href="{{ route('home') }}" class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="nav-item nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('service') }}" class="nav-item nav-link {{ request()->routeIs('service') ? 'active' : '' }}">Service</a>
                <a href="{{ route('posts.index') }}" class="nav-item nav-link {{ request()->routeIs('posts.index') ? 'active' : '' }}">Research</a>
                <a href="{{ route('contact') }}" class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                <a href="{{ route('job_vacancies.index') }}" class="nav-item nav-link {{ request()->routeIs('job_vacancies.index') ? 'active' : '' }}">Jobs</a>
                @guest
                    <a href="{{ route('login') }}" class="nav-item nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                @endguest
                @auth
                    @php $roleId = auth()->user()->role_users_id; @endphp
                    @if($roleId == 1)
                        <a href="{{ route('dashboard') }}" class="nav-item nav-link">Admin Dashboard</a>
                    @elseif($roleId == 2)
                        <a href="{{ route('dashboard_employee') }}" class="nav-item nav-link">Employee Dashboard</a>
                    @elseif($roleId == 3)
                        <a href="{{ route('dashboard_client') }}" class="nav-item nav-link">Client Dashboard</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="nav-item nav-link btn btn-link" style="display:inline;">Logout</button>
                    </form>
                @endauth
            </div>
            <div class="h-100 d-lg-inline-flex align-items-center d-none">
                <a class="btn btn-square rounded-circle bg-light text-primary me-2" href="">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a class="btn btn-square rounded-circle bg-light text-primary me-2" href="">
                    <i class="fab fa-twitter"></i>
                </a>
                <a class="btn btn-square rounded-circle bg-light text-primary me-0" href="">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Content Start -->
    <main>
        @yield('content')
    </main>
    <!-- Content End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-light footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6">
                    <h1 class="text-primary mb-4">
                        <img class="img-fluid me-2" src="/myapps/img/icon-1.png" alt="" style="width: 150px">
                    </h1>
                    <span>Shaping the Future with Innovative Solutions.</span>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-4">Newsletter</h5>
                    <p>Stay updated with our latest news and announcements.</p>
                    
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Contact Us</h5>
                    <p><i class="fa fa-map-marker-alt me-3"></i>House # 353, 3rd Floor (North), Road # 05, DOHS Baridhara, Dhaka-1206</p>
                    <p><i class="fa fa-phone-alt me-3"></i>09643112277</p>
                    <p>
                        <i class="fa fa-envelope me-3"></i>
                        <a href="mailto:info@onchain.com.bd">info@onchain.com.bd</a><br>
                        <i class="fa fa-envelope me-3"></i>
                        <a href="mailto:onchainsoftwareresearch@gmail.com">onchainsoftwareresearch@gmail.com</a>
                    </p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Our Services</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('service') }}#b2g" class="btn btn-link">B2G Services</a></li>
                        <li><a href="{{ route('service') }}#enterprise" class="btn btn-link">Enterprise Blockchain</a></li>
                        <li><a href="{{ route('service') }}#defi" class="btn btn-link">Decentralized Finance</a></li>
                        <li><a href="{{ route('service') }}#health" class="btn btn-link">HealthTech Blockchain</a></li>
                        <li><a href="{{ route('service') }}#multiverse" class="btn btn-link">Multiverse & Robotics</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Quick Links</h5>
                    <a href="{{ route('about') }}" class="btn btn-link">About Us</a>
                    <a href="{{ route('contact') }}" class="btn btn-link">Contact Us</a>
                    <a href="{{ route('service') }}" class="btn btn-link">Our Services</a>
                    <a href="{{ route('faq') }}" class="btn btn-link">Terms & Condition</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Follow Us</h5>
                    <div class="d-flex">
                        <a class="btn btn-square rounded-circle me-1" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-square rounded-circle me-1" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square rounded-circle me-1" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square rounded-circle me-1" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; {{ date('Y') }} OnChain, All Right Reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/myapps/lib/wow/wow.min.js"></script>
    <script src="/myapps/lib/easing/easing.min.js"></script>
    <script src="/myapps/lib/waypoints/waypoints.min.js"></script>
    <script src="/myapps/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/myapps/lib/counterup/counterup.min.js"></script>

    <!-- Template Javascript -->
    <script src="/myapps/js/main.js"></script>

    <script>
        // Initialize template features
        $(document).ready(function() {
            // Initialize counter
            $('[data-toggle="counter-up"]').counterUp({
                delay: 10,
                time: 2000
            });

            // Initialize roadmap carousel
            $(".roadmap-carousel").owlCarousel({
                autoplay: true,
                smartSpeed: 1000,
                margin: 25,
                loop: true,
                dots: false,
                nav: true,
                navText: [
                    '<i class="bi bi-chevron-left"></i>',
                    '<i class="bi bi-chevron-right"></i>'
                ],
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 },
                    1200: { items: 5 }
                }
            });

            // Sticky Navbar
            $(window).scroll(function () {
                if ($(this).scrollTop() > 300) {
                    $('.sticky-top').addClass('shadow-sm').css('top', '0px');
                } else {
                    $('.sticky-top').removeClass('shadow-sm').css('top', '-100px');
                }
            });

            // Back to top button
            $(window).scroll(function () {
                if ($(this).scrollTop() > 300) {
                    $('.back-to-top').fadeIn('slow');
                } else {
                    $('.back-to-top').fadeOut('slow');
                }
            });
            $('.back-to-top').click(function () {
                $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
                return false;
            });

            // Hide spinner
            setTimeout(function () {
                $("#spinner").removeClass("show");
            }, 1);

            // Initialize WOW.js
            new WOW().init();
        });
    </script>

    @stack('scripts')
</body>
</html>