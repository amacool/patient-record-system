@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h2>Endre notatet "{{ $record->title }}" for <a href="{{ route('clients.show', $client->id) }}">{{ $client->firstname }} {{ $client->lastname }}</a></h2>

  <hr />

  Du blir automatisk logget ut om: <span class="countdown"></span> . Sørg for at du lagrer notatet før tiden løper ut.

  <hr>

  {!! Form::model($record, array('route' => array('clients.records.update', $client->id, $record->id), 'method' => 'put')) !!}

  {!! Form::hidden('client_id', $client->id) !!}
  {!! Form::hidden('category_id', 1) !!}

  {{--<div class="col-md-12">

        <div class="form-group">
            {!! Form::label('category_id', 'Journal note: ') !!}
            {!! Form::radio('category_id', '1', true) !!}
            {!! Form::label('category_id', 'Treatment Plan: ') !!}
            {!! Form::radio('category_id', '2') !!}
            {!! Form::label('category_id', 'Report: ') !!}
            {!! Form::radio('category_id', '3') !!}
        </div>
    </div>--}}

  <div class="col-md-6">
    <div class="form-group">
      {!! Form::label('app_date', 'Dato for avtalen: ') !!}
      {!! Form::text('app_date', $record->app_date->format('d-m-Y'), ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {!! Form::label('title', 'Tittel: ') !!}
      {!! Form::text('title', null, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {!! Form::label('content', 'Innhold: ') !!}
      {!! Form::textarea('content', null, ['class' => 'form-control']) !!}
    </div>
    <script>
      // Replace the <textarea id="content"> with a CKEditor
      // instance, using default configuration.
      CKEDITOR.replace('content');
    </script>
  </div>



  <div class="col-md-12">
    {!! Form::submit('Lagre') !!}
  </div>

  {!! Form::close() !!}

  @stop