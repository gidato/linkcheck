@unless($pages->count() > 0)
    <p class="small">None found.</p>
@else
    @php
        $colors = [
            'error' => 'error',
            'warning' => 'warning',
            'fatal' => 'danger',
            'unknown' => 'gray'
        ];
    @endphp
    <table>
        <tr>
            <th>URL</th>
            <th class="center">DEPTH</div>
            <th class="center">METHOD</th>
            <th class="center">TYPE</th>
        </tr>

        @foreach ($pages as $page)
            <tr class="border-top border-light">
                <td>
                    @if ($page->isError())
                        <span class="pill error">{{ $page->status_code }}</span>
                    @elseif ($page->isRedirect())
                        <span class="pill redirect">{{ $page->status_code }}</span>
                    @else
                        <span class="pill success">{{ $page->status_code }}</span>
                    @endif
                    <a href="{{ $page->url }}">{{ $page->getShortUrl() }}</a>
                    @if ($page->isRedirect())
                        redirected to {{ $page->getShortRedirectUrl() }}
                    @endif
                </td>
                <td class="center">{{ $page->depth }}</td>
                <td class="center">{{ Str::upper($page->method) }}</td>
                <td class="center">
                    @if (!$page->isError() && !$page->isRedirect())
                        {{ $page->mime_type }}
                    @elseif ($page->isRedirect())
                        Redirected - {{ $page->getStatusText() }}
                    @else
                        {{ $page->getStatusText() }}
                    @endif
                </td>
            </tr>
            @foreach (json_decode($page->html_errors,true) as $error)
                <tr>
                    <td colspan="2" class="indent small">
                        <span class="pill {{ $colors[$error['level']] }}">
                            {{ $error['level'] }}
                        </span>
                        {{ $error['message'] }}
                    </td>
                    <td colspan="2" class="small right">
                        Line {{ $error['line'] }}, Column {{ $error['column'] }}
                    </td>
                </tr>
            @endforeach
            <tr><td>&nbsp;</td></tr>
        @endforeach
    </table>
@endunless
