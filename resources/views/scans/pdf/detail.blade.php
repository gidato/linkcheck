@unless($pages->count() > 0)
    <p class="small">None found.</p>
@else
    <table>
        <tr>
            <th>URL</th>
            <th class="center">DEPTH</div>
            <th class="center">METHOD</th>
            <th class="center">TYPE</th>
        </tr>

        @foreach ($pages as $page)
            <tr @if (!empty($references)) class="border-top border-light" @endif>
                <td>
                    @if (!$page->checked)
                        <span class="pill warning">&nbsp;</span>
                    @elseif ($page->isError())
                        <span class="pill danger">{{ $page->status_code }}</span>
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
                    @if (!empty($page->exception))
                        {{ $page->exception }}
                    @elseif (!$page->isError() && !$page->isRedirect())
                        {{ $page->mime_type }}
                    @elseif ($page->isRedirect())
                        Redirected - {{ $page->getStatusText() }}
                    @else
                        {{ $page->getStatusText() }}
                    @endif
                </td>
            </tr>
            @if (!empty($referernces))
                @foreach ($page->referredInPages as $referrer)
                    <tr>
                        <td class="indent">
                            {{ $referrer->getShortUrl() }}
                        </td>
                        <td class="small">
                            @if ($referrer->pivot->times == 2)
                                <span class="pill blue">TWICE</span>
                            @elseif ($referrer->pivot->times > 1)
                                <span class="pill blue">{{ $referrer->pivot->times }} TIMES</span>

                            @endif
                        </td>
                        @include(
                            'scans.pdf.reference-' . $referrer->pivot->type,
                            [
                                'reference' => $referrer->pivot->getReference($referrer->method)
                            ]
                        )
                    </tr>
                @endforeach
            @endif
        @endforeach
    </table>
@endunless
