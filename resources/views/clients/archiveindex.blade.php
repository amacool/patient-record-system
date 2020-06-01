@extends('layout')

@section('content')

    @include('partials.confirmdelete')

    @include('partials.clientssidebar')

    <h1>Klienter i mitt arkiv (ikke aktive)</h1>

    <table class="table">
        <tr>
            <th>Navn</th>
            <th>Født</th>
            <th>Journalnotater</th>
            <th>Filer</th>
            <th>Tilganger</th>
            <th>Aktiver</th>
        </tr>

        @foreach ($clients as $client)
            <tr>
                <td><a href="{{ route('clients.show', [$client->id]) }}">{{$client->lastname}}, {{\Crypt::decrypt($client->firstname)}}</a></td>
                <td>{{$client->born->format('d.m.Y')}}</td>
                <td><a class="btn btn-default" href="{{ route('clients.records.show', [$client->id]) }}" role="button">SE</a>
                    <a class="btn btn-default" href="{{ route('clients.records.create', [$client->id]) }}" role="button">SKRIV</a>
                </td>
                <td><a class="btn btn-default" href="{{ route('clients.files.index', [$client->id]) }}" role="button">SE</a>
                    <a class="btn btn-default" href="{{ route('clients.files.create', [$client->id]) }}" role="button">LAST OPP</a>
                </td>
                <td><a class="btn btn-default" href="{{ route('clients.access', [$client->id]) }}" role="button">ENDRE</a></td>
                <td>
                    {!! Form::open(array('route' => array('clients.archivemove'), 'method' => 'POST')) !!}
                    {!! Form::hidden('client_id', $client->id) !!}
                    <button type="submit" class="btn btn-danger">Aktiver</button>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
    </table>
@stop