<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">Approved Redirects</h5>
            </div>
            @if ($site->approvedRedirects->count() > 0)
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{route('sites.redirects.list', $site)}}" class="btn btn-sm btn-primary"><i class="las la-pen"></i> MANAGE APPROVALS</a>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-md-2">Redirects Approved</dt>
            <dd class="col-md-10">{{ $site->approvedRedirects->count() }}</dd>
        </dl>
    </div>
</div>
