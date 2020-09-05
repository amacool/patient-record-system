@extends('layout')

@section('content')

@include('partials.clientSidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Liste over alle notater for {{ $client->firstname }} {{ $client->lastname }}</h4>

  <table class="table">
    <tr>
      <th>Tittel</th>
      <th>Forfatter</th>
      <th>Avtaledato</th>
      <th>Opprettet</th>
      <th>Signert</th>
    </tr>

    @foreach ($records as $record)
    <tr>
      <td><a href="{{ route('clients.records.show', [$client->id, $record->id]) }}">{{ Crypt::decrypt($record->title) }}</a></td>
      <td>{{ $record->user->name }}</td>
      @if (($record->app_date->format('d/m/Y')) == "30/11/-0001")
      <td></td>
      @else
      <td>{{ $record->app_date->format('d/m/Y') }}</td>
      @endif
      <td>{{ $record->created_at->format('d/m/Y, H:i') }}</td>

      <td>
        @if ($record->signed_by == null)
          @if ($record->created_by === Auth::user()->id)
            {!! Form::open(array('route' => array('clients.records.sign', $client->id), 'method' => 'POST')) !!}
              {!! Form::hidden('created_by', Auth::user()->id) !!}
              {!! Form::hidden('record_id', $record->id) !!}
              {!! Form::hidden('client_id', $client->id) !!}
              <button type="submit" class="btn btn-danger">SIGNER</button>
            {!! Form::close() !!}
          @endif
          @if ($record->created_by !== Auth::user()->id)
            Nei
          @endif
        @endif
        @if ($record->signed_by == !null)
          {{ $record->signed_date->format('d/m/Y, H:i') }}
        @endif
      </td>
    </tr>
    @endforeach
  </table>

@stop
