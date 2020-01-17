<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">
                    Verification Code
                    @if (!$site->validated)
                        <span class="badge badge-danger">NOT VERIFIED</span>
                    @else
                        <span class="badge badge-success">VERIFIED</span>
                    @endif
                </h5>
            </div>
            <div class="btn-toolbar mb-2 mb-md-0">
                <form action="{{ route('verification.refresh', $site)}}" method="POST">
                    @method('PATCH')
                    @csrf
                    <button stype="submit" class="btn btn-sm btn-primary"><i class="las la-redo-alt"></i> REFRESH CODE</button>
                </form>

                @if (!$site->validated)
                    <form action="{{ route('verification.check', $site)}}" method="POST">
                        @method('PATCH')
                        @csrf
                        <button stype="submit" class="btn btn-sm btn-danger ml-2"><i class="las la-broadcast-tower"></i> VERIFY NOW</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <p>
            Place the following json code in a file called <strong>linkcheck_verification.json</strong>
            at the root of your site.
        </p>
        <p>
            If you multiple sections within yours site, and each is checked separately, include all codes in one file.
        </p>

        <pre class="bg-dark text-light p-4"><code>{
    "{{ (string) $site->url }}": "{{ $site->validation_code }}"
}
</code></pre>
    </div>
</div>
