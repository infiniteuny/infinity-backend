@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Kamu ga punya akses untuk mengakses halaman ini!'))
