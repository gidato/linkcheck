@extends('layouts.app')

<?php $colors = ['errors' => 'danger', 'queued' => 'orange', 'processing' => 'orange', 'success' => 'success', 'aborted' => 'danger']; ?>

@section('title', 'Sites')
@section('title-buttons')
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Add</a>
    </div>
@endsection

@section('content')
    <div class="container">
        @forelse ($sites as $site)
            <?php $scan = $site->scans[0] ?? null; ?>
            <?php $status = ($scan) ? $scan->status : null; ?>
            <?php $color = ($scan) ? $colors[$status] : 'secondary'; ?>
            <div class="card mb-3 border-{{ $color }}">
                <div class="card-header  @if (!$site->validated) bg-danger text-white @endif">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <h5>
                                @if ($scan)
                                    <span class="badge badge-{{ $color }}">{{ Str::upper($status) }}</span>
                                @endif
                                <small><strong>{{ (string) $site->url }}</strong></small>
                                @if ($scan)
                                    <br><small><small>{{ $scan->updated_at }}</small></small>
                                @endif
                            </h5>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            @if (!$scan || $scan->isComplete())
                                <form action="{{ route('scans.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                                    <button class="btn btn btn-orange mr-2">{{ __('NEW SCAN') }}</button>
                                </form>
                            @else
                                <form action="{{ route('scans.abort', $scan) }}" method="POST">
                                    @csrf
                                    <button class="btn btn btn-danger mr-2">{{ __('ABORT') }}</button>
                                </form>
                            @endif
                            <a href="{{ route('scans.list', ['id' => $site->id]) }}" class="btn btn btn-secondary mr-2">{{ __('HISTORY') }}</a>
                            <a href="{{ route('sites.settings', $site) }}" class="btn btn btn-info"><i class="las la-cog"></i></a>
                        </div>
                    </div>
                </div>

                @if ($scan)
                    @include('scans.common.summary', ['scan' => $scan])
                    @include('scans.common.card-links', ['scan' => $scan])
                    @include('scans.common.card-link-forms', ['scan' => $scan])
                @else
                    @include('scans.common.summary', ['site' => $site])
                @endif
            </div>
        @empty
            <em>-- None -- </em>
        @endforelse
    </div>


@endsection
