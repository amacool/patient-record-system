@extends('layout')

@section('content')

<h3>Endre info for firma : {{$company->name}}</h3>

<section>
  <div class="container">


    {!! Form::model($company, array('route' => array('companies.update', $company->id), 'method' => 'put')) !!}

    <div class="col-md-12">
      @if (Auth::user()->role === 2)
        <div class="form-group">
          {!! Form::label('name', 'Firmanavnet: ') !!}
          {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
          {!! Form::label('orgnr', 'organisasjonsnummer: ') !!}
          {!! Form::text('orgnr', null, ['class' => 'form-control']) !!}
        </div>
      @endif

      <div class="form-group">
        {!! Form::label('seats', 'Antall brukere: ') !!}
        {!! Form::text('seats', null, ['class' => 'form-control']) !!}
      </div>
    </div>

    <div class="col-md-12">
      {!! Form::submit('Lagre') !!}
    </div>

    {!! Form::close() !!}

  </div>
</section>

@stop
