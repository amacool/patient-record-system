@extends('layout')

@section('content')

  <h1>Alle klienter</h1>


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
        <td><a href="{{ route('clients.show', [$client->id]) }}">{{ $client->lastname }}, {{ Crypt::decrypt($client->firstname) }}</a></td>
        <td>{{ $client->born->format('d.m.Y') }}</td>
        <td>{{ $client->owner->name }}</td>
        <td>
          @foreach($client->user as $user)
            @if($loop->last)
              {{ $user->name }}
            @else
              {{ $user->name }},
            @endif
          @endforeach
        </td>
        <td>{{ $client->active === 1 ? 'Active' : 'Inactive' }}</td>
      </tr>
    @endforeach
  </table>
  {{ $clients->onEachSide(3)->links() }}
@stop
