<form id="{{ $id }}" action="{{ $action }}" method="POST" style="display: none;">
    @if (!empty($method))
        @method($method)
    @endif
    @csrf
</form>
