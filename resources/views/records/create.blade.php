@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Opprett nytt notat for <a href="{{ route('clients.show', $client->id) }}">{{ $client->firstname }} {{ $client->lastname }}</a></h4>
  <hr />

  Du blir automatisk logget ut om: <span class="countdown"></span> . Sørg for at du lagrer notatet før tiden løper ut.

  <hr>

  <div class="row">
    {!! Form::open(array('route' => array('templates.use', $client->id))) !!}
      {!! Form::hidden('client_id', $client->id) !!}
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::select('template_id', $templates, $template->id, ['id' => 'templates_list', 'class' => 'form-control']) !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::submit('BRUK MAL') !!}
        </div>
      </div>
    {!! Form::close() !!}
  </div>
  <hr />

  <div class="row">
    {!! Form::open(array('route' => array('clients.records.store', $client->id))) !!}
      @include('partials.createrecordform')
    {!! Form::close() !!}
  </div>
</div>

@stop
