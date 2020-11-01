@extends('layout')

@section('content')

  @include('partials.companySidebar')
  <div class="col-md-10">
    <h1>Klienter i firmaet {{ $company->name }}</h1>
    <table class="table">
      <tr>
        <th>Navn</th>
        <th>Født</th>
        <th>Beh.ansv.</th>
        <th>Tilganger (nå)</th>
        <th>Status</th>
      </tr>


      @foreach ($clients as $client)
        <tr>
          <td>
            @if (Auth::user()->role === 2 || in_array($client->id, array_column(Auth::user()->clients->toArray(), 'id')) || in_array($client->id, array_column(Auth::user()->coopClients->toArray(), 'id')))
              <a href="{{ route('clients.show', [$client->id]) }}">{{ $client->lastname }}, {{ Crypt::decrypt($client->firstname) }}</a>
            @else
              {{ $client->lastname }}, {{ Crypt::decrypt($client->firstname) }}
            @endif
          </td>
          <td>{{ $client->born->format('d.m.Y') }}</td>
          <td>{{ $client->owner->name }}</td>
          <td>
            <a href="{{ route('clients.access', [$client->id]) }}">
              @if ($client->user->count())
                @foreach($client->user as $user)
                  @if($loop->last)
                    {{ $user->name }}
                  @else
                    {{ $user->name }},
                  @endif
                @endforeach
              @else
                Ingen
              @endif
            </a>
          </td>
          <td>{{ $client->active === 1 ? 'Aktiv' : 'Inaktiv' }}</td>
        </tr>
      @endforeach
    </table>
  </div>
@stop
