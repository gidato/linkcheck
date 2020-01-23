@unless($pages->count() > 0)
    <p>None found.</p>
@else
    <div class="mx-3 row border-bottom font-weight-bold d-none d-md-flex py-2">
        <div class="col-md-7">URL</div>
        <div class="col-md-1 text-center">DEPTH</div>
        <div class="col-md-1 text-center">METHOD</div>
        <div class="col-md-3 text-center">TYPE</div>
    </div>

    @foreach ($pages as $page)
        <div class="mx-3 row  border-top">
            <div class="col-md-6 py-2">
                <button class="dropdown collapsed btn btn-sq-xs btn-outline-secondary" data-toggle="collapse" data-target="#references-{{ $page->id }}">
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
                @if ($page->isRedirect())
                    redirected to {{ $page->getShortRedirectUrl() }}
                @endif
            </div>
            <div class="col-2">
                @if ($page->isRedirect())
                    @if (!$page->redirect_approved)
                        <form class="d-inline-block" action="{{route('sites.redirects.approve', $site) }}" method="POST">
                            @csrf
                            <input type="hidden" name="from_url" value="{{ $page->url }}">
                            <input type="hidden" name="to_url" value="{{ $page->redirect }}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="las la-check"></i>
                                <span class="d-none d-md-inline-block">APPROVE</span>
                            </button>
                        </form>
                    @endif
                @endif
            </div>
            <div class="col-2 col-md-1 text-center">{{ $page->depth }}</div>
            <div class="col-2 col-md-1 text-center">{{ Str::upper($page->method) }}</div>
            <div class="col-6 col-md-3 text-center">
                @if (!empty($page->exception))
                    {{ $page->exception }}
                @elseif (!$page->isError() && !$page->isRedirect())
                    {{ $page->mime_type }}
                @elseif ($page->isRedirect())
                    Redirected - {{ $page->getStatusText() }}
                @else
                    {{ $page->getStatusText() }}
                @endif
            </div>
        </div>
        <div id="references-{{ $page->id }}" class="mx-3 row collapse">
            <div class="col">
                @foreach ($page->referredInPages as $referrer)
                    <div class="row" >
                        <div class="col-md-7">
                            {{ $referrer->getShortUrl() }}
                        </div>
                        <div class="col-4 col-md-1 text-md-center small">
                            @if ($referrer->pivot->times == 2)
                                <span class="ml-2 badge badge-pill badge-primary">TWICE</span>
                            @elseif ($referrer->pivot->times > 1)
                                <span class="ml-2 badge badge-pill badge-primary">{{ $referrer->pivot->times }} TIMES</span>

                            @endif
                        </div>
                        @include(
                            'scans.common.reference-' . $referrer->pivot->type,
                            [
                                'reference' => $referrer->pivot->getReference($referrer->method)
                            ]
                        )
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3"></div>
    @endforeach
@endunless
