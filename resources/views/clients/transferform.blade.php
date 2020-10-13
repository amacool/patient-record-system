@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Overfør klient {{$client->firstname}} {{$client->lastname}} til bruker {{$user->name}} for </h4>
  Du vil ikke lengre ha tilgang til klienten, men andre tilganger berøres ikke av overføringen. Dersom du ønsker å endre andre
  tilganger, må du gjøre dette først.

  <section>
    <div class="container">

      {!! Form::open(array('route' => ['clients.transfer_form_post', $client->id, $user->id])) !!}

        {!! Form::hidden('user_id', $user->id) !!}
        {!! Form::hidden('client_id', $client->id) !!}

        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('reason', 'Årsak til overføringen: ') !!}
            {!! Form::textarea('reason', null, ['class' => 'form-control']) !!}
          </div>
        </div>

        <div class="col-md-12">
          {!! Form::submit('Overfør') !!}
        </div>

      {!! Form::close() !!}

    </div>
  </section>

  @stop