@extends('layout')

@section('content')

@include('partials.clientsSidebar')

<h4>Registrer en ny klient</h4>
<hr />

{!! Form::open(array('route' => 'clients.store', 'autocomplete' => 'off')) !!}

  {!! Form::hidden('active', '1') !!}

  <h4>Påkrevde felt</h4>

  <div class="col-md-3">
    <div class="form-group">
      {{--firstname--}}
      {!! Form::label('dhjwhq3v7j', 'Fornavn: ') !!}
      {!! Form::text('dhjwhq3v7j', null, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      {{--lastname--}}
      {!! Form::label('6x93mscfgo', 'Etternavn: ') !!}
      {!! Form::text('6x93mscfgo', null, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      {{--born--}}
      {!! Form::label('i2hmibi8a5', 'Født (dd.mm.åååå): ') !!}
      {!! Form::text('i2hmibi8a5', null, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      {{--ssn--}}
      {!! Form::label('0rpk6x0uoe', 'Fødselsnr: ') !!}
      {!! Form::text('0rpk6x0uoe', null, ['class' => 'form-control', 'placeholder' => '11111 hvis ukjent']) !!}
    </div>
  </div>

  <div class="col-md-12">
    <hr />
  </div>

  <div>
    <hr />
    <h4>Valgfrie felt</h4>
  </div>

  @include('partials.createClientForm')

{!! Form::close() !!}

@stop
