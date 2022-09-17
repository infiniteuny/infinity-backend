@extends('layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="{{ asset('assets/images/download-bgshape.png') }}" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>Cek Keanggotaan</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content">

        <!-- Contact Area -->
        <div id="tm-area-contact" class="tm-contact-area tm-section tm-padding-section bg-white">
            <div class="tm-contact-bgshape">
                <img src="{{ asset('assets/images/bg-shape-contact.png') }}" alt="contact shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="tm-sectiontitle text-center">
                        <h2>Masukkan NIM</h2>
                        <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        @if (isset($member))
                            @if (isset($member->is_extraordinary))
                                @if ($member->is_extraordinary)
                                    <p>Beliau ini bukan sembarang beliau</p>
                                    <h4>Anggota Luar Biasa</h4>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <form id="tm-contactform" action="{{ route('member.check') }}" method="post"
                            class="tm-form tm-contact-form">
                            <div class="tm-form-inner">
                                @csrf
                                @if (isset($member))
                                    <div class="tm-form-field">
                                        <input type="text" name="student_id" placeholder="NIM"
                                            value="{{ $member ? $member->student_id : $member->student_id }}"
                                            required="required">
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field tm-form-fieldhalf">
                                        <input type="text" name="name" placeholder="Nama (Terisi Otomatis)"
                                            value="{{ isset($member->name) ? $member->name : 'Bukan Anggota Infinite' }}"
                                            readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field tm-form-fieldhalf">
                                        <input type="text" name="year" placeholder="Angkatan (Terisi Otomatis)"
                                            value="{{ isset($member->programStudies) ? $member->programStudies->name . ' ' . $member->year : 'Bukan Anggota Infinite' }}"
                                            readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field">
                                        <input type="text" name="period" placeholder="Periode Anggota (Terisi Otomatis)"
                                            value="{{ isset($member->start_date) ? 'Anggota infinite' . ($member->status ? ' sejak ' : ' dari ') . Carbon\Carbon::parse($member->start_date)->format('d/m/Y') . ' - ' . (isset($member->end_date) ? $member->end_date : '') : 'Bukan Anggota Infinite' }}"
                                            readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field">
                                        <input type="text" name="status"
                                            placeholder="Status Keanggotaan (Terisi Otomatis)"
                                            value="{{ isset($member->status) ? ($member->status ? 'Aktif' : 'Non-aktif') : 'Bukan Anggota Infinite' }}"
                                            readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                @else
                                    <div class="tm-form-field">
                                        <input type="text" name="student_id" placeholder="NIM" value=""
                                            required="required">
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field tm-form-fieldhalf">
                                        <input type="text" name="name" placeholder="Nama (Terisi Otomatis)"
                                            value="" readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field tm-form-fieldhalf">
                                        <input type="text" name="year" placeholder="Angkatan (Terisi Otomatis)"
                                            value="" readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field">
                                        <input type="text" name="period" placeholder="Periode Anggota (Terisi Otomatis)"
                                            value="" readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                    <div class="tm-form-field">
                                        <input type="text" name="status"
                                            placeholder="Status Keanggotaan (Terisi Otomatis)" value="" readonly>
                                        <span class="tm-form-animatedline"></span>
                                    </div>
                                @endif
                                <div class="tm-form-field">
                                    <button type="submit" class="tm-button"><span>Cek</span></button>
                                </div>
                            </div>
                        </form>
                        <p class="form-messages"></p>
                    </div>
                </div>
            </div>
        </div>
        <!--// Contact Area -->

    </main>
    <!--// Page Content -->
@endsection
