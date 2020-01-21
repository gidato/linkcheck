@extends('layouts.pdf')

@php
    $colors = ['errors' => 'danger', 'warnings' => 'danger', 'queued' => 'orange', 'processing' => 'orange', 'success' => 'success', 'warnings' => 'warning'];
    $site = $scan->site;
    $status = $scan->status;
    $color = $colors[$status];
@endphp

@section('content')
    <h1>Scan Detail</h1>
    <div class="header-box">
        <h2>{{ $site->url }}</h2>
        <p>Date: {{$scan->updated_at}}</p>
        <p>Status: <span class="badge {{ $color}}">{{ $status }}</span></p>
    </div>
    @include('scans.pdf.show', ['scan' => $scan])
@endsection
