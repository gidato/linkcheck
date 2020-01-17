@php $camelKey = Str::camel($filter->key) @endphp
<div class="form-group row">
    <label for="{{ $filter->key }}-max" class="col-md-auto col-form-label text-md-right">{{ __('Maximum depth') }}</label>

    <div class="col-md-4">
        <input id="{{ $filter->key }}-max"
            type="number"
            class="form-control @error('max', $camelKey) is-invalid @enderror"
            name="{{ $filter->key }}[max]"
            v-bind:disabled="!filters.{{ $camelKey }}"
            required
            value="{{ old($filter->key, $filter->parameters)['max'] ?? '' }}">

        @error('max', $camelKey)
            <span class="invalid-feedback" role="alert">
                {{ $message }}
            </span>
        @endif
    </div>
</div>
