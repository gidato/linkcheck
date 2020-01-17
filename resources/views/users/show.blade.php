@extends('layouts.app')

@section('content')
<div class="container-fluid bg-white mb-3 border-bottom">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-2 ">
                    <h1 class="h3">{{ __('Profile') }}</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <a href="{{ route('edit-password') }}" class="btn btn-primary">Change Password</a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <form class="show">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-right">{{ __('Name') }}</label>
            <div class="col-sm-9 mb-3">
                <input type="text" class="form-control" readonly value="{{ $user->name }}">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-right">{{ __('Email') }}</label>
            <div class="col-sm-9 input-group mb-3">
                <input type="text" class="form-control" readonly value="{{ $user->email }}">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-right">{{ __('Password') }}</label>
            <div class="col-sm-9 input-group mb-3">
                <input type="text" class="form-control" readonly value="{{ str_repeat("*", 8) }}">
                <span class="input-group-append">
                    <button type="button" class="btn btn-secondary" href="{{ route('edit-password') }}"><i class="las la-pen"></i></button>
                </span>
            </div>
        </div>
    </form>


</div>
@endsection
