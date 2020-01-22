@extends('layouts.app')

@section('title', 'Settings - ' . $site->url)

@section('content')
    <div class="container">
        @include('sites.settings.owners', ['site' => $site])
        @include('sites.settings.filters', ['site' => $site, 'filter' => $filters])
        @include('sites.settings.approved-redirects', ['site' => $site])
        @include('sites.settings.throttling', ['site' => $site])
        @include('sites.settings.verification-code', ['site' => $site])
        @include('sites.settings.delete', ['site' => $site])
    </div>
@endsection
