@extends('layouts.app')

@section('title', 'Delete - ' . $site->url)

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('sites.delete', $site) }}" method="POST">
                @csrf
                @method('delete')
                <div class="card-header bg-danger text-light">
                    <h5 class="mb-0 text-center">DELETING SITE</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <p>
                                You are about to delete the site and all of the associated scans.
                            </p>
                            <p>
                                <strong>This cannot be undone.</strong>
                            </p>
                            <p>
                                Please confirm you wish to delete by entering your password.
                            </p>
                            <p>
                                Site: <strong>{{ $site->url }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                        <div class="col-md-4">
                            <input id="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password"
                                required>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    {{ $errors->first('password') }}
                                </span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">DELETE</button>
                    <a href="{{route('sites.settings', $site)}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
@endsection
