<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">Delete Site</h5>
            </div>
        </div>
    </div>
    <div class="card-body text-center">
        <p>
            Delete the site and all scans.
            <strong>This cannot be undone</strong>
        </p>

        <a href="{{ route('sites.delete.request', $site) }}" class="btn btn-danger">
            <i class="las la-trash-alt"></i>
            DELETE
        </a>
    </div>
</div>
