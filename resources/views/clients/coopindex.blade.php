@extends('layout')

@section('content')

@include('partials.confirmdelete')

@include('partials.clientssidebar')


<h1>Tilganger jeg har mottatt fra andre brukere
</h1>

<table class="table">
  <tr>
    <th>Navn</th>
    <th>Født</th>
    <th>Journalnotater</th>
    <th>Filer</th>
    <th>Status</th>
    <th>Eier</th>

  </tr>

  @foreach ($clients as $client)
  <tr>
    <td><a href="{{ route('clients.show', [$client->id]) }}">{{ $client->lastname }}, {{ \Crypt::decrypt($client->firstname) }}</a></td>
    <td>{{$client->born->format('d.m.Y')}}</td>
    <td><a class="btn btn-default" href="{{ route('clients.records.list', [$client->id]) }}" role="button">SE</a>
      <a class="btn btn-default" href="{{ route('clients.records.create', [$client->id]) }}" role="button">SKRIV</a>
    </td>
    <td><a class="btn btn-default" href="{{ route('clients.files.index', [$client->id]) }}" role="button">SE</a>
      <a class="btn btn-default" href="{{ route('clients.files.create', [$client->id]) }}" role="button">LAST OPP</a>
    </td>
    <td>{{ $client->active === 1 ? 'Aktiv' : 'Inaktiv' }}</td>
    <td>{{ $client->owner->name }}</td>
  </tr>
  @endforeach
</table>
@stop
