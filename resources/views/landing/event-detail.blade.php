@extends('landing.layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('landing/assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('title', $event->title)

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="{{ asset('landing/assets/images/download-bgshape.png') }}" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>Event Details</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content">

        <!-- Blog Area -->
        <div class="tm-section blogs-area bg-whtie tm-padding-section">
            <div class="container">
                <div class="tm-blog blogitem">
                    <div class="tm-blog-topside">
                        <div class="tm-blog-image">
                            <img src="{{ $event->media ?: asset('landing/assets/images/blog-image-lg-1.jpg') }}"
                                alt="infinite event image">
                        </div>
                        <span class="tm-blog-date">{{ Carbon\Carbon::parse($event->publishedAt)->format('F d, Y') }}</span>
                    </div>
                    <div class="tm-blog-content">
                        <div class="tm-blog-meta">
                            <span><a href="#"><i class="zmdi zmdi-account"></i> Admin</a></span>
                            <span><a href="#" style="text-transform: capitalize"><i class="zmdi zmdi-folder"></i>
                                    {{ $event->type }}</a></span>
                        </div>
                        <h4 class="tm-blog-title">{{ $event->title }}</h4>
                        {!! $event->content !!}
                    </div>

                    <div class="blogitem-tags">
                        <h5 class="blogitem-tags-title">Tags:</h5>
                        <ul>
                            <li><a href="#" style="text-transform: capitalize">{{ $event->type }}</a></li>
                        </ul>
                    </div>

                    <div class="blogitem-share">
                        <h5 class="blogitem-share-title">Share With: </h5>
                        <ul>
                            <li class="facebook"><a
                                    href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}"><i
                                        class="zmdi zmdi-facebook"></i>
                                    Facebook</a></li>
                            <li class="twitter"><a
                                    href="https://twitter.com/share?url=Check%20this%20cool%20article%20{{ url()->current() }}"><i
                                        class="zmdi zmdi-twitter"></i>
                                    Twitter</a></li>
                            {{-- <li class="pinterest"><a
                                    href="https://pinterest.com/pin/create/button/?url=&media=&description="><i
                                        class="zmdi zmdi-pinterest"></i> Pinterest</a></li> --}}
                            {{-- <li class="linkedin"><a
                                    href="https://www.linkedin.com/shareArticle?mini=true&url={{ url()->current() }}&title=&summary=&source="><i
                                        class="zmdi zmdi-linkedin"></i> Linkedin</a></li> --}}
                        </ul>
                    </div>

                </div>
            </div>
        </div>
        <!--// Blog Area -->

    </main>
    <!--// Page Content -->
@endsection
