@extends('layout')

@section('content')

  @include('partials.clientsidebar')

  <div class="col-md-10 col-md-offset-3">
    <h4>Flytt notat til en annen klient</h4>
    <hr />
    <form method="GET">
      <div class="form-group" style="display: flex">
        <input
          type="text"
          class="form-control"
          name="search"
          value="{{ $search }}"
          style="max-width: 300px; margin-right: 5px"
          placeholder="Please enter client id or client name."
        />
        <button class="btn btn-primary" type="submit">Søk</button>
      </div>
    </form>
    <table class="table">
      <tr>
        <th>clinet id</th>
        <th>client name</th>
        <th>Move</th>
      </tr>

      @foreach ($clients as $otherClient)
        <tr>
          <td>{{ $otherClient->id }}</td>
          <td>{{ Crypt::decrypt($otherClient->lastname) }}, {{ Crypt::decrypt($otherClient->firstname) }}</td>
          <td>
            {!! Form::open(array('route' => array('clients.records.move_post', $client->id, $record->id, $otherClient->id), 'method' => 'POST')) !!}
              <button type="submit" class="btn btn-default">Flytt</button>
            {!! Form::close() !!}
          </td>
        </tr>
      @endforeach
    </table>
    {!! $clients->appends(['search' => $search])->render() !!}
  </div>

@stop
