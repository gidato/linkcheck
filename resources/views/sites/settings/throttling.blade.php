<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">Page Throttling</h5>
            </div>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="{{route('throttling.edit', $site)}}" class="btn btn-sm btn-primary"><i class="las la-pen"></i> EDIT</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-md-2">Internal Pages</dt>
            <dd class="col-md-10">
                @if (null === $site->throttle->internal)
                    default <small>( {{ config('throttle.internal') }} {{ Str::plural('second', config('throttle.internal')) }})</small>
                @else
                    {{ $site->throttle->internal }} {{ Str::plural('second', $site->throttle->internal) }}
                @endif
            </dd>

            <dt class="col-md-2">External Pages</dt>
            <dd class="col-md-10">
                @if (null === $site->throttle->external)
                    default <small>( {{ config('throttle.external') }} {{ Str::plural('second', config('throttle.external')) }})</small>
                @else
                    {{ $site->throttle->external }} {{ Str::plural('second', $site->throttle->external) }}
                @endif
            </dd>
        </dl>
    </div>
</div>
