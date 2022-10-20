@extends('student.layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Member Panel</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"> <i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Daftar Ulang</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins')
@endsection

@section('js')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12 box-col-12 xl-100">
                <div class="card custom-card custom-profile">
                    <div class="pb-0">
                        <div class="card-profile"><img class="user-img rounded-circle" src="{{ auth()->user()->avatar }}"
                                alt="">
                        </div>
                        <div class="text-center profile-details">
                            <h4>{{ auth()->user()->name }}</h4>
                            <h6 class="f-16 pb-5">
                                {{ auth()->user()->members->is_extraordinary ? 'Anggota Luar Biasa' : 'Anggota' }}</h6>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-4 col-sm-4">
                                <h6 class="font-roboto">Tanggal Masuk</h6>
                                <h5 class="counter">
                                    {{ \Carbon\Carbon::parse(auth()->user()->members->start_date)->format('d M Y') }}</h5>
                            </div>
                            <div class="col-4 col-sm-4">
                                <h6 class="font-roboto">Tanggal Selesai</h6>
                                <h5 class="counter">
                                    {{ \Carbon\Carbon::parse(auth()->user()->members->end_date)->format('d M Y') }}</h5>
                            </div>
                            <div class="col-4 col-sm-4">
                                <h6 class="font-roboto">Status</h6>
                                <h5 class="counter">{{ auth()->user()->members->status ? 'Aktif' : 'Tidak Aktif' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('student.re-registration.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg w-100">Daftar Ulang</button>
                </form>
            </div>
        </div>
    </div>
@endsection
