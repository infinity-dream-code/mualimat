@extends('layouts.admin_new')

@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        {{($dataTitle??($mainTitle??($title??'')))}}
    </h3>
    <ul class="breadcrumb breadcrumb-style2">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.index') }}" class="text-hover-primary">Beranda</a>
        </li>
        <li class="breadcrumb-item">{{ $title ?? 'Wakaf' }}</li>
        <li class="breadcrumb-item">{{ $mainTitle ?? 'Wakaf' }}</li>
        <li class="breadcrumb-item active">{{ $dataTitle ?? 'Rekap Wakaf' }}</li>
    </ul>

    <div class="card">
        <div class="card-body text-center py-5">
            <h5 class="mb-2">Rekap Wakaf</h5>
            <p class="text-muted mb-0">Halaman rekap wakaf siap dipakai. Detail rekap bisa ditambahkan berikutnya.</p>
        </div>
    </div>
@endsection
