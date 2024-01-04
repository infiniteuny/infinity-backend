@extends('landing.layouts.app')

@section('css')
<style>
    .tm-sectiontitle-divider::before,
    .tm-sectiontitle-divider::after {
        background-image: url({{ asset('landing/assets/images/title-shape.png')}});
    }
    .tm-team-imageinner img.hidden-image {
        display: none;
    }
    .tm-team:hover .tm-team-imageinner img.hidden-image {
        display: block;
    }
    .tm-team:hover .tm-team-imageinner img.default-image {
        display: none;
    }
</style>
@endsection

@section('title', 'Core Team')

@section('js')
<script>
    function selectYear() {
            var year = document.getElementById("year").value;
            var url = "{{ route('team', 'year=:year') }}";
            url = url.replace(':year', year);
            window.location.href = url;
        }
</script>
@endsection

@section('content')
<!-- Breadcrumb Area -->
<div class="tm-breadcrumb-area tm-padding-section bg-gradient">
    <div class="tm-breadcrumb-bgshape">
        <img src="{{ asset('landing/assets/images/download-bgshape.png') }}" alt="bg shape">
    </div>
    <div class="container">
        <div class="tm-breadcrumb text-center">
            <h2>Core Team {{ $data['commitees'][0]->cabinet }}</h2>
        </div>
    </div>
</div>
<!--// Breadcrumb Area -->

<!-- Page Content -->
<main class="page-content">

    <!-- Team Member Area -->
    <div id="tm-area-team" class="tm-team-area tm-section tm-padding-section bg-grey">
        <div class="tm-team-areabgshape">
            <img src="{{ asset('landing/assets/images/team-area-bgshape.png') }}" alt="appmyil bg shape">
        </div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="tm-sectiontitle text-center">
                        <h2>Meet The Core Team</h2>
                        <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4 mb-5">
                    <form action="#">
                        <div class="tm-team-search">
                            <select onchange="selectYear()" name="year" id="year">
                                @foreach ($data['cabinets'] as $item)
                                <option value="{{ $item->year }}" {{ $item->year == $data['commitees'][0]->cabinet ?
                                    'selected' : '' }}>
                                    {{ $item->year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                </div>
            </div>
            <div class="row">

                @foreach ($data['commitees'] as $item)
                <!-- Team Member -->
                <div class="col-lg-3 col-md-6 col-12 mb-2">
                    <div class="tm-team text-center">
                        <div class="tm-team-top">
                            <div class="tm-team-image">
                                <div class="tm-team-imageinner">
                                    @if ($item->gif !== "")
                                    {{-- If Not Hovered --}}
                                    <img class="default-image" src="{{ $item->photo }}" alt="{{ $item->name }}">
                                    {{-- If Hovered --}}
                                    <img class="hidden-image" src="{{ $item->gif }}" alt="{{ $item->name }}">
                                    @else
                                    <img src="{{ $item->photo }}" alt="{{ $item->name }}">
                                    @endif
                                </div>
                            </div>
                            <button class="tm-team-socialtrigger"><a href="https://instagram.com/{{ $item->instagram ?: 'infinite.uny' }}"
                                target="_blank"><i class="zmdi zmdi-instagram"></i></a></button>
                            {{-- <ul class="tm-team-socialicons">
                                <li><a href="https://instagram.com/{{ $item->instagram ?: 'infinite.uny' }}"
                                        target="_blank"><i class="zmdi zmdi-instagram"></i></a></li>
                            </ul> --}}
                        </div>
                        <div class="tm-team-content">
                            <h6>{{ $item->division }}</h6>
                            <h4>{{ $item->name }}</h4>
                            <h6>{{ $item->study_program . ' ' . $item->year }}</h6>
                        </div>
                    </div>
                </div>
                <!--// Team Member -->
                @endforeach

            </div>
        </div>
    </div>
    <!--// Team Member Area -->

</main>

<!--// Page Content -->
@endsection
