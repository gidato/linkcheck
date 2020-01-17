@if ($site && !$site->validated)
    <div class="alert alert-danger">
        This site has not been validated.  Please see
        <a href="{{ route('sites.settings', $site) }}">site settings</a> to validate.
    </div>
@endif
