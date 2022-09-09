@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="assets/images/download-bgshape.png" alt="bg shape">
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
                <img src="assets/images/bg-shape-contact.png" alt="contact shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="tm-sectiontitle text-center">
                        <h2>Masukkan NIM</h2>
                        <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <form id="tm-contactform" action="#" method="post" class="tm-form tm-contact-form">
                            <div class="tm-form-inner">
                                <div class="tm-form-field">
                                    <input type="text" name="student_id" placeholder="NIM" required="required">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="name" placeholder="Nama (Terisi Otomatis)" readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="year" placeholder="Angkatan (Terisi Otomatis)" readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <input type="text" name="study_program" placeholder="Program Studi (Terisi Otomatis)"
                                        readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="start_year" placeholder="Tahun Aktif (Terisi Otomatis)"
                                        readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="end_year" placeholder="Tahun Non-Aktif (Terisi Otomatis)"
                                        readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <input type="text" name="status" placeholder="Status Keanggotaan (Terisi Otomatis)"
                                        readonly>
                                    <span class="tm-form-animatedline"></span>
                                </div>
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
