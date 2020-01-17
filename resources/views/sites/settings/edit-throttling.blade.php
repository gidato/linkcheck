@extends('layouts.app')

@section('title', 'Edit Throttling')

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('throttling.update', $site) }}" method="POST">
                @csrf
                @method('patch')

                <div class="card-header">
                    <h5>{{ $site->url }}</h5>
                </div>

                <div class="card-body">
                    <div class="form-group row">
                        <label for="internal" class="col-md-4 col-form-label text-md-right">{{ __('Delay for Internal pages') }}</label>

                        <div class="col-md-4">
                            <input id="internal"
                                type="number"
                                class="form-control @error('internal') is-invalid @enderror"
                                name="internal"
                                value="{{ old('internal', $site->throttle->internal)}}">
                            <small class="form-text text-muted">Whole number of seconds, or empty to use application default</small>

                            @error('internal')
                                <span class="invalid-feedback" role="alert">
                                    {{ $errors->first('internal') }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="external" class="col-md-4 col-form-label text-md-right">{{ __('Delay for External pages') }}</label>

                        <div class="col-md-4">
                            <input id="external"
                                type="number"
                                class="form-control @error('external') is-invalid @enderror"
                                name="external"
                                value="{{ old('external', $site->throttle->external)}}">
                            <small class="form-text text-muted">Whole number of seconds, or empty to use application default</small>

                            @error('external')
                                <span class="invalid-feedback" role="alert">
                                    {{ $errors->first('external') }}
                                </span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">UPDATE</button>
                    <a href="{{route('sites.settings', $site)}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
@endsection
