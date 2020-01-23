@php
    $colors = ['failed' => 'danger', 'errors' => 'danger', 'warnings' => 'danger', 'queued' => 'orange', 'processing' => 'orange', 'success' => 'success', 'warnings' => 'warning'];
    $site = $scan->site;
    $status = $scan->status;
    $color = $colors[$status];
@endphp

        <div class="card mb-3 border-{{ $color }}">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <strong>Summary</strong>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">
                    Latest Scan: <span class="badge badge-{{ $color }}">{{ Str::upper($status) }}</span>
                </h5>
                @if ($scan->isComplete())
                    <p>
                        Site: <strong>{{ (string) $site->url }}</strong>
                    </p>

                    <p>
                        Completed at: {{ $scan->updated_at }}
                    </p>
                @endif

                <div class="mb-2">
                    <div class="row">
                        <div class="col-md-2">
                            Pages Checked:
                        </div>
                        <div class="col">
                            {{ $scan->pages->where('checked',true)->count() }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Pages Not Checked:
                        </div>
                        <div class="col">
                            {{ $scan->pages->where('checked',false)->count() }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Messages:
                        </div>
                        <div class="col">
                            @if($scan->message) {{ $scan->message }} @else <em>none</em> @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Pages with errors:
                        </div>
                        <div class="col">
                            {{ $scan->getLinkErrors()->count() }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Pages with HTML Errors:
                        </div>
                        <div class="col">
                            {{ $scan->getHtmlErrors()->count() }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Pages with unapproved redirects:
                        </div>
                        <div class="col">
                            {{ $scan->getUnapprovedRedirects()->count() }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            Max depth checked:
                        </div>
                        <div class="col">
                            {{ $scan->pages->where('checked',true)->max('depth') }}
                        </div>
                    </div>

                </div>


            </div>
            @include('scans.common.card-links', ['scan' => $scan, 'excludeView' => true])
            @include('scans.common.card-link-forms', ['scan' => $scan])
        </div>

        <div class="card mb-3 border-{{ $color }}">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <strong>Detail</strong>
                </div>
            </div>
            <div class="card-body">
                <h4 class="bg-dark text-light py-2 px-1">Errors &amp; Warnings</h4>
                <h5 class="bg-light py-2 px-1">Link Errors</h5>
                @include('scans.common.detail', ['pages' => $scan->getLinkErrors()->sortBy('url')])

                <h5 class="bg-light py-2 px-1">HTML Errors</h5>
                @include('scans.common.html-errors', ['pages' => $scan->getHtmlErrors()->sortBy('url')])

                <h5 class="bg-light py-2 px-1">Unapproved Page Redirects</h5>
                @include('scans.common.detail', ['pages' => $scan->getUnapprovedRedirects()->sortBy('url')])

                <h4 class="bg-dark text-light py-2 px-1">Successfully Checked Links</h4>
                <h5 class="bg-light py-2 px-1">Internal HTML pages</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', false)
                                    ->where('status_code', 200)
                                    ->where('mime_type', 'text/html')
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">Internal Images</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', false)
                                    ->where('status_code', 200)
                                    ->filter(function($item) {
                                        return 'image/' == substr($item->mime_type,0,6);
                                    })
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">Other Internal Resources</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', false)
                                    ->where('status_code', 200)
                                    ->filter(function($item) {
                                        return 'image/' !== substr($item->mime_type,0,6)
                                            && $item->mime_type != 'text/html';
                                    })
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">Approved Redirects on Internal Pages</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->getApprovedRedirects()
                                    ->where('is_external', false)
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">External HTML pages</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', true)
                                    ->where('status_code', 200)
                                    ->where('mime_type', 'text/html')
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">External Images</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', true)
                                    ->where('status_code', 200)
                                    ->filter(function($item) {
                                        return 'image/' == substr($item->mime_type,0,6);
                                    })
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">Other External Resources</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->pages
                                    ->where('is_external', true)
                                    ->where('status_code', 200)
                                    ->filter(function($item) {
                                        return 'image/' !== substr($item->mime_type,0,6)
                                            && $item->mime_type != 'text/html';
                                    })
                                    ->sortBy('url')
                ])

                <h5 class="bg-light py-2 px-1">Approved Redirects on External Pages</h5>
                @include('scans.common.detail', [
                    'pages' => $scan->getApprovedRedirects()
                                    ->where('is_external', true)
                                    ->sortBy('url')
                ])

                @if ($scan->pages->where('checked', false)->count() > 0)
                    <h4 class="bg-dark text-light py-2 px-1">Links Not Checked</h4>
                    <h5 class="bg-light py-2 px-1">Internal</h5>
                    @include('scans.common.detail', [
                        'pages' => $scan->pages->where('checked', false)->where('is_external', false)->sortBy('url')
                    ])

                    <h5 class="bg-light py-2 px-1">External</h5>
                    @include('scans.common.detail', [
                        'pages' => $scan->pages->where('checked', false)->where('is_external', true)->sortBy('url')
                    ])
                @endif
            </div>
        </div>
