@extends('layout')

@section('content')

    @include('partials.userSidebar')
    <div class="col-md-10 col-md-offset-3">
        <h3>Coop Access</h3>
        <table class="table">
            <tr>
                <th>Client name</th>
                <th>User name</th>
                <th>Reason</th>
                <th>Time</th>
            </tr>

            @foreach ($accessRights as $accessRight)
                <tr>
                    <td>{{ \Crypt::decrypt($accessRight->clients->lastname) }}, {{ \Crypt::decrypt($accessRight->clients->firstname) }}</td>
                    <td>{{ $accessRight->user->name }}</td>
                    <td>{{ $accessRight->reason }}</td>
                    <td>{{ $accessRight->datetime }}</td>
                </tr>
            @endforeach
        </table>
    </div>

@stop
