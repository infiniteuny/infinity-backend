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
                        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Pengajuan Dana</a></li>
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
        var member = {!! json_encode($data['fund']->team_members_count) !!};

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
                        <button class="btn btn-danger" type="button"
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
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-xl-12">
                    <form class="card"
                        action="{{ route('student.fund-application.update', Crypt::encryptString($data['fund']->id)) }}"
                        method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="card-header">
                            <h4 class="card-title mb-0">Edit Pengajuan Dana {{ $data['fund']->competition_name }}</h4>
                            <div class="card-options"><a class="card-options-collapse" href="#"
                                    data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a
                                    class="card-options-remove" href="#" data-bs-toggle="card-remove"><i
                                        class="fe fe-x"></i></a></div>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_name">Nama Kompetisi</label>
                                        <input type="text" class="form-control" id="competition_name"
                                            name="competition_name" placeholder="Masukkan nama kompetisi"
                                            value="{{ $data['fund']->competition_name }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_url">Website Penyelenggara Kompetisi</label>
                                        <input type="text" class="form-control" id="competition_url"
                                            name="competition_url"
                                            placeholder="Masukkan URL website penyelenggara kompetisi"
                                            value="{{ $data['fund']->competition_url }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_date">Tanggal Kompetisi</label>
                                        <input type="date" class="form-control" id="competition_date"
                                            name="competition_date" placeholder="Masukkan tanggal kompetisi"
                                            value="{{ $data['fund']->competition_date }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="competition_branch">Cabang Lomba</label>
                                        <input type="text" class="form-control" id="competition_branch"
                                            name="competition_branch" placeholder="Masukkan nama cabang lomba"
                                            value="{{ $data['fund']->competition_branch }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="team_name">Nama Tim</label>
                                        <input type="text" class="form-control" id="team_name" name="team_name"
                                            placeholder="Masukkan nama tim" value="{{ $data['fund']->team_name }}"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div id="leader-input" class="row g-3" style="margin-bottom: 1rem !important;">
                                <div class="col-md-4">
                                    <label for="team_leader_name">Nama Ketua Tim</label>
                                    <input type="text" class="form-control" id="team_leader_name"
                                        name="team_leader[name]" placeholder="Masukkan nama ketua tim"
                                        value="{{ $data['fund']->team_leader['name'] }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="team_leader_student_id">NIM Ketua Tim</label>
                                    <input type="number" class="form-control" id="team_leader_student_id"
                                        name="team_leader[student_id]" placeholder="Masukkan nim ketua tim"
                                        value="{{ $data['fund']->team_leader['student_id'] }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="team_leader_phone">Nomor Hp Ketua Tim</label>
                                    <div class="input-group"><span class="input-group-text">+62</span>
                                        <input type="number" class="form-control" id="team_leader[phone]"
                                            name="team_leader[phone]" placeholder="Masukkan nomor hp ketua tim"
                                            value="{{ $data['fund']->team_leader['phone'] }}" required>
                                    </div>
                                </div>
                            </div>

                            @if ($data['fund']->team_members)
                                @foreach ($data['fund']->team_members as $member)
                                    <div id="member-{{ $loop->index }}" class="row g-3"
                                        style="margin-bottom: 1rem !important;">
                                        <div class="col-md-4">
                                            <label for="team_member[name][{{ $loop->index }}][]">Nama Anggota</label>
                                            <input type="text" class="form-control"
                                                id="team_member[name][{{ $loop->index }}]"
                                                name="team_member[name][{{ $loop->index }}][]"
                                                placeholder="Masukkan nama anggota tim" value="{{ $member->name }}"
                                                required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="team_member[student_id][{{ $loop->index }}][]">NIM
                                                Anggota</label>
                                            <input type="text" class="form-control"
                                                id="team_member[student_id][{{ $loop->index }}]"
                                                name="team_member[student_id][{{ $loop->index }}][]"
                                                placeholder="Masukkan nim anggota tim" value="{{ $member->student_id }}"
                                                required>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="team_member[phone][{{ $loop->index }}]">Nomor Hp
                                                Anggota</label>
                                            <div class="input-group"><span class="input-group-text">+62</span>
                                                <input type="number" class="form-control"
                                                    id="team_member[phone][{{ $loop->index }}]"
                                                    name="team_member[phone][{{ $loop->index }}][]"
                                                    placeholder="Masukkan nomor hp anggota tim"
                                                    value="{{ $member->phone }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label>Hapus</label>
                                            <button class="btn btn-danger btn-block" type="button"
                                                onclick="removeMember({{ $loop->index }})"><i
                                                    class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

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
                                            value="">
                                        <small class="text-danger">*Pdf maksimal 2MB</small>
                                        <object data="{{ $data['fund']->student_id_card }}" type="application/pdf"
                                            width="100%" height="500px">
                                            <p>Duhh! Browser kamu ga support view pdf, download aja filenya <a
                                                    href="{{ $data['fund']->student_id_card }}">disini</a>.</p>
                                        </object>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="loa">Letter Of Acceptance (Bukti Pengumuman Finalis)</label>
                                        <input type="file" class="form-control" id="loa" name="loa"
                                            placeholder="Unggah Letter Of Acceptance" value="">
                                        <small class="text-danger">*Pdf maksimal 2MB</small>
                                        <object data="{{ $data['fund']->letter_of_acceptance }}" type="application/pdf"
                                            width="100%" height="500px">
                                            <p>Duhh! Browser kamu ga support view pdf, download aja filenya <a
                                                    href="{{ $data['fund']->letter_of_acceptance }}">disini</a>.</p>
                                        </object>
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
                                            placeholder="Unggah Rencana Anggaran Biaya" value="">
                                        <small class="text-danger">*Pdf maksimal 2MB</small>
                                        <object data="{{ $data['fund']->budget_plan }}" type="application/pdf"
                                            width="100%" height="500px">
                                            <p>Duhh! Browser kamu ga support view pdf, download aja filenya <a
                                                    href="{{ $data['fund']->budget_plan }}">disini</a>.</p>
                                        </object>
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
