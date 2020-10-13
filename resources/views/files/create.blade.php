@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Last opp en fil for klient <a href="{{ route('clients.show', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a></h4>
  <hr />

  {!! Form::open(array('route' => array('clients.files.store', $client->id), 'method' => 'post', 'files' => 'true')) !!}

  <div class="form-group">
    {!! Form::label('file', 'Last opp nytt dokument: ') !!}
    {!! Form::file('file', null, ['class' => 'form-control']) !!}
  </div>

  <div class="form-group">
    {!! Form::label('description', 'Beskrivelse: ') !!}
    {!! Form::text('description', null, ['class' => 'form-control']) !!}
  </div>

  <div class="form-group">
    {!! Form::submit('Last opp', ['class' => 'btn btn-primary']) !!}
  </div>

  {!! Form::close() !!}

  @stop