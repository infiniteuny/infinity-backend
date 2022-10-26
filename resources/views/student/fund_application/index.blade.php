@extends('student.layouts.app')

@section('title', 'Pengajuan Dana')

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
                        <li class="breadcrumb-item active">Pengajuan Dana</li>
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
            var table = $('.fund_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('student.fund-application.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'competition_name',
                        name: 'competition_name'
                    },
                    {
                        data: 'competition_date',
                        name: 'competition_date'
                    },
                    {
                        data: 'competition_branch',
                        name: 'competition_branch'
                    },
                    {
                        data: 'team_name',
                        name: 'team_name'
                    },
                    {
                        data: null,
                        name: 'team_members',
                        render: function(data) {
                            return data.team_members.map(function(item) {
                                return `<br> <span class="badge badge-primary counter" style="text-transform: capitalize">${item.role}</span> ${item.name}`;
                            })
                        }
                    },
                    {
                        data: null,
                        name: 'student_id_card',
                        render: function(data) {
                            return `
                                <form action="{{ url('student/fund-application') }}/${data.id}/download/student-id-card" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-download"></i> </button>
                                </form>
                            `;
                        }
                    },
                    {
                        data: null,
                        name: 'letter_of_acceptance',
                        render: function(data) {
                            return `
                                <form action="{{ url('student/fund-application') }}/${data.id}/download/letter-of-acceptance" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-download"></i> </button>
                                </form>
                            `;
                        }
                    },
                    {
                        data: null,
                        name: 'budget_plan',
                        render: function(data) {
                            return `
                                <form action="{{ url('student/fund-application') }}/${data.id}/download/budget-plan" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-download"></i> </button>
                                </form>
                            `;
                        }
                    },
                    {
                        data: null,
                        name: 'status',
                        render: function(data) {
                            if (data.status == 'waiting') {
                                return `<span class="badge badge-warning">${data.status}</span>`;
                            } else if (data.status == 'accepted') {
                                return `<span class="badge badge-success">${data.status}</span>`;
                            } else if (data.status == 'rejected') {
                                return `<span class="badge badge-danger">${data.status}</span>`;
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'competition_url',
                        render: function(data) {
                            return '<a href="' + data.competition_url +
                                '" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-external-link"></i></a>';
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        render: function(data) {
                            if (data.status == 'waiting') {
                                return `
                                <form action="{{ url('student/fund-application') }}/${data.id}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="btn-group btn-group-pill" role="group" aria-label="Manage Button">
                                        <button type="button" onclick="goTo('${data.id}')" class="btn btn-primary btn-sm btn-edit-fund"><i class="fa fa-edit"></i></button>
                                        <button type="submit" class="btn btn-danger btn-sm btn-detele-fund"><i class="fa fa-trash"></i></button>
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
        });
    </script>

    {{-- button on datatables --}}
    <script>
        function goTo(id) {
            var id = id;
            var url = '{{ route('student.fund-application.edit', ':id') }}';
            url = url.replace(':id', id);
            window.location.href = url;
        }

        var member = 0;

        function addMember() {
            $('#leader-input').after(`
                <div id="member-${member}" class="row g-3" style="margin-bottom: 1rem !important;">
                    <div class="col-md-4">
                        <label for="team_member[name][${member}]">Nama Anggota</label>
                        <input type="text" class="form-control" id="team_member[name][${member}]" name="team_member[name][${member}][]"
                            placeholder="Masukkan nama anggota tim" required>
                    </div>
                    <div class="col-md-4">
                        <label for="team_member[student_id][${member}]">NIM Anggota</label>
                        <input type="text" class="form-control" id="team_member[student_id][${member}]"
                            name="team_member[student_id][${member}][]" placeholder="Masukkan nim anggota tim" required>
                    </div>
                    <div class="col-md-3">
                        <label for="team_member[phone][${member}]">Nomor Hp Anggota</label>
                        <div class="input-group"><span class="input-group-text">+62</span>
                            <input type="number" class="form-control" id="team_member[phone][${member}]"
                                name="team_member[phone][${member}][]" placeholder="Masukkan nomor hp anggota tim" required>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label>Hapus</label>
                        <button class="btn btn-danger btn-xs" type="button"
                            onclick="removeMember(${member})"><i
                                class="fa fa-trash-o"></i></button>
                    </div>
                </div>
            `);
            member += 1;
        }

        function removeMember(id) {
            $('#member-' + id).remove();
        }

        $(document.body).on('click', '.btn-detele-fund', function(event) {
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

@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Data Pengajuan Dana Lomba </h5><span>Data Pengajuan Dana {{ auth()->user()->name }}.</span>
                    <div class="card-header-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-original-title="addFund"
                            data-bs-target="#addFundModal">Ajukan Dana</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="stripe hover fund_table" id="fund_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Kompetisi</th>
                                    <th>Tanggal Kompetisi</th>
                                    <th>Cabang Lomba</th>
                                    <th>Nama Tim</th>
                                    <th>Anggota Tim</th>
                                    <th>Scan KTA</th>
                                    <th>Letter Of Acceptance</th>
                                    <th>Rencana Anggaran Biaya</th>
                                    <th>Status</th>
                                    <th>Website Penyelenggara</th>
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

    <!-- Modal add fund -->
    <div class="modal fade" id="addFundModal" tabindex="-1" role="dialog" aria-labelledby="addFundModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFundModalLabel">Ajukan Dana Lomba</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('student.fund-application.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
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
                                    <label for="competition_url">Website Penyelenggara Kompetisi</label>
                                    <input type="text" class="form-control" id="competition_url" name="competition_url"
                                        placeholder="Masukkan URL website penyelenggara kompetisi"
                                        value="{{ old('competition_url') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="competition_date">Tanggal Kompetisi</label>
                                    <input type="date" class="form-control" id="competition_date" name="competition_date"
                                        placeholder="Masukkan tanggal kompetisi" value="{{ old('competition_date') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="competition_branch">Cabang Lomba</label>
                                    <input type="text" class="form-control" id="competition_branch"
                                        name="competition_branch" placeholder="Masukkan nama cabang lomba"
                                        value="{{ old('competition_branch') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="team_name">Nama Tim</label>
                                    <input type="text" class="form-control" id="team_name" name="team_name"
                                        placeholder="Masukkan nama tim" value="{{ old('team_name') }}" required>
                                </div>
                            </div>
                        </div>
                        <div id="leader-input" class="row g-3" style="margin-bottom: 1rem !important;">
                            <div class="col-md-4">
                                <label for="team_leader_name">Nama Ketua Tim</label>
                                <input type="text" class="form-control" id="team_leader_name"
                                    name="team_leader[name]" placeholder="Masukkan nama ketua tim"
                                    value="{{ old('team_leader_name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="team_leader_student_id">NIM Ketua Tim</label>
                                <input type="number" class="form-control" id="team_leader_student_id"
                                    name="team_leader[student_id]" placeholder="Masukkan nim ketua tim"
                                    value="{{ old('team_leader_student_id') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="team_leader_phone">Nomor Hp Ketua Tim</label>
                                <div class="input-group"><span class="input-group-text">+62</span>
                                    <input type="number" class="form-control" id="team_leader[phone]"
                                        name="team_leader[phone]" placeholder="Masukkan nomor hp ketua tim" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <button type="button" onclick="addMember()" class="btn btn-success"><i
                                            class="fa fa-user"></i>
                                        Tambah Anggota</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="student_id_card">Scan KTA Restek (Seluruh Anggota)</label>
                                    <input type="file" class="form-control" id="student_id_card"
                                        name="student_id_card" placeholder="Unggah KTA Restek (Seluruh Anggota)"
                                        value="{{ old('student_id_card') }}" required>
                                    <small class="text-danger">*Pdf maksimal 2MB</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="loa">Letter Of Acceptance (Bukti Pengumuman Finalis)</label>
                                    <input type="file" class="form-control" id="loa" name="loa"
                                        placeholder="Unggah Letter Of Acceptance" value="{{ old('loa') }}" required>
                                    <small class="text-danger">*Pdf maksimal 2MB</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="budget_plan">Rencana Anggaran Biaya</label>
                                    <small><a target="_blank" href="https://unyku.id/format-rab"> (Download format
                                            RAB)</a></small>
                                    <input type="file" class="form-control" id="budget_plan" name="budget_plan"
                                        placeholder="Unggah Rencana Anggaran Biaya" value="{{ old('budget_plan') }}"
                                        required>
                                    <small class="text-danger">*Pdf maksimal 2MB</small>
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
