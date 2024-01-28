@extends('admin.layouts.app')

@section('title', 'Member')

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
                        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Member</a></li>
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
    <script>
        $(document).ready(function() {
            $(".select-faculty").select2({
                placeholder: 'Pilih Fakultas',
                language: {
                    searching: function() {
                        return "Mencari...";
                    },
                    errorLoading: function() {
                        return 'Data gagal dimuat';
                    },
                },
                ajax: {
                    url: "{{ route('faculties.list') }}",
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
                            results: $.map(data, function(faculty) {
                                return {
                                    text: `${faculty.name}`,
                                    id: faculty.id
                                }
                            }),
                        }
                    }
                },
            });

            $(".select-program-study").select2({
                // disabled: true,
                placeholder: "Pilih fakultas terlebih dahulu",
            });

            $('.select-faculty').on('change', function(e) {
                let selectedFacultyId = $(this).select2('data')[0]['id'];

                // Re-initialize select2 with empty value
                $('.select-program-study').val(null).trigger('change');

                // Majors List Select2
                let endpointUrl =
                    `{{ url('api/faculties') }}/${selectedFacultyId}/program-studies`;
                if (selectedFacultyId) {
                    $('.select-program-study').select2({
                        placeholder: 'Pilih Program Studi',
                        disabled: false,
                        language: {
                            searching: function() {
                                return "Mencari...";
                            },
                            errorLoading: function() {
                                return 'Data gagal dimuat';
                            },
                        },
                        ajax: {
                            url: endpointUrl,
                            dataType: 'json',
                            cache: true,
                            delay: 250, //minimize api request with delay
                            data: function(params) {
                                var q = params.term;
                                var query = {
                                    q: q,
                                }
                                return query;
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data, function(programStudy) {
                                        return {
                                            text: `${programStudy.name}`,
                                            id: programStudy.id
                                        }
                                    }),
                                }
                            }
                        },
                    });
                } else {
                    $('.select-faculty').empty();
                    $('.select-program-study').empty();
                }
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            @php
                                $name = explode(' ', $member->name)[0];
                            @endphp
                            <h4 class="card-title mb-0">Akun {{ $name }}</h4>
                            <div class="card-options"><a class="card-options-collapse" href="#"
                                    data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a
                                    class="card-options-remove" href="#" data-bs-toggle="card-remove"><i
                                        class="fe fe-x"></i></a></div>
                        </div>
                        <div class="card-body">
                            <form action="{{ $user ? route('admin.user.update', Crypt::encryptString($user->id)) : '#' }}"
                                method="POST" enctype="application/x-www-form-urlencoded">
                                @csrf
                                @method('PUT')
                                <div class="row mb-2">
                                    <div class="profile-title">
                                        <div class="media"> <img class="img-70 rounded-circle" alt=""
                                                src="{{ $member->avatar }}">
                                            <div class="media-body">
                                                <h5 class="mb-1">{{ $member->name }}</h5>
                                                <p>{{ $member->student_id }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">NIM</label>
                                    <input class="form-control" value="{{ $member->student_id }}" name="student_id"
                                        disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="role" id="role"
                                        {{ $user ? '' : 'disabled' }}>
                                        @if ($user)
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}"
                                                    {{ $user->roles == $role->name ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="admin">Admin</option>
                                            <option value="student" {{ $user ? '' : 'selected' }}>Student</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat Email</label>
                                    <input class="form-control" value="{{ $member->email }}" name="email"
                                        {{ $user ? '' : 'disabled' }}>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="********" name="password"
                                        {{ $user ? '' : 'disabled' }}>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ulangi Password</label>
                                    <input class="form-control" type="password" placeholder="********"
                                        name="password_confirmation" {{ $user ? '' : 'disabled' }}>
                                </div>
                                <div class="form-footer">
                                    <button class="btn btn-primary btn-block {{ $user ? '' : 'disabled' }}"
                                        type="submit">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <form class="card" action="{{ route('admin.member.update', Crypt::encryptString($member->id)) }}"
                        method="POST" enctype="application/x-www-form-urlencoded">
                        @method('PUT')
                        @csrf
                        <div class="card-header">
                            <h4 class="card-title mb-0">Edit Profil {{ $name }}</h4>
                            <div class="card-options"><a class="card-options-collapse" href="#"
                                    data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a
                                    class="card-options-remove" href="#" data-bs-toggle="card-remove"><i
                                        class="fe fe-x"></i></a></div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama</label>
                                        <input class="form-control" type="text" placeholder="Nama Kamu" name="name"
                                            value="{{ $member->name }}">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">NIM</label>
                                        <input class="form-control" type="number" placeholder="NIM Kamu"
                                            name="student_id" value="{{ $member->student_id }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Fakultas</label>
                                        <select class="select-faculty col-sm-12 btn-square" name="faculty">
                                            <option value="{{ $member->programStudies->faculties->id }}">
                                                {{ $member->programStudies->faculties->name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label class="form-label">Program Studi</label>
                                        <select class="select-program-study col-sm-12 btn-square" name="programStudy">
                                            <option value="{{ $member->programStudies->id }}">
                                                {{ $member->programStudies->name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Status Keanggotaan</label>
                                        <select class="form-select btn-square" name="status">
                                            <option value="1" {{ $member->status ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ $member->status ? '' : 'selected' }}>Tidak Aktif
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Anggota Luar Biasa</label>
                                        <select class="form-select btn-square" name="is_extraordinary">
                                            <option value="1" {{ $member->is_extraordinary ? 'selected' : '' }}>Ya
                                            </option>
                                            <option value="0" {{ $member->is_extraordinary ? '' : 'selected' }}>Tidak
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Masuk INFINITE</label>
                                        <input class="form-control digits" type="date"
                                            value="{{ $member->start_date }}" name="date_start"
                                            data-bs-original-title="" title="">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input class="form-control digits" type="date"
                                            value="{{ $member->end_date }}" name="date_end" data-bs-original-title=""
                                            title="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit">Update Profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
