@extends('layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="{{ asset('assets/images/download-bgshape.png') }}" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>INFIEVENT</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content">

        <!-- Blog Area -->
        <div class="tm-blog-area tm-section tm-padding-section bg-grey">
            <div class="container">
                <div class="row mt-50-reverse blog-masonry-active">

                    @foreach ($events as $event)
                        <!-- Single Blog -->
                        <div class="col-lg-4 col-md-6 col-12 mt-50 blog-masonry-item">
                            <div class="tm-blog">
                                <div class="tm-blog-topside">
                                    <div class="tm-blog-image">
                                        <img src="{{ $event->media ?: 'assets/images/blog-image-1.jpg' }}"
                                            alt="appmyil blog image">
                                    </div>
                                    <span
                                        class="tm-blog-date">{{ Carbon\Carbon::parse($event->published_at)->format('F d, Y') }}</span>
                                </div>
                                <div class="tm-blog-content">
                                    <h4><a
                                            href="{{ route('event.detail', $event->id) }}">{{ strlen($event->title) > 40 ? substr($event->title, 0, 40) . '...' : $event->title }}</a>
                                    </h4>
                                    <p>{!! strlen(str_replace('&nbsp;', '', strip_tags($event->content))) > 150
                                        ? substr(str_replace('&nbsp;', '', strip_tags($event->content)), 0, 150) . '...'
                                        : str_replace('&nbsp;', '', strip_tags($event->content)) !!}</p>
                                    <a href="{{ route('event.detail', $event->id) }}"
                                        class="tm-button tm-button-sm tm-button-dark"><span>Read
                                            more</span></a>
                                </div>
                            </div>
                        </div>
                        <!--// Single Blog -->
                    @endforeach

                </div>
                {{-- <div class="tm-pagination text-center mt-50">
                    <ul>
                        <li><a href="blog-without-sidebar.html"><i class="zmdi zmdi-chevron-left"></i></a></li>
                        <li class="is-active"><a href="blog-without-sidebar.html">1</a></li>
                        <li><a href="blog-without-sidebar.html">2</a></li>
                        <li><a href="blog-without-sidebar.html">3</a></li>
                        <li><a href="blog-without-sidebar.html"><i class="zmdi zmdi-chevron-right"></i></a></li>
                    </ul>
                </div> --}}
            </div>
        </div>
        <!--// Blog Area -->

    </main>
    <!--// Page Content -->
@endsection
