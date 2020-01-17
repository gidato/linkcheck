<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="{{route('filters.edit', $site)}}" class="btn btn-sm btn-primary"><i class="las la-pen"></i> EDIT</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <thead class="thead-light">
                <tr>
                    <th class="fit">In Use</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filters as $key => $filter)
                    <tr>
                        <td>
                            @if( $filter->on )
                                <span class="badge badge-success">ON</span>
                            @else
                                <span class="badge badge-danger">OFF</span>
                            @endif
                        </td>
                        <td>{!! $filter->description !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
