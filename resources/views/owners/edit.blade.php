@extends('layouts.app')

@section('title', 'Update Owner')

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('owners.update', $owner) }}" method="POST">
                @csrf
                @method('put')
                @include('owners.form-body')
                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">UPDATE</button>
                    <a href="{{route('sites.settings', $site)}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
@endsection
