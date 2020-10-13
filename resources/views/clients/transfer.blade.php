@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Overfør klient <a href="{{ route('clients.show', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a> til en annen psykolog</h4>
  <hr />

  Du kan overføre klienten til følgende personer i ditt firma. En slik overføring betyr at du ikke lengre vil ha tilgang til klienten, men andre tilganger vil ikke berøres.
  <hr />

  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Overfør</th>
    </tr>
    @foreach ($restofcompany as $user)

    @unless ($user->id == Auth::user()->id)
    <tr>
      <td>{{$user->name}}</td>
      <td><a class="btn btn-default" href="{{ route('clients.transfer_form', [$client->id, $user->id]) }}" role="button">TRANSFER</a></td>
    </tr>
    @endunless
    @endforeach

  </table>

  @stop