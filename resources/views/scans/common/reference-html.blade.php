<div class="col-4 col-md-2">
    {{ $reference->getTagAndAttribute() }}
</div>

<div class="col-4 col-md-2 text-md-center text-orange">
    @if ($reference->opensInNewWindow())
        <small>OPENS IN NEW WINDOW</small>
    @endif
</div>
