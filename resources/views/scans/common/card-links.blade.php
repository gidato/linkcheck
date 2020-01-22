@if ($scan)
    <div class="card-footer">

        <a href="#" class="card-link"
            v-on:click="form_link_confirmation($event,'deletion-form-{{ $scan->id }}')" >
            Delete
        </a>

        @if (!($excludeView ?? false))
            <a href="{{ route('scans.show', $scan) }}" class="card-link">View Detail</a>
        @endif

        @if ($scan->isComplete())
            <a href="#" class="card-link"
                v-on:click="form_submit($event,'email-self-form-{{ $scan->id }}')" >
                Email User
            </a>

            <a href="#" class="card-link"
                v-on:click="form_submit($event,'email-all-form-{{ $scan->id }}')" >
                Email Owners
            </a>

            @if ($scan->hasLinkErrors() || $scan->hasWarnings())
                <a href="#" class="card-link"
                    v-on:click="form_submit($event,'rescan-errors-form-{{ $scan->id }}')">
                    Rescan error pages
                </a>
            @endif
            @if ($scan->hasLinkErrors())
                <a href="#" class="card-link"
                    v-on:click="form_submit($event,'rescan-referring-form-{{ $scan->id }}')">
                    Rescan referring pages
                </a>
            @endif
        @endif
    </div>
@endif
