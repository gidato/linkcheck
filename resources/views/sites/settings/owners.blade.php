<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h5 class="mb-0">Owners</h5>
            </div>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="{{route('owners.create', $site)}}" class="btn btn-sm btn-primary"><i class="las la-plus"></i> NEW</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($site->owners as $owner)
                    <tr>
                        <td>{{ $owner->name }}</td>
                        <td>{{ $owner->email }}</td>
                        <td class="text-right">

                            <a href="{{ route('owners.edit', $owner )}}" class="btn btn-sm btn-primary">
                                <i class="las la-pen"></i>
                                <span class="d-none d-md-inline-block">EDIT</span>
                            </a>

                            <a href="#" class="btn btn-sm btn-danger"
                                v-on:click="form_link_confirmation($event,'owner-delete-form-{{ $owner->id }}')" >
                                <i class="las la-trash-alt"></i>
                                <span class="d-none d-md-inline-block">DELETE</span>
                            </a>
                            @include('common.hidden-form', [
                                'id' => 'owner-delete-form-' . $owner->id,
                                'action' => route('owners.delete', $owner),
                                'method' => 'delete'
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
