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
                        <li class="breadcrumb-item active">Member</li>
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
    <script src="{{ asset('admin-panel/assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            var table = $('.member_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100,
                ajax: "{{ route('admin.member.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'nim',
                        name: 'nim'
                    },
                    {
                        data: 'prodi',
                        name: 'prodi'
                    },
                    {
                        data: 'fakultas',
                        name: 'fakultas'
                    },
                    {
                        data: 'jumlah_prestasi',
                        name: 'jumlah_prestasi'
                    },
                    {
                        data: 'tanggal_aktif',
                        name: 'tanggal_aktif'
                    },
                    {
                        data: 'tanggal_selesai',
                        name: 'tanggal_selesai'
                    },
                    {
                        data: 'alb',
                        name: 'alb'
                    },
                    {
                        data: null,
                        name: 'status',
                        render: function(data) {
                            if (data.status == true) {
                                return '<span class="badge badge-success">Aktif</span>';
                            } else {
                                return '<span class="badge badge-danger">Tidak Aktif</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        render: function(data) {
                            return `
                            <form action="{{ url('admin/member') }}/${data.id}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="btn-group btn-group-pill" role="group" aria-label="Manage Button">
                                    <button type="button" onclick="goTo('${data.id}')" class="btn btn-primary btn-sm btn-edit-member"><i class="fa fa-edit"></i></button>
                                    <button type="submit" class="btn btn-danger btn-sm btn-detele-member"><i class="fa fa-trash"></i></button>
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
            var url = "{{ route('admin.member.edit', ':id') }}";
            url = url.replace(':id', id);
            window.location.href = url;
        }

        $(document.body).on('click', '.btn-detele-member', function(event) {
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
    <script>
        $(document).ready(function() {
            $(".select-faculty").select2({
                dropdownParent: $('#addMemberModal'),
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
                disabled: true,
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
                        dropdownParent: $('#addMemberModal'),
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
    <script>
        $('#importMemberModal form').submit(function(e) {
            e.preventDefault();

            const file = $(this).find('#upload-members')[0];
            if (!file.files.length) {
                return
            }

            button = $(this).find('button[type="submit"]')
            spinner = $(button).find('.spinner-border')
            button.attr('disabled', true)
            spinner.removeClass('d-none')

            function enableButton() {
                button.removeAttr('disabled')
                spinner.addClass('d-none')
            }

            const reader = new FileReader();
            reader.readAsArrayBuffer(file.files[0]);
            reader.onload = function (e) {
                try {
                    const data = new Uint8Array(reader.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    const sheet = workbook.Sheets[workbook.SheetNames[0]]

                    // source https://github.com/SheetJS/sheetjs/issues/270
                    const result = [];
                    let rowNum = 0;
                    let colNum = 0;
                    const range = XLSX.utils.decode_range(sheet['!ref']);
                    for(rowNum = range.s.r; rowNum <= range.e.r; rowNum++) {
                        const row = []
                        for(colNum = range.s.c; colNum <= range.e.c; colNum++) {
                            const nextCell = sheet[XLSX.utils.encode_cell({ r: rowNum, c: colNum })];
                            if (typeof nextCell === 'undefined') {
                                row.push(void 0)
                            } else {
                                row.push(nextCell.w)
                            }
                        }
                        if ([0, 1].every((v) => Boolean(row[v]))) {
                            result.push(row)
                        }
                    }

                    if ((result[0][0] || '').toLowerCase() == 'nama') {
                        result.shift()
                    }

                    const members = []
                    result.forEach((item, i) => {
                        members.push({
                            name: item[0],
                            student_id: item[1],
                            start_date: moment(new Date(item[2])).format('YYYY-MM-DD'),
                            end_date: moment(new Date(item[3])).format('YYYY-MM-DD'),
                            status: item[4] === 'Aktif' ? 1 : 0,
                            is_extraordinary: item[5] === 'Ya' ? 1 : 0,
                        })
                    })

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.member.storeBulk') }}",
                        data: JSON.stringify({
                            members
                        }),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        contentType: 'application/json',
                        success: function(res) {
                            enableButton()
                            flasher.success(res.message)
                            $('#importMemberModal').modal('hide')
                            setTimeout(() => {
                                location.reload()
                            }, 1000)
                        },
                        error: function(err) {
                            enableButton()
                            flasher.error(err.responseJSON.message)
                        }
                    })
                } catch (e) {
                    enableButton()
                }
            }
        })
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Data Anggota INFINITE UNY</h5><span>Data anggota aktif maupun tidak aktif INFINITE UNY dari
                        tahun ke
                        tahun.</span>
                    <div class="card-header-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-original-title="addMember"
                                data-bs-target="#addMemberModal">Tambah
                            Anggota</button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-original-title="addMember"
                                data-bs-target="#importMemberModal">Impor
                            Anggota</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="stripe hover member_table" id="member_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Prodi</th>
                                    <th>Fakultas</th>
                                    <th>Jumlah Prestasi</th>
                                    <th>Tanggal Aktif</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Anggota Luar Biasa</th>
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

    <!-- Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Tambah Member</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.member.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="name"
                                           placeholder="Masukkan nama" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="nim">NIM</label>
                                    <input type="number" class="form-control" id="nim" name="student_id"
                                           placeholder="Masukkan NIM" required>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2" style="margin-bottom: 1rem !important;">
                            <div class="col-md-6">
                                <label for="fakultas">Fakultas</label>
                                <select class="select-faculty col-sm-12 btn-square" name="faculty">
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="prodi">Prodi</label>
                                <select class="select-program-study col-sm-12 btn-square" name="programStudy" required>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="tanggal_aktif">Tanggal Aktif</label>
                                    <input type="date" class="form-control" id="tanggal_aktif" name="date_start"
                                           placeholder="Masukkan Tanggal Aktif" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="date_end"
                                           placeholder="Masukkan Tanggal Selesai" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="anggota_luar_biasa">Anggota Luar Biasa</label>
                                    <select class="form-select digits" id="anggota_luar_biasa" name="is_extraordinary"
                                            required>
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="status">Status</label>
                                    <select class="form-select digits" id="status" name="status" required>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary" type="submit">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Impor -->
    <div class="modal fade" id="importMemberModal" tabindex="-1" role="dialog" aria-labelledby="importMemberModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importMemberModalLabel">Impor Member</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form>
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label>1. Unduh Template</label>
                                    <div>
                                        <a href="/" class="btn btn-primary">Unduh</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label>2. Isi Data</label>
                                    <div class="table-responsive">
                                        <table class="stripe hover">
                                            <tbody>
                                                <tr>
                                                    <td>Nama</td>
                                                    <td>Wahyudi</td>
                                                </tr>
                                                <tr>
                                                    <td>NIM</td>
                                                    <td>20537144011</td>
                                                </tr>
                                                <tr>
                                                    <td>Tanggal Mulai</td>
                                                    <td><span class="font-weight-bold">YYYY-MM-DD</span> atau <span class="font-weight-bold">DD/MM/YYYY</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Tanggal Selesai</td>
                                                    <td><span class="font-weight-bold">YYYY-MM-DD</span> atau <span class="font-weight-bold">DD/MM/YYYY</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Status</td>
                                                    <td><span class="font-weight-bold">Aktif</span> atau <span class="font-weight-bold">Tidak Aktif</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Anggota Luar Biasa</td>
                                                    <td><span class="font-weight-bold">Ya</span> atau kosong</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="upload-members">3. Unggah File</label>
                                    <input class="form-control" id="upload-members" type="file" name="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary" type="submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Impor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
