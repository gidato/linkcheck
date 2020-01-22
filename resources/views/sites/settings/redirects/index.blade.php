@extends('layouts.app')

@section('title', 'Manage Approved Redirects')

@section('content')
    <div class="container">
        <div class="card mb-3">

                <div class="card-header">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <h5>Approved Redirects for {{ $site->url }}</h5>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="{{route('sites.settings', $site)}}" class="btn btn-sm btn-primary"><i class="las la-angle-left"></i> BACK TO SETTINGS</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($site->approvedRedirects as $redirect)
                                <tr>
                                    <td>
                                        {{ $redirect->from_url }}
                                    </td>
                                    <td>
                                        {{ $redirect->to_url }}
                                    </td>
                                    <td class="text-right">
                                        <form action="{{route('sites.redirects.delete', [$site, $redirect]) }}" method="POST">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="las la-trash-alt"></i>
                                                <span class="d-none d-md-inline-block">DELETE</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <em>-- NONE --</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>


@endsection
