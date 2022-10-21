@extends('student.layouts.app')

@section('title', 'Prestasi')

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
                        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Prestasi</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/select2.css') }}">

@endsection

@section('js')
    <script src="{{ asset('admin-panel/assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/select2/select2-custom.js') }}"></script>

    {{-- select2 --}}
    <script>
        $(document).ready(function() {
            $(".select-leader").select2({
                placeholder: 'Pilih Ketua Tim',
                language: {
                    searching: function() {
                        return "Mencari...";
                    },
                    errorLoading: function() {
                        return 'Data gagal dimuat';
                    },
                },
                ajax: {
                    url: "{{ url('api/members') }}",
                    dataType: 'json',
                    cache: true,
                    delay: 250,
                    data: function(params) {
                        var q = params.term;
                        var query = {
                            q: q,
                        }
                        return query;
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(member) {
                                return {
                                    text: member.name,
                                    id: member.id
                                }
                            }),
                        }
                    }
                },
            });

            $(".select-member").select2({
                placeholder: 'Pilih Anggota Tim',
                language: {
                    searching: function() {
                        return "Mencari...";
                    },
                    errorLoading: function() {
                        return 'Data gagal dimuat';
                    },
                },
                ajax: {
                    url: "{{ url('api/members') }}",
                    dataType: 'json',
                    cache: true,
                    delay: 250,
                    data: function(params) {
                        var q = params.term;
                        var query = {
                            q: q,
                        }
                        return query;
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(member) {
                                return {
                                    text: member.name,
                                    id: member.id
                                }
                            }),
                        }
                    }
                },
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-xl-12">
                    <form class="card" action="{{ route('student.achievement.update', $data['achievement']->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="card-header">
                            <h4 class="card-title mb-0">Edit Prestasi Tim {{ $data['achievement']->team_name }}</h4>
                            <div class="card-options"><a class="card-options-collapse" href="#"
                                    data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a
                                    class="card-options-remove" href="#" data-bs-toggle="card-remove"><i
                                        class="fe fe-x"></i></a></div>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="team_name">Nama Tim</label>
                                        <input type="text" class="form-control" id="team_name" name="team_name"
                                            placeholder="Masukkan nama tim" value="{{ $data['achievement']->team_name }}"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_name">Nama Kompetisi</label>
                                        <input type="text" class="form-control" id="competition_name"
                                            name="competition_name" placeholder="Masukkan nama kompetisi"
                                            value="{{ $data['achievement']->competition_name }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_organizer">Penyelenggara</label>
                                        <input type="text" class="form-control" id="competition_organizer"
                                            name="competition_organizer" placeholder="Masukkan penyelenggara"
                                            value="{{ $data['achievement']->organizer }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="description">Deskripsi</label>
                                        <textarea name="description" id="description" class="form-control" rows="3" required>{{ $data['achievement']->description }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="date">Tanggal Kompetisi</label>
                                        <input type="date" class="form-control" id="date" name="date"
                                            placeholder="Masukkan Tanggal Aktif" value="{{ $data['achievement']->date }}"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2" style="margin-bottom: 1rem !important;">
                                <div class="col-md-6">
                                    <label for="fakultas">Ketua</label>
                                    <select class="select-leader col-sm-12 btn-square" name="leader">
                                    </select>
                                    <small class="text-danger">*Ketua harus anggota INFINITE, jika bukan pilih diri sendiri
                                        sebagai ketua</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="member">Anggota</label>
                                    <select class="select-member col-sm-12 btn-square" name="member[]" multiple="multiple">
                                    </select>
                                    <small class="text-danger">*Pilih anggota INFINITE lain yang terlibat dalam tim, jika
                                        tidak
                                        ada kosongkan</small>
                                </div>
                            </div>
                            <div class="row g-3" style="margin-bottom: 1rem !important;">
                                <div class="col-md-4">
                                    <label for="competition_rank">Pencapaian</label>
                                    <select class="form-select digits" id="competition_rank" name="competition_rank"
                                        required>
                                        @foreach ($data['competition_ranks'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_rank_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="competition_type">Tipe Kompetisi</label>
                                    <select class="form-select digits" id="competition_type" name="competition_type"
                                        required>
                                        @foreach ($data['competition_types'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_type_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="competition_scale">Skala Kompetisi</label>
                                    <select class="form-select digits" id="competition_scale" name="competition_scale"
                                        required>
                                        @foreach ($data['competition_scales'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_scale_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3" style="margin-bottom: 1rem !important;">
                                <div class="col-md-4">
                                    <label for="competition_outputs">Output Kompetisi</label>
                                    <select class="form-select digits" id="competition_outputs"
                                        name="competition_outputs" required>
                                        @foreach ($data['competition_outputs'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_output_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="competition_time_range">Rentang Waktu Kompetisi</label>
                                    <select class="form-select digits" id="competition_time_range"
                                        name="competition_time_range" required>
                                        @foreach ($data['competition_time_ranges'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_time_range_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="competition_level">Level Kompetisi</label>
                                    <select class="form-select digits" id="competition_level" name="competition_level"
                                        required>
                                        @foreach ($data['competition_levels'] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $data['achievement']->competition_level_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="date">Dokumentasi</label>
                                        <div style="max-width: 350px" class="pb-3">
                                            <img class="img-fluid" src="{{ $data['achievement']->image }}"
                                                alt="dokumentasi">
                                        </div>
                                        <input type="file" class="form-control" id="image" name="image"
                                            placeholder="Unggah Dokumentasi Lomba">
                                        <small class="text-danger">*Jpg, jpeg, png maksimal 2MB</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="date">Jumlah Poin</label>
                                        <input type="text" class="form-control" id="point" name="point"
                                            placeholder="Jumlah Poin" value="{{ $data['achievement']->point }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit">Update Prestasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
