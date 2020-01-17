@extends('layouts.app')

@section('title', 'Add Owner')

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('owners.store', $site) }}" method="POST">
                @csrf
                @include('owners.form-body')
                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">ADD</button>
                    <a href="{{route('sites.settings', $site)}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>


@endsection
