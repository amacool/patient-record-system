@extends('layout')

@section('content')

  @include('partials.userSidebar')
  <div class="col-md-10 col-md-offset-3">
    <h3>Aktive klienter for bruker {{ $user->name }}</h3>
    <table class="table">
      <tr>
        <th>Navn</th>
        <th>Født</th>
        <th>owner</th>
        <th>coop users</th>
        <th>status</th>
      </tr>

      @foreach ($clients as $client)
        <tr>
          <td>
            @if (Auth::user()->role === 2 || in_array($client->id, array_column(Auth::user()->coopClients->toArray(), 'id')))
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
                No cooperation
              @endif
            </a>
          </td>
          <td>{{ $client->active === 1 ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </table>
  </div>

@stop
