@extends('layout')

@section('content')

@include('partials.clientSidebar')

<div class="col-md-9 col-md-offset-3">
  <h4>Endre tilganger for <a href="{{ route('clients.show', $client->id) }}">{{ $client->firstname }} {{ $client->lastname }}</a></h4>
  <hr />
  @if (!count($coopusers))
    Ingen andre enn deghar på nåværende tidspunkt tilgang til {{ $client->firstname }} {{ $client->lastname }}.
  @else

  <h4>Følgende personer (i tillegg til deg) har tilgang til {{ $client->firstname }} {{ $client->lastname }}</h4>

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

  <h4>Du kan gi tilgang til følgende fra firmaet ditt</h4>

  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Gi tilgang</th>
    </tr>
    @foreach ($othersInCompany as $user)
      @unless ($user->id === Auth::user()->id)
        <tr>
          <td>{{ $user->name }}</td>
          <td><a class="btn btn-default" href="{{ route('clients.access_form', [$client->id, $user->id]) }}" role="button">GI TILGANG</a></td>
        </tr>
      @endunless
    @endforeach
  </table>

@stop