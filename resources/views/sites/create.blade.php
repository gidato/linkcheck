@extends('layouts.app')

@section('title', 'Add Site')

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('sites.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('Site URL in full') }}</label>

                        <div class="col-md-6">
                            <input id="url"
                                type="text"
                                class="form-control @error('url') is-invalid @enderror"
                                name="url"
                                required
                                value="{{ old('url')}}">

                            @error('url')
                                <span class="invalid-feedback" role="alert">
                                    {{ $errors->first('url') }}
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">ADD</button>
                    <a href="{{route('sites.list')}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>


@endsection
