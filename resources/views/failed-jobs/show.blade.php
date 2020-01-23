@extends('layouts.app')

@section('title-bar','container-fluid')
@section('title', 'Failed Job')
@section('title-buttons')
    <div class="btn-toolbar mb-2 mb-md-0">
        <form class="d-inline-block mr-2" action="{{ route( 'failed-jobs.retry', $job->id ) }}" method="post">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="las la-redo-alt"></i>
                Retry
            </button>
        </form>
        <form class="d-inline-block" action="{{ route( 'failed-jobs.delete', $job->id ) }}" method="post">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger">
                <i class="las la-trash-alt"></i>
                Delete
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="card mb-3 border">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <strong>Summary</strong>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-2">Job Id</dt>
                    <dd>{{ $job->id }}</dd>
                </dl>
                <dl class="row">
                    <dt class="col-md-2">Job Class</dt>
                    <dd>{{ $job->jobName }}</dd>
                </dl>
                <dl class="row">
                    <dt class="col-md-2">Failed At</dt>
                    <dd>{{ $job->failed_at }}</dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3 border">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <strong>Related Scan</strong>
                </div>
            </div>
            <div class="card-body">
                @if ( property_exists($job->command,'scan'))
                    <dl class="row">
                        <dt class="col-md-2">Url</dt>
                        <dd>{{ (string) $job->command->scan->site->url }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-2">Checked</dt>
                        <dd>{{ $job->command->scan->pages->where('checked',true)->count() }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-2">Not Checked</dt>
                        <dd>{{ $job->command->scan->pages->where('checked',false)->count() }}</dd>
                    </dl>
                @else
                    <em>-- No accessible scan </em>
                @endif
            </div>
        </div>

        <div class="card mb-3 border">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <strong>Exception</strong>
                </div>
            </div>
            <div class="card-body">
                <pre>{{ $job->exception }}</pre>
            </div>
        </div>
    </div>


@endsection
