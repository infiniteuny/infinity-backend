@extends('admin.layouts.app')

@section('title', 'User')

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
                        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">User</a></li>
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

                // // Re - initialize select2 with empty value
                $('.select-program-study').val(null).trigger('change');

                // // Majors List Select2
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
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Akun {{ $user->name }}</h4>
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
                                                src="{{ $user->avatar }}">
                                            <div class="media-body">
                                                <h5 class="mb-1">{{ $user->name }}</h5>
                                                <p>{{ $user->student_id }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">NIM</label>
                                    <input class="form-control" value="{{ $user->student_id }}" name="student_id" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="role" id="role">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ $user->roles == $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat Email</label>
                                    <input class="form-control" value="{{ $user->email }}" name="email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="********" name="password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ulangi Password</label>
                                    <input class="form-control" type="password" placeholder="********"
                                        name="password_confirmation">
                                </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary btn-block" type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
