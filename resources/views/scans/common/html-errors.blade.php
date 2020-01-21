@unless($pages->count() > 0)
    <p>None found.</p>
@else
    @php
        $colors = [
            'error' => 'orange',
            'warning' => 'warning',
            'fatal' => 'danger',
            'unknown' => 'secondary'
        ];
    @endphp
    <div class="mx-3 row border-bottom font-weight-bold d-none d-md-flex py-2">
        <div class="col-md-7">URL</div>
        <div class="col-md-1 text-center">DEPTH</div>
        <div class="col-md-1 text-center">METHOD</div>
        <div class="col-md-3 text-center">TYPE</div>
    </div>

    @foreach ($pages as $page)
        <div class="mx-3 row  border-top">
            <div class="col-md-7 py-2">
                <button class="dropdown collapsed btn btn-sq-xs btn-outline-secondary" data-toggle="collapse" data-target="#errors-{{ $page->id }}">
                    <i class="las la-angle-down"></i>
                </button>
                @if ($page->isError())
                    <span class="ml-2 badge badge-pill badge-danger">
                        {{ $page->status_code }}
                    </span>
                @elseif ($page->isRedirect())
                    <span class="ml-2 badge badge-pill  badge-orange">
                        {{ $page->status_code }}
                    </span>
                @else
                    <span class="ml-2 badge badge-pill badge-success">
                        {{ $page->status_code }}
                    </span>
                @endif
                <a href="{{ $page->url }}" target="_blank">{{ $page->getShortUrl() }}</a> <i class="las la-external-link-alt"></i>
            </div>
            <div class="col-2 col-md-1 text-center">{{ $page->depth }}</div>
            <div class="col-4 col-md-1 text-center">{{ Str::upper($page->method) }}</div>
            <div class="col-6 col-md-3 text-center">
                @if (!$page->isError() && !$page->isRedirect())
                    {{ $page->mime_type }}
                @else
                    {{ $page->getStatusText() }}
                @endif
            </div>
        </div>
        <div id="errors-{{ $page->id }}" class="mx-3 row collapse">
            <div class="col">
                @foreach (json_decode($page->html_errors,true) as $error)
                    <div class="row">
                        <div class="col-9">
                            <span class="ml-2 badge badge-pill badge-{{ $colors[$error['level']] }}">
                                {{ $error['level'] }}
                            </span>
                            {{ $error['message'] }}
                        </div>
                        <div class="col-3">
                            Line {{ $error['line'] }}, Column {{ $error['column'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3"></div>
    @endforeach
@endunless
