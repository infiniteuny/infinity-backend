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
                        <li class="breadcrumb-item active">Akun</li>
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
@endsection

@section('js')
    <script src="{{ asset('admin-panel/assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/datatable/datatables/datatable.custom.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            var table = $('.user_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100,
                ajax: "{{ route('admin.user.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: null,
                        name: 'name',
                        render: function(data) {
                            return `
                                <div class="align-middle image-sm-size" style="display: flex;align-items: center;"><img style="width: 41px;"
                                        class="img-radius align-top m-r-15 rounded-circle"
                                        src="${data.avatar}" alt="avatar">
                                        <b>${data.name}<b/>
                                </div>
                            `
                        }
                    },
                    {
                        data: 'student_id',
                        name: 'student_id'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'email_verified_at',
                        name: 'email_verified_at'
                    },
                    {
                        data: null,
                        name: 'role',
                        render: function(data) {
                            return `
                                <span class="badge badge-${data.role == 'admin' ? 'primary' : 'warning'} counter" style="text-transform: capitalize">${data.role}</span>
                            `
                        }
                    },
                    {
                        data: null,
                        name: 'provider',
                        render: function(data) {
                            return `
                                <span class="badge badge-${data.provider == 'google' ? 'info' : 'secondary'} counter" style="text-transform: capitalize">${data.provider}</span>
                            `
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        render: function(data) {
                            return `
                            <form action="{{ url('admin/user') }}/${data.id}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="btn-group btn-group-pill" role="group" aria-label="Manage Button">
                                    <button type="button" onclick="goTo('${data.id}')" class="btn btn-primary btn-sm btn-edit-user"><i class="fa fa-edit"></i></button>
                                    <button type="submit" class="btn btn-danger btn-sm btn-detele-user"><i class="fa fa-trash"></i></button>
                                </div>
                            </form>
                            `;
                        },
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
    <script>
        function goTo(id) {
            var id = id;
            var url = '{{ route('admin.user.edit', ':id') }}';
            url = url.replace(':id', id);
            window.location.href = url;
        }

        $(document.body).on('click', '.btn-detele-user', function(event) {
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
                    <h5>Daftar Akun INFINITE UNY</h5><span>Data anggota INFINITE UNY yang sudah membuat akun.</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive emplyoee-table">
                        <table class="table-hover table-stripe user_table" id="user_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Email</th>
                                    <th>Verified</th>
                                    <th>Role</th>
                                    <th>Login</th>
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
@endsection
