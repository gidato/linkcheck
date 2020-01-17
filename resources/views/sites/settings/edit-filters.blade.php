@extends('layouts.app')

@section('title', 'Edit Filters')

@section('content')
    <div class="container">
        <div class="card mb-3">
            <form action="{{route('filters.update', $site) }}" method="POST">
                @csrf
                @method('patch')

                <div class="card-header">
                    <h5>{{ $site->url }}</h5>
                </div>

                <div class="card-body">

                    @foreach($filters as $filter)
                        @php $camelKey = Str::camel($filter->key) @endphp
                        <div
                            class="row mt-2 py-2 border-top"
                            v-bind:class="{ 'bg-light': !filters.{{ $camelKey }}  }"
                        >
                            <div class="col-auto">
                                <div class="custom-control custom-checkbox">
                                    <input
                                        id = "active-{{ $filter->key }}"
                                        class="custom-control-input"
                                        type="checkbox"
                                        name="on[{{ $filter->key }}]"
                                        value="{{ $filter->key }}"
                                        @if (!empty(old("on", $activated)[$filter->key])) checked @endif
                                        v-model="filters.{{ $camelKey }}"
                                    >
                                    <label class="custom-control-label" for="active-{{ $filter->key }}">
                                        <strong>{{ $filter->name }}</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div
                            class="row mb-2 py-2 border-bottom"
                            v-bind:class="{ 'bg-light text-very-light': !filters.{{ $camelKey }}  }"
                        >
                            <div class="ml-4 col">
                                @include(
                                    'sites.settings.filters.edit-'.$filter->key,
                                    [
                                        'filter' => $filter
                                    ]
                                )
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-footer text-center">
                    <button type="submit" name="go" class="btn btn-primary">UPDATE</button>
                    <a href="{{route('sites.settings', $site)}}" class="btn btn-secondary">CANCEL</a>
                </div>
            </form>
        </div>
    </div>

    <script type="application/javascript">
        window.filtersActive = {
            @foreach($filters as $filter)
                {{ Str::camel($filter->key) }}: {{ empty(old("on", $activated)[$filter->key]) ? 'false' : 'true' }},
            @endforeach
        };
    </script>

@endsection
