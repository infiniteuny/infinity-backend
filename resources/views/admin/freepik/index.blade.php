@extends('admin.layouts.app')

@section('title', 'Freepik Downloader')

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
                        <li class="breadcrumb-item active">Freepik Downloader</li>
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
            var table = $('.freepik_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.freepik.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'file_name',
                        name: 'file_name'
                    },
                    {
                        data: null,
                        name: 'status',
                        render: function(data) {
                            if (data.status == 'completed') {
                                return `<span class="badge badge-success counter" style="text-transform: Capitalize">${data.status}</span>`;
                            } else if (data.status == 'failed') {
                                return `<span class="badge badge-danger counter" style="text-transform: Capitalize">${data.status}</span>`;
                            } else {
                                return `<span class="badge badge-warning counter" style="text-transform: Capitalize">${data.status}</span>`;
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'file_size',
                        render: function(data) {
                            return `${data.file_size} MB`;
                        }
                    },
                    {
                        data: null,
                        name: 'status',
                        render: function(data) {
                            if (data.status == 'completed') {
                                return `
                                    <form action="{{ url('admin/freepik') }}/${data.id}/download" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-download"></i> </button>
                                    </form>
                                `;
                            } else {
                                return `
                                    <button type="button" class="btn btn-success btn-xs disabled"><i class="fa fa-download"></i> </button>
                                `;
                            }
                        }
                    },
                ]
            });
        });
    </script>

@endsection

@section('content')
    <div class="container-fluid">

        <div class="row second-chart-list third-news-update">
            <div class="col-sm-6 col-xl-6 col-lg-6">
                <div class="card o-hidden static-top-widget-card">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Kuota Download Freepik Hari Ini</h6>
                                <h4 class="mb-0 counter">{{ $data['freepik']['used'] . '/' . $data['freepik']['quota'] }}
                                </h4>
                            </div>
                            <svg width="44" height="46" viewBox="0 0 44 46" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M24.2309 21.589L27.1376 18.6822C28.0313 17.7791 29.4611 17.5534 30.5241 18.2683C31.9634 19.2278 32.1045 21.1939 30.9474 22.3509L23.1585 30.1399C22.293 31.0053 20.8914 31.0053 20.0259 30.1399L12.237 22.3509C11.0799 21.1939 11.221 19.2278 12.6603 18.2683C13.7139 17.5628 15.1437 17.7885 16.0374 18.6822L18.9441 21.589V2.64335C18.9441 1.18528 20.1294 0 21.5875 0C23.0456 0 24.2309 1.18528 24.2309 2.64335V21.589ZM28.7457 31.5223C28.7928 31.099 29.1314 30.7791 29.5547 30.7791V30.7885H39.3568C41.4075 30.7885 43.0725 32.4536 43.0725 34.5043V42.2838C43.0725 44.3345 41.4075 45.9996 39.3568 45.9996H3.81731C1.76659 45.9996 0.101562 44.3345 0.101562 42.2838V34.4949C0.101562 32.4442 1.76659 30.7791 3.81731 30.7791H13.6005C14.0239 30.7791 14.3625 31.1084 14.4095 31.5223C14.7858 35.1534 17.8525 37.9848 21.5776 37.9848C25.3122 37.9848 28.3789 35.1534 28.7457 31.5223Z"
                                    fill="#51BB25" />
                            </svg>

                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-success" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-6 col-lg-6">
                <div class="card o-hidden static-top-widget-card">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Total Download Freepik</h6>
                                <h4 class="mb-0 counter">{{ $data['freepik']['total'] }}</h4>
                            </div>
                            <svg width="44" height="46" viewBox="0 0 44 46" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M24.2309 21.589L27.1376 18.6822C28.0313 17.7791 29.4611 17.5534 30.5241 18.2683C31.9634 19.2278 32.1045 21.1939 30.9474 22.3509L23.1585 30.1399C22.293 31.0053 20.8914 31.0053 20.0259 30.1399L12.237 22.3509C11.0799 21.1939 11.221 19.2278 12.6603 18.2683C13.7139 17.5628 15.1437 17.7885 16.0374 18.6822L18.9441 21.589V2.64335C18.9441 1.18528 20.1294 0 21.5875 0C23.0456 0 24.2309 1.18528 24.2309 2.64335V21.589ZM28.7457 31.5223C28.7928 31.099 29.1314 30.7791 29.5547 30.7791V30.7885H39.3568C41.4075 30.7885 43.0725 32.4536 43.0725 34.5043V42.2838C43.0725 44.3345 41.4075 45.9996 39.3568 45.9996H3.81731C1.76659 45.9996 0.101562 44.3345 0.101562 42.2838V34.4949C0.101562 32.4442 1.76659 30.7791 3.81731 30.7791H13.6005C14.0239 30.7791 14.3625 31.1084 14.4095 31.5223C14.7858 35.1534 17.8525 37.9848 21.5776 37.9848C25.3122 37.9848 28.3789 35.1534 28.7457 31.5223Z"
                                    fill="#51BB25" />
                            </svg>

                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-success" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>INFINITE Freepik Downloader </h5><span>Masukkan URL file freepik yang akan di download.</span>
                        <hr>
                        @if ($data['freepik']['is_can_download'])
                            <form action="{{ route('admin.freepik.store') }}" method="POST">
                                @csrf
                                <label class="form-label" for="freepik_url">Url Download</label>
                                <input type="text" class="form-control" name="freepik_url" id="freepik_url">

                                <button type="submit" class="btn btn-primary mt-3" id="download">Download</button>
                            </form>
                        @else
                            <label class="form-label" for="freepik_url">Url Download</label>
                            <input type="text" class="form-control" name="freepik_url" id="freepik_url">
                            <button data-bs-toggle="modal" data-original-title="addQuota" data-bs-target="#addQuotaModal"
                                class="btn btn-primary mt-3" id="download">Download</button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="stripe hover freepik_table" id="freepik_table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama File</th>
                                        <th>Status</th>
                                        <th>Ukuran File</th>
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
    </div>

    <!-- Modal add quota -->
    <div class="modal fade" id="addQuotaModal" tabindex="-1" role="dialog" aria-labelledby="addQuotaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuotaModalLabel">Kuota Download Hari Ini Habis</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container text-center">
                        <div class="row">
                            <div class="col-12">
                                <h6>Yahh kuota downloadmu untuk hari ini sudah habis, kamu bisa meningkatkan kuota download
                                    kamu dengan mengumpulkan poin prestasi infinite atau memberi donasi dibawah.</h6>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-6">
                                <img class="img-fluid" style="max-width: 100%; width: 300px;"
                                    src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" alt="">
                                <br>
                                <p>Lebih berprestasi bersama INFINITE!</p>
                            </div>
                            <div class="col-6">
                                <p>Bantu INFINITE Beli Galon</p>
                                <img src="{{ asset('admin-panel/assets/images/saweria/saweriaqrcode.png') }}"
                                    alt="">
                                <br>
                                <br>
                                <p>Donasi minimum Rp5.000,00 untuk mendapatkan kuota tambahan sebanyak 8. (Wajib pake
                                    email
                                    @student.uny.ac.id)</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
