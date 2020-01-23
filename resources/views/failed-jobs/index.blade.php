@extends('layouts.app')

@section('title', 'Failed Jobs')
@section('title-buttons')
    @if (count($jobs) > 0)
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-danger"
                v-on:click="form_job_flush_confirmation($event,'flush-jobs')" >
                <i class="las la-trash-alt"></i>
                FLUSH
            </a>
            @include('common.hidden-form', [
                'id' => 'flush-jobs',
                'action' => route('failed-jobs.delete-all'),
                'method' => 'delete'
            ])
        </div>
    @endif
@endsection

@section('content')
    <div class="container">

        <div class="card mb-3 border">
            <div class="card-body">
                <table class="table">
                    <thead class="thead-light">
                        <tr>
                            <th class="fit">ID</th>
                            <th>Class</th>
                            <th>Failed At</th>
                            <th>Exception</th>
                            <th></th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $job)
                            <tr>
                                <td>{{ $job->id }}</td>
                                <td>{{ $job->jobName }}</td>
                                <td>{{ $job->failed_at }}</td>
                                <td>{{ $job->exception->firstLine }}</td>
                                <td class="text-right fit">
                                    <a href="{{ route( 'failed-jobs.show', $job->id ) }}" class="btn btn-sm btn-primary">
                                        <i class="las la-eye"></i>
                                        View details
                                    </a>
                                    <form class="d-inline-block" action="{{ route( 'failed-jobs.delete', $job->id ) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="las la-trash-alt"></i>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <em>-- No failed Jobs --</em>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


@endsection
