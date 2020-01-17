@extends('layouts.app')

@section('title', 'Scan Detail')

@section('content')
    <div class="container">
        @include('scans.common.show', ['scan' => $scan])
    </div>
@endsection
