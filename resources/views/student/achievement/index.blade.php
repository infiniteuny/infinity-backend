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
                        <li class="breadcrumb-item active">Prestasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/select2.css') }}">
    <style>
        .dt-buttons {
            margin-left: 50px;
        }
    </style>
@endsection

@section('js')
    <script src="{{ asset('admin-panel/assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatables/datatable.custom.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/jszip.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatable-extension/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- datatables --}}
    <script type="text/javascript">
        $(function() {
            function format(d) {
                return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
                    '<tr>' +
                    '<td>Tipe Kompetisi:</td>' +
                    '<td>' + d.competition_type + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Skala Kompetisi:</td>' +
                    '<td>' + d.competition_scale + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Output Kompetisi:</td>' +
                    '<td>' + d.competition_output + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Rentang Waktu Kompetisi:</td>' +
                    '<td>' + d.competition_time_range + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Level Kompetisi:</td>' +
                    '<td>' + d.competition_level + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Point:</td>' +
                    '<td>' + d.point + '</td>' +
                    '</tr>' +
                    '</table>';
            }
            var table = $('.achievement_table').DataTable({
                aLengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                iDisplayLength: 10,
                dom: 'lBfrtip',
                buttons: [
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ],
                processing: true,
                serverSide: true,
                ajax: "{{ route('student.achievement.index') }}",
                columns: [{
                        "className": 'details-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'team_name',
                        name: 'team_name'
                    },
                    {
                        data: 'competition_name',
                        name: 'competition_name'
                    },
                    {
                        data: 'organizer',
                        name: 'organizer'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: null,
                        name: 'member',
                        render: function(data) {
                            return data.member.map(function(item) {
                                return `<br> <span class="badge badge-primary counter" style="text-transform: capitalize">${item.role}</span> ${item.name}`;
                            })
                        }
                    },
                    {
                        data: null,
                        name: 'competition_rank',
                        render: function(data) {
                            return `<span class="badge badge-secondary counter">${data.competition_rank}</span>`;
                        }
                    },
                    {
                        data: null,
                        name: 'status',
                        render: function(data) {
                            if (data.status == 'accepted') {
                                return `<span class="badge badge-success counter" style="text-transform: Capitalize">${data.status}</span>`;
                            } else if (data.status == 'rejected') {
                                return `<span class="badge badge-danger counter" style="text-transform: Capitalize">${data.status}</span>`;
                            } else {
                                return `<span class="badge badge-warning counter" style="text-transform: Capitalize">${data.status}</span>`;
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        render: function(data) {
                            if (data.status == 'waiting') {
                                return `
                                <form action="{{ url('student/achievement') }}/${data.id}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="btn-group btn-group-pill" role="group" aria-label="Manage Button">
                                        <button type="button" onclick="goTo('${data.id}')" class="btn btn-primary btn-sm btn-edit-achievement"><i class="fa fa-edit"></i></button>
                                        <button type="submit" class="btn btn-danger btn-sm btn-detele-achievement"><i class="fa fa-trash"></i></button>
                                    </div>
                                </form>
                                `;
                            } else {
                                return ``;
                            }
                        },
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            $('.achievement_table tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });
        });
    </script>

    {{-- button on datatables --}}
    <script>
        function goTo(id) {
            var id = id;
            var url = '{{ route('student.achievement.edit', ':id') }}';
            url = url.replace(':id', id);
            window.location.href = url;
        }

        $(document.body).on('click', '.btn-detele-achievement', function(event) {
            event.preventDefault();
            var $form = $(this).closest('form');

            swal({
                    title: "Yakin nih?",
                    text: "Kalo udah dihapus, kamu gabisa kembaliin datanya!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Poof! Berhasil menghapus data!", {
                            icon: "success",
                        });
                        $form.submit();
                    } else {
                        swal("Hapus data dibatalkan!");
                    }
                })
        });
    </script>

    {{-- select2 --}}
    <script>
        $(document).ready(function() {
            $(".select-leader").select2({
                dropdownParent: $('#addAchievementModal'),
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
                dropdownParent: $('#addAchievementModal'),
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
        });
    </script>

@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Data Prestasi {{ auth()->user()->name }}</h5><span>Data prestasi {{ auth()->user()->name }} dari
                        tahun ke tahun.</span>
                    <div class="card-header-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-original-title="addAchievement"
                            data-bs-target="#addAchievementModal">Ajukan Prestasi</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="stripe hover achievement_table" id="achievement_table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>#</th>
                                    <th>Nama Tim</th>
                                    <th>Nama Kompetisi</th>
                                    <th>Penyelenggara</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal</th>
                                    <th>Anggota Tim</th>
                                    <th>Pencapaian</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal add achievement -->
    <div class="modal fade" id="addAchievementModal" tabindex="-1" role="dialog"
        aria-labelledby="addAchievementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAchievementModalLabel">Tambah Prestasi</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('student.achievement.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="team_name">Nama Tim</label>
                                    <input type="text" class="form-control" id="team_name" name="team_name"
                                        placeholder="Masukkan nama tim" value="{{ old('team_name') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="competition_name">Nama Kompetisi</label>
                                    <input type="text" class="form-control" id="competition_name" name="competition_name"
                                        placeholder="Masukkan nama kompetisi" value="{{ old('competition_name') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="competition_organizer">Penyelenggara</label>
                                    <input type="text" class="form-control" id="competition_organizer"
                                        name="competition_organizer" placeholder="Masukkan penyelenggara"
                                        value="{{ old('competition_organizer') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="description">Deskripsi</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="date">Tanggal Kompetisi</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                        placeholder="Masukkan Tanggal Aktif" value="{{ old('date') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2" style="margin-bottom: 1rem !important;">
                            <div class="col-md-6">
                                <label for="fakultas">Ketua</label>
                                <select class="select-leader col-sm-12 btn-square" name="leader" required>
                                </select>
                                <small class="text-danger">*Ketua harus anggota INFINITE, jika bukan pilih diri sendiri
                                    sebagai ketua</small>

                            </div>
                            <div class="col-md-6">
                                <label for="member">Anggota</label>
                                <select class="select-member col-sm-12 btn-square" name="member[]" multiple="multiple">
                                </select>
                                <small class="text-danger">*Pilih anggota INFINITE lain yang terlibat dalam tim, jika tidak
                                    ada kosongkan</small>
                            </div>
                        </div>
                        <div class="row g-3" style="margin-bottom: 1rem !important;">
                            <div class="col-md-4">
                                <label for="competition_rank">Pencapaian</label>
                                <select class="form-select digits" id="competition_rank" name="competition_rank"
                                    required>
                                    @foreach ($data['competition_ranks'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="competition_type">Tipe Kompetisi</label>
                                <select class="form-select digits" id="competition_type" name="competition_type"
                                    required>
                                    @foreach ($data['competition_types'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="competition_scale">Skala Kompetisi</label>
                                <select class="form-select digits" id="competition_scale" name="competition_scale"
                                    required>
                                    @foreach ($data['competition_scales'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-3" style="margin-bottom: 1rem !important;">
                            <div class="col-md-4">
                                <label for="competition_outputs">Output Kompetisi</label>
                                <select class="form-select digits" id="competition_outputs" name="competition_outputs"
                                    required>
                                    @foreach ($data['competition_outputs'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="competition_time_range">Rentang Waktu Kompetisi</label>
                                <select class="form-select digits" id="competition_time_range"
                                    name="competition_time_range" required>
                                    @foreach ($data['competition_time_ranges'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="competition_level">Level Kompetisi</label>
                                <select class="form-select digits" id="competition_level" name="competition_level"
                                    required>
                                    @foreach ($data['competition_levels'] as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="date">Dokumentasi</label>
                                    <input type="file" class="form-control" id="image" name="image"
                                        placeholder="Unggah Dokumentasi Lomba" value="{{ old('image') }}" required>
                                    <small class="text-danger">*Jpg, jpeg, png maksimal 2MB</small>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" type="submit">Tambah</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
