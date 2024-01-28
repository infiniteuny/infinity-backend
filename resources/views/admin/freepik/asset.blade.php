@extends('admin.layouts.app')

@section('title', 'Freepik Asset')

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Admin Panel</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"> <i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Freepik Asset</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/photoswipe.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/sweetalert2.css') }}">
@endsection

@section('js')
    <script src="{{ asset('admin-panel/assets/js/photoswipe/photoswipe.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/photoswipe/photoswipe-ui-default.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/photoswipe/photoswipe.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/sweet-alert/sweetalert.min.js') }}"></script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>INFINITE Freepik Asset </h5>
                </div>
                <div class="my-gallery card-body row gallery-with-description" itemscope="">
                    @foreach ($data['freepik'] as $item)
                        <figure class="col-xl-3 col-sm-6" itemprop="associatedMedia" itemscope=""><a
                                href="{{ $item['thumbnail'] }}" itemprop="contentUrl" data-size="1600x950"><img
                                    src="{{ $item['thumbnail_small'] }}" itemprop="thumbnail"
                                    alt="{{ $item['file_name'] }}">
                                <div class="caption">
                                    <h4>{{ $item['file_name'] }}</h4>
                                    <ul style="color: #a3a3a3">
                                        <li>File Size : {{ $item['file_size'] . ' Mb' }}</li>
                                        <li>Last Update :
                                            {{ Carbon\Carbon::parse($item['updated_at'])->format('d M Y') }}</li>
                                    </ul>
                                </div>
                            </a>
                            <figcaption itemprop="caption description">
                                <h4>{{ $item['file_name'] }}</h4>
                                <ul style="color: #a3a3a3">

                                    <li>
                                        <form
                                            action="{{ url('admin/freepik') }}/{{ Crypt::encryptString($item['id']) }}/download"
                                            method="POST">
                                            @csrf
                                            Download <button type="submit" class="btn btn-success btn-xs"><i
                                                    class="fa fa-download"></i> </button>
                                        </form>
                                    </li>
                                    <li>File Size : {{ $item['file_size'] . ' Mb' }}</li>
                                    <li>Last Update : {{ Carbon\Carbon::parse($item['updated_at'])->format('d M Y') }}
                                    </li>
                                </ul>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
                    <!--Background of PhotoSwipe. It's a separate element, as animating opacity is faster than rgba(). -->
                    <div class="pswp__bg"></div>
                    <!-- Slides wrapper with overflow:hidden.-->
                    <div class="pswp__scroll-wrap">
                        <!-- Container that holds slides. PhotoSwipe keeps only 3 slides in DOM to save memory.-->
                        <!-- don't modify these 3 pswp__item elements, data is added later on.-->
                        <div class="pswp__container">
                            <div class="pswp__item"></div>
                            <div class="pswp__item"></div>
                            <div class="pswp__item"></div>
                        </div>
                        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed.-->
                        <div class="pswp__ui pswp__ui--hidden">
                            <div class="pswp__top-bar">
                                <!-- Controls are self-explanatory. Order can be changed.-->
                                <div class="pswp__counter"></div>
                                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                                {{-- <button class="pswp__button pswp__button--share" title="Share"></button> --}}
                                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                                <!-- Preloader demo https://codepen.io/dimsemenov/pen/yyBWoR-->
                                <!-- element will get class pswp__preloader--active when preloader is running-->
                                <div class="pswp__preloader">
                                    <div class="pswp__preloader__icn">
                                        <div class="pswp__preloader__cut">
                                            <div class="pswp__preloader__donut"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                                <div class="pswp__share-tooltip"></div>
                            </div>
                            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
                            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
                            <div class="pswp__caption">
                                <div class="pswp__caption__center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="pull-right">
                    {{ $data['freepik']->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
