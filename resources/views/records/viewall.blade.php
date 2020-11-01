@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Alle notater for<a href="{{ route('clients.show', $client->id) }}">
      {{$client->firstname}} {{$client->lastname}} - ({{$client->born->format('d.m.Y')}} {{$client->ssn}})
    </a>
    <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.print_all', [$client->id]) }}" role="button">UTSKRIFTSVERSJON</a>
  </h4>
  <hr />

  @foreach ($records as $record)
  <div class="panel panel-default">
    <div class="panel-heading">

      "{{ Crypt::decrypt($record->title)}}", forfatter {{ $record->user->name }} (opprettet {{ $record->created_at->format('d/m/Y') }}).

      <span class="pull-right">
        Avtaledato
        @if (($record->app_date->format('d/m/Y')) == "30/11/-0001")
          ikke angitt
        @else
          {{ $record->app_date->format('d/m/Y')}} 
        @endif
      </span>

      <br />Notatet ble sist oppdatert {{ $record->updated_at->format('d/m/Y') }}

      <span class="pull-right">
        @if ($record->signed_by == null)
          Ikke signert
        @endif

        @if ($record->signed_by !== null)
          Signert {{ $record->signed_date->format('d/m/Y') }} av {{ $record->user->name }}
        @endif
      </span>
      <br />

      @if ($record->created_by === Auth::user()->id)
      <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.edit', [$client->id, $record->id]) }}" role="button">ENDRE</a>
      @endif
      <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.change_history', [$client->id, $record->id]) }}" role="button">HISTORIE</a>
      <br />

    </div>
    <div class="panel-body">{!! $parser->parse(Crypt::decrypt($record->content)) !!}</div>
  </div>
  @endforeach

</div>

@stop
