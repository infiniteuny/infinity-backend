@extends('auth.layouts.app')

@section('title', 'Register')

@section('content')

    <!-- login page start-->
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card">
                    <div>
                        <div><a class="logo" href="index.html"><img class="img-fluid for-light"
                                    src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" alt="looginpage"><img
                                    class="img-fluid for-dark"
                                    src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" alt="looginpage"></a>
                        </div>
                        <div class="login-main">
                            <form class="theme-form" action="{{ url('/register') }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                <h4>Buat akun mu!</h4>
                                <p>Masukkan informasi berikut untuk melanjutkan pembuatan akun</p>
                                <div class="form-group">
                                    @csrf
                                    <label class="col-form-label">NIM</label>
                                    <input type="hidden" name="name" value="{{ $userdata['name'] }}" required>
                                    <input type="hidden" name="email" value="{{ $userdata['email'] }}" required>
                                    <input type="hidden" name="password" value="{{ $userdata['password'] }}" required>
                                    <input type="hidden" name="password_confirmation" value="{{ $userdata['password'] }}"
                                        required>
                                    <input type="hidden" name="role" value="{{ $userdata['role'] }}" required>
                                    <input type="hidden" name="provider" value="{{ $userdata['provider'] }}" required>
                                    <input type="hidden" name="provider_id" value="{{ $userdata['provider_id'] }}"
                                        required>
                                    <input type="hidden" name="avatar" value="{{ $userdata['avatar'] }}" required>
                                    <input class="form-control" type="number" name="student_id" required=""
                                        placeholder="20999999999">
                                </div>
                                <div class="form-group mb-0">
                                    <button class="btn btn-primary btn-block w-100" type="submit">Buat Akun</button>
                                </div>
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
