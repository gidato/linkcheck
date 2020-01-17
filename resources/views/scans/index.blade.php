@extends('layouts.app')

<?php $colors = ['errors' => 'danger', 'queued' => 'orange', 'processing' => 'orange', 'success' => 'success', 'aborted' => 'danger']; ?>

@section('title', 'Scans')

@section('content')


    <div class="container">
        <form method="get" action={{ route('scans.list') }} class="mb-2">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="filter-label">Filter:</span>
                </div>
                <select class="custom-select" name="id" onchange="this.form.submit()" aria-describedby="filter-label">
                    <option value=''>All Sites</option>
                    @foreach ($sites as $site)
                        <option value="{{ $site->id }}" @if ($site->id == $siteId) selected @endif >{{ $site->url }}</option>
                        @endforeach
                </select>
            </div>
        </form>

        <div class="accordian" id="scansAccordian">
            <form method="post" action="{{ route('scans.delete.many') }}" v-on:submit="confirmation">
                @method('delete')
                @csrf
                @if ($scans->count() == 0)
                    <em>-- None -- </em>
                @else
                    @foreach ($scans as $scan)
                        <?php $status = ($scan) ? $scan->status : null; ?>
                        <?php $color = ($scan) ? $colors[$status] : 'secondary'; ?>
                        <div class="card mb-1">
                            <div class="card-header hand" id="scan-{{ $scan->id }}" data-toggle="collapse" data-target="#scan-detail-{{ $scan->id }}">
                                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <input type="checkbox" class="mr-2" name="id[]" value="{{ $scan->id }}" v-model="checked">
                                            <span class="badge badge-{{ $color }}">{{ Str::upper($status) }}</span>
                                            <small><strong>{{ $scan->site->url }}</strong></small>
                                        </h5>
                                    </div>
                                    <div>
                                        {{ $scan->updated_at->format('l, j-M-Y \a\t H:i:s') }}
                                    </div>
                                </div>
                            </div>

                            <div
                                id="scan-detail-{{ $scan->id }}"
                                class="collapse"
                                aria-labelledby="heading-{{ $scan->id }}"
                                data-parent="#scansAccordian"
                            >
                            @include('scans.common.summary', ['scan' => $scan])
                            @include('scans.common.card-links', ['scan' => $scan])

                            </div>
                        </div>
                    @endforeach
                    <div class="mt-4 text-right">
                        <button class="btn btn-danger" :disabled="checked.length == 0">DELETE CHECKED</button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if ($scans->count() > 0)
        @foreach ($scans as $scan)
            @include('scans.common.card-link-forms', ['scan' => $scan])
        @endforeach
    @endif

@endsection
