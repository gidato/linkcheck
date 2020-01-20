<div style="font-family: arial;font-size:13px;color: #444;">
    <p>
        Latest report for https://www.colintonlettings.co.uk
    </p>

    @if (!empty($scan->message))
        <p style="color:#c00000;font-weight:bold">Error Processing Scan</p>
        <p>{{ $scan->message}}</p>
    @endif

    @if ($scan->hasLinkErrors())
        <p style="color:#c00000;font-weight:bold">LINK ERRORS FOUND</p>
        <table style="margin-left:30px; font-family: arial;font-size:13px;color: #444;">
            @foreach($scan->getLinkErrors() as $page)
                <tr>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->status_code }}
                        {{ $page->getStatusText() }}:
                    </td>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->getShortUrl() }}
                    </td>
                </tr>
                @foreach($page->referredInPages as $i => $source)
                    <tr>
                        <td style="text-align:right">
                            @if ($i)
                                &nbsp;
                            @else
                                referenced in:
                            @endif
                        </td>
                        <td>
                            {{ $source->getShortUrl() }}
                        </td>
                    </tr>
                @endforeach

            @endforeach
        </table>
    @endif

    @if ($scan->hasHtmlErrors())
        <p style="color:#c00000;font-weight:bold">HTML ERRORS FOUND</p>
        <p>The following pages have HTML errors</p>
        <table style="margin-left:30px; font-family: arial;font-size:13px;color: #444;">
            @foreach($scan->getHtmlErrors() as $page)
                <tr>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->getShortUrl() }}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($scan->hasRedirects())
        <p style="color:#c00000;font-weight:bold">PAGE REDIRECTS FOUND</p>
        <table style="margin-left:30px; font-family: arial;font-size:13px;color: #444;">
            @foreach($scan->getRedirects() as $page)
                <tr>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->status_code }}
                        {{ $page->getStatusText() }}:
                    </td>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->getShortUrl() }}
                    </td>
                    <td style="font-weight:bold; padding-top:10px;">
                        {{ $page->getShortRedirectUrl() }}
                    </td>
                </tr>
                @foreach($page->referredInPages as $i => $source)
                    <tr>
                        <td style="text-align:right">
                            @if ($i)
                                &nbsp;
                            @else
                                referenced in:
                            @endif
                        </td>
                        <td>
                            {{ $source->getShortUrl() }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    @endif

    @if (!$scan->hasLinkErrors() && !$scan->hasWarnings())
        <p style="color:#c00000;font-weight:bold">ALL LINKS FOUND</p>
    @endif

    <p>
        For full detail of all pages and resources, see attached PDF.
    </p>

    <p>
        Regards
        <br>
        David
    </p>
</div>
