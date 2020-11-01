@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">
  <h4>Endre tilganger for <a href="{{ route('clients.show', $client->id) }}">{{ $client->firstname }} {{ $client->lastname }}</a></h4>
  <hr />
    <h5>Behandlingsansvarlig: {{ $client->owner->name }} ({{$client->owner->company->name}})</h5>
  <hr />
  @if (!count($coopusers))
    Ingen andre enn {{ $client->owner->name }} har på nåværende tidspunkt tilgang til {{ $client->firstname }} {{ $client->lastname }}.
  @else

  <h4>Følgende brukere (i tillegg til {{ $client->owner->name }}) har tilgang til {{ $client->firstname }} {{ $client->lastname }}</h4>

  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Fjern tilgang</th>
    </tr>

    @foreach ($coopusers as $user)
    <tr>
      <td>{{ $user->name }}</td>
      <td><a class="btn btn-default" href="{{ route('clients.remove_access_form', [$client->id, $user->id]) }}" role="button">FJERN TILGANG</a></td>
    </tr>
    @endforeach

  </table>
  @endif

  <hr />

  @if (Auth::user()->role === 2)
    <h4>Gi tilgang til bruker fra behandlingsansvarliges firma</h4>
  @else
    <h4>Du kan gi tilgang til følgende fra firmaet ditt</h4>
  @endif
  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Gi tilgang</th>
    </tr>
    @foreach ($otherUsers as $user)
      @unless ($user->id === $client->owner->id || $user->company_id !== $client->owner->company_id)
        <tr>
          <td>{{ $user->name }}</td>
          <td><a class="btn btn-default" href="{{ route('clients.access_form', [$client->id, $user->id]) }}" role="button">GI TILGANG</a></td>
        </tr>
      @endunless
    @endforeach
  </table>

  @if (Auth::user()->role === 2)
    <hr />

    <h4>Gi tilgang til bruker fra annet firma</h4>

    <table class="table">
      <tr>
        <th>Navn</th>
        <th>Gi tilgang</th>
      </tr>
      @foreach ($otherUsers as $user)
        @unless ($user->id === $client->owner->id || $user->company_id === $client->owner->company_id)
          <tr>
            <td>{{ $user->name }}</td>
            <td><a class="btn btn-default" href="{{ route('clients.access_form', [$client->id, $user->id]) }}" role="button">GI TILGANG</a></td>
          </tr>
        @endunless
      @endforeach
    </table>
  @endif

@stop
