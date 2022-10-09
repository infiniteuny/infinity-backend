@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Kamu terlalu sering mengakses halaman ini. Coba lagi nanti ya.'))
