{{-- <x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-auth-card>
</x-guest-layout> --}}

@extends('auth.layouts.app')

@section('title', 'Sign up')

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
                            <form class="theme-form" action="{{ route('verification.send') }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                @csrf
                                <h4>Selesaikan pembuatan akun kamu!</h4>
                                <p>Terima kasih sudah mendaftar! Sebelum mulai, boleh minta tolong verifikasi email kamu
                                    dulu ga? klik link yang kami kirimkan ke email kamu ya! Kalo kamu ga nerima email,
                                    dengan senang hati akan kami kirim ulang.</p>
                                <div class="form-group mb-2">
                                    <button class="btn btn-outline-primary btn-block w-100" type="submit">Kirim ulang email
                                        verifikasi</button>
                                </div>
                                <div class="form-group mb-2">
                                    @csrf
                                    <a class="btn btn-outline-danger btn-block w-100"
                                        href="{{ route('logout') }}">Keluar</a>
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
