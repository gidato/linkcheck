<div class="card-body">
    @if ($scan)
        @include('sites.common.not-verified', ['site' => $scan->site])
        @if (!empty($scan->message))
            <div class="alert alert-danger">
                <strong>Error Message:</strong> {{ $scan->message }}
            </div>
        @endif
        @if ($scan->hasLinkErrors())
            <p>
                The following link errors were found:
            </p>
            <ul>
                @foreach ($scan->getLinkErrors() as $error)
                    <li>
                        <strong>{{ $error->status_code }}</strong>:
                        <a href="{{ $error->url }}" target="_blank">{{ $error->url }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if ($scan->hasHtmlErrors())
            <p>
                There are <strong>{{ $scan->getHtmlErrors()->count() }}</strong> HTML pages with errors.
            </p>
        @endif
        @if ($scan->hasUnapprovedRedirects())
            <p>
                There are <strong>{{ $scan->getUnapprovedRedirects()->count() }}</strong> links that have been redirected to other pages and have not been approved as appropriate redirects.
            </p>
        @endif

        @if (!$scan->hasLinkErrors() && !$scan->hasWarnings())
            <em>-- All links found --</em>
        @endif
    @else
        @include('sites.common.not-verified', ['site' => $site])
        <p>
            <em>-- No scans yet -- </em>
        </p>
    @endif
</div>
