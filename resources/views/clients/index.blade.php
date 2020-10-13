@extends('layout')

@section('content')

@include('partials.confirmdelete')

@include('partials.clientssidebar')

{{--<div class="alert alert-danger" role="alert">
        04.07.18: PLANLAGT VEDLIKEHOLD: På grunn av arbeid på serveren vil journalsystemet være ustabilt
        i ca 15 minutter fra torsdag 5.juli klokken 23.50. Det anbefales å ikke skrive notater i dette
        tidsrommet, da du kan risikere at notatet ikke blir lagret.
    </div>--}}

<h1>Aktive klienter</h1>


<table class="table">
  <tr>
    <th>Navn</th>
    <th>Født</th>
    <th>Journalnotater</th>
    <th>Filer</th>
    <th>Tilganger</th>
    <th>Skjul</th>
  </tr>

  @foreach ($clients as $client)
  <tr>
    <td><a href="{{ route('clients.show', [$client->id]) }}">{{ $client->lastname }}, {{ Crypt::decrypt($client->firstname) }}</a></td>
    <td>{{ $client->born->format('d.m.Y') }}</td>
    <td><a class="btn btn-default" href="{{ route('clients.records.list', [$client->id]) }}" role="button">SE</a>
      <a class="btn btn-default" href="{{ route('clients.records.create', [$client->id]) }}" role="button">SKRIV</a>
    </td>
    <td><a class="btn btn-default" href="{{ route('clients.files.index', [$client->id]) }}" role="button">SE</a>
      <a class="btn btn-default" href="{{ route('clients.files.create', [$client->id]) }}" role="button">LAST OPP</a>
    </td>
    <td><a class="btn btn-default" href="{{ route('clients.access', [$client->id]) }}" role="button">ENDRE</a></td>
    <td>
      {!! Form::open(array('route' => array('clients.archive_move'), 'method' => 'POST')) !!}
      {!! Form::hidden('client_id', $client->id) !!}
      <button type="submit" class="btn btn-danger">Skjul</button>
      {!! Form::close() !!}
    </td>
  </tr>
  @endforeach
</table>
@stop
