@extends('auth.layouts.app')

@section('title', 'Sign in')

@section('content')

    <!-- login page start-->
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card">
                    <div>
                        <div><a class="logo" href="{{ route('landing') }}"><img class="img-fluid for-light"
                                    src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" alt="looginpage"><img
                                    class="img-fluid for-dark"
                                    src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" alt="looginpage"></a>
                        </div>
                        <div class="login-main">
                            <form class="theme-form" action="{{ url('/login') }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                <h4>Masuk dulu ya!</h4>
                                <p>Inputkan email dan password kamu untuk login</p>
                                @csrf
                                <div class="form-group">
                                    <label class="col-form-label">Alamat Email</label>
                                    <input class="form-control" type="email" required="" name="email"
                                        placeholder="infinite@student.uny.ac.id">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control" type="password" name="password" required=""
                                            placeholder="*********">
                                        <div class="show-hide"><span class="show"> </span></div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="checkbox p-0">
                                        <input id="checkbox1" type="checkbox" name="remember" value="1">
                                        <label class="text-muted" for="checkbox1">Ingat saya</label>
                                    </div>
                                    {{-- <a class="link" href="#">Lupa password?</a> --}}
                                    <div class="text-end mt-3">
                                        <button class="btn btn-primary btn-block w-100" type="submit">Masuk</button>
                                    </div>
                                </div>
                                <h6 class="text-muted mt-4 or">Atau masuk dengan</h6>
                                <div class="social mt-4">
                                    <div class="btn-showcase">
                                        <a class="btn btn-light" href="{{ route('login.infinite-sso') }}"><i
                                                class="fa fa-sign-in"></i>
                                            INFINITE SSO</a>
                                    </div>
                                </div>
                                <p class="mt-4 mb-0 text-center">Belum punya akun?<a class="ms-2"
                                        href="{{ route('register') }}">Buat Akun</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- latest jquery-->
        <script src="{{ asset('admin-panel/assets/js/jquery-3.5.1.min.js') }}"></script>
        <!-- Bootstrap js-->
        <script src="{{ asset('admin-panel/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
        <!-- feather icon js-->
        <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather.min.js') }}"></script>
        <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
        <!-- scrollbar js-->
        <!-- Sidebar jquery-->
        <script src="{{ asset('admin-panel/assets/js/config.js') }}"></script>
        <!-- Plugins JS start-->
        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        <script src="{{ asset('admin-panel/assets/js/script.js') }}"></script>
        <!-- login js-->
        <!-- Plugin used-->
    </div>
@endsection
