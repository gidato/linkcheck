<h3
    @if ($scan->hasLinkErrors() || $scan->hasWarnings())
        class="danger"
    @endif
>Errors &amp; Warnings</h3>
<h4>Link Errors</h4>
@include('scans.pdf.detail', ['references' =>true, 'pages' => $scan->getLinkErrors()->sortBy('url')])

<h4>HTML Errors</h4>
@include('scans.pdf.html-errors', ['pages' => $scan->getHtmlErrors()->sortBy('url')])

<h4>Unapproved Page Redirects</h4>
@include('scans.pdf.detail', ['references' =>true, 'pages' => $scan->getUnapprovedRedirects()->sortBy('url')])

<h3>Successfully Checked Links</h3>
<h4>Internal HTML pages</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', false)
                    ->where('status_code', 200)
                    ->where('mime_type', 'text/html')
                    ->sortBy('url')
])

<h4>Internal Images</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', false)
                    ->where('status_code', 200)
                    ->filter(function($item) {
                        return 'image/' == substr($item->mime_type,0,6);
                    })
                    ->sortBy('url')
])

<h4>Other Internal Resources</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', false)
                    ->where('status_code', 200)
                    ->filter(function($item) {
                        return 'image/' !== substr($item->mime_type,0,6)
                            && $item->mime_type != 'text/html';
                    })
                    ->sortBy('url')
])

<h4>Approved Redirects on Internal Pages</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->getApprovedRedirects()
                    ->where('is_external', false)
                    ->sortBy('url')
])

<h4>External HTML pages</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', true)
                    ->where('status_code', 200)
                    ->where('mime_type', 'text/html')
                    ->sortBy('url')
])

<h4>External Images</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', true)
                    ->where('status_code', 200)
                    ->filter(function($item) {
                        return 'image/' == substr($item->mime_type,0,6);
                    })
                    ->sortBy('url')
])

<h4>Other External Resources</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->pages
                    ->where('is_external', true)
                    ->where('status_code', 200)
                    ->filter(function($item) {
                        return 'image/' !== substr($item->mime_type,0,6)
                            && $item->mime_type != 'text/html';
                    })
                    ->sortBy('url')
])

<h4>Approved Redirects on External Pages</h4>
@include('scans.pdf.detail', [
    'references' =>false,
    'pages' => $scan->getApprovedRedirects()
                    ->where('is_external', true)
                    ->sortBy('url')
])


@if ($scan->pages->where('checked', false)->count() > 0)
    <h3>Links Not Checked</h3>
    <h4>Internal</h4>
    @include('scans.pdf.detail', [
        'references' =>false,
        'pages' => $scan->pages->where('checked', false)->where('is_external', false)->sortBy('url')
    ])

    <h4>External</h4>
    @include('scans.pdf.detail', [
        'references' =>false,
        'pages' => $scan->pages->where('checked', false)->where('is_external', true)->sortBy('url')
    ])
@endif
