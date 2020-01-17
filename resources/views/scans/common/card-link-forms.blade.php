@if ($scan)

    @include('common.hidden-form', [
        'id' => 'deletion-form-' . $scan->id ,
        'action' => route('scans.delete', $scan),
        'method' => 'delete'
    ])

    @if ($scan->isComplete())

        @include('common.hidden-form', [
            'id' => 'email-self-form-' . $scan->id ,
            'action' => route('scans.email.self', $scan)
        ])

        @include('common.hidden-form', [
            'id' => 'email-all-form-' . $scan->id ,
            'action' => route('scans.email.all', $scan)
        ])

        @if ($scan->hasLinkErrors() || $scan->hasWarnings())
            @include('common.hidden-form', [
                'id' => 'rescan-errors-form-' . $scan->id ,
                'action' => route('scans.rescan.errors', $scan)
            ])
        @endif

        @if ($scan->hasLinkErrors())
            @include('common.hidden-form', [
                'id' => 'rescan-referring-form-' . $scan->id ,
                'action' => route('scans.rescan.referrers', $scan)
            ])
        @endif

    @endif

@endif
