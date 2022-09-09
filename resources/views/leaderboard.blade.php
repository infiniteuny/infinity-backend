@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="assets/images/download-bgshape.png" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>Leaderboard Kompetisi</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content mt-5 mb-5">

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Jumlah Prestasi</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 100; $i++)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <img src="https://mdbcdn.b-cdn.net/img/new/avatars/2.webp" class="rounded-circle"
                                            style="width: 50px;" alt="Avatar" />
                                        dadas
                                    </td>
                                    <td>Teknologi Informasi</td>
                                    <td><b>5</b> prestasi</td>
                                    <td><b>120</b> total points</td>
                                </tr>
                            @endfor
                            {{-- @foreach ($members as $member)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->year }}</td>
                                    <td>{{ $member->study_program }}</td>
                                    <td>{{ $member->score }}</td>
                                </tr>
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
    <!--// Page Content -->
@endsection
