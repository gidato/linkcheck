<div class="card-body">
    <div class="form-group row">
        <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

        <div class="col-md-6">
            <input id="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name"
                required
                value="{{ old('name', $owner->name)}}">

            @error('name')
                <span class="invalid-feedback" role="alert">
                    {{ $errors->first('name') }}
                </span>
            @enderror
        </div>
    </div>
    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}</label>

        <div class="col-md-6">
            <input id="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                required
                value="{{ old('email', $owner->email) }}">

            @error('email')
                <span class="invalid-feedback" role="alert">
                    {{ $errors->first('email') }}
                </span>
            @enderror
        </div>
    </div>
</div>
