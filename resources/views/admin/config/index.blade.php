@extends('admin.layouts.app')

@section('title', 'Setting')

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
                        <li class="breadcrumb-item active">Settings</li>
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
    <script>
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

        function check(id) {
            if ($('#' + id).prop('checked') == true) {
                $('#' + id + '_hidden').val('true');
            } else {
                $('#' + id + '_hidden').val('false');
            }
        }
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pengaturan</h5><span>Pengaturan sistem dashboard.</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.config.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        @foreach ($data['configs'] as $config => $item)
                            @if ($item['type'] == 'string')
                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label"
                                        style="text-transform: capitalize">{{ str_replace('_', ' ', $config) }}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" type="text" placeholder="Pengaturan"
                                            data-bs-original-title="" title="" name="{{ $config }}"
                                            value="{{ $item['value'] }}">
                                    </div>
                                </div>
                            @elseif ($item['type'] == 'integer')
                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label"
                                        style="text-transform: capitalize">{{ str_replace('_', ' ', $config) }}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" type="number" placeholder="Pengaturan"
                                            data-bs-original-title="" title="" name="{{ $config }}"
                                            value="{{ $item['value'] }}">
                                    </div>
                                </div>
                            @elseif ($item['type'] == 'boolean')
                                <div class="media mb-2">
                                    <label class="col-form-label m-r-10"
                                        style="text-transform: capitalize">{{ str_replace('_', ' ', $config) }}</label>
                                    <div class="media-body text-end icon-state switch-outline">
                                        <label class="switch">
                                            <input type="checkbox" data-bs-original-title="" title=""
                                                id="{{ $config }}" onclick="check(this.id)"
                                                {{ $item['value'] == 'true' ? 'checked' : '' }} value="true"><span
                                                class="switch-state bg-primary"></span>
                                            <input type="hidden" name="{{ $config }}"
                                                id="{{ $config . '_hidden' }}" value="{{ $item['value'] }}">
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
