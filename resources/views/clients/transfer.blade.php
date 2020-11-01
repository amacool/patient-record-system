@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Overfør klient <a href="{{ route('clients.show', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a> til en annen bruker</h4>
  <h5>Nåværende behandlingsansvarlig: {{ $client->owner->name }} ({{$client->owner->company->name}})</h5>
  <hr />

  Du kan overføre klienten til brukere i listen under. En slik overføring betyr at du ikke lengre vil ha tilgang til klienten, men andre tilganger vil ikke berøres.
  En overføring er det samme som bytte av behandlingsansvar.

  <hr>

  @if (Auth::user()->role === 2)
    <h4>Overfør til bruker i behandlingsansvarliges firma</h4>
  @endif
  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Overfør</th>
    </tr>
    @foreach ($restUsers as $user)

    @unless ($user->id == $client->owner->id || $user->company_id !== $client->owner->company_id)
    <tr>
      <td>{{$user->name}}</td>
      <td><a class="btn btn-default" href="{{ route('clients.transfer_form', [$client->id, $user->id]) }}" role="button">OVERFØR</a></td>
    </tr>
    @endunless
    @endforeach

  </table>

  @if (Auth::user()->role === 2)
    <h4>Overfør til bruker fra et annet firma</h4>

    <table class="table">
      <tr>
        <th>Navn</th>
        <th>Overfør</th>
      </tr>
      @foreach ($restUsers as $user)

        @unless ($user->id == $client->owner->id || $user->company_id === $client->owner->company_id)
          <tr>
            <td>{{$user->name}}</td>
            <td><a class="btn btn-default" href="{{ route('clients.transfer_form', [$client->id, $user->id]) }}" role="button">TRANSFER</a></td>
          </tr>
        @endunless
      @endforeach
    </table>
  @endif
  @stop
