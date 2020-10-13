@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <div class="panel panel-default">
    <div class="panel-heading">

      "{{ $record->title }}", forfatter {{ $record->user->name }} (opprettet {{ $record->created_at->format('d/m/Y') }}).

      <span class="pull-right">

        Avtaledato
        @if (($record->app_date->format('d/m/Y')) == "30/11/-0001")
          ikke angitt
        @else
          {{ $record->app_date->format('d/m/Y') }}
        @endif

      </span>

      <br />Notatet ble sist oppdatert {{$record->updated_at->format('d/m/Y')}}.
      @if ($record->updated_at == $record->signed_date)
        (signering)
      @endif

      <span class="pull-right">
        @if ($record->signed_by == null)
          Ikke signert
        @endif

        @if ($record->signed_by !== null)
          Signert {{ $record->signed_date->format('d/m/Y') }} av {{ $record->user->name }}
        @endif
      </span>
      <br />
      Klient: {{ Crypt::decrypt($record->client->firstname) }} {{ Crypt::decrypt($record->client->lastname) }} (født {{ $record->client->born->format('d.m.Y') }})

      <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.print_show', [$client->id, $record->id]) }}" role="button">UTSKRIFTSVERSJON</a>
      @if ($record->created_by === Auth::user()->id)
        @if ($record->signed_by !== null)
        <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.unsign_form', [$client->id, $record->id]) }}" role="button">AVSIGNER</a>
        @endif
        @if ($record->signed_by == null)
        <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.edit', [$client->id, $record->id]) }}" role="button">ENDRE</a>
        @endif
      @endif
      <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.records.change_history', [$client->id, $record->id]) }}" role="button">HISTORIE</a>
      <br />

    </div>
    <div class="panel-body">{!! $record->content !!}</div>
  </div>
</div>

@stop
