@extends('layout')

@section('content')

{!! Form::open(array('route' => 'register-users')) !!}

  {!! Form::hidden('created_by', Auth::user()->id) !!}

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Navn') }}
      <input type="text" name="name" class="form-control" value="{{ old('name') }}">
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Email') }}
      <input type="email" name="email" class="form-control" value="{{ old('email') }}">
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Landskode (telefon)') }}
      <select data-show-as="number" class="form-control" id="authy-countries" name="country_code" data-value="+47"></select>
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Telefonnummer') }}
      <input type="number" name="phone" class="form-control" value="{{ old('phone') }}">
    </div>
  </div>

  <div class="form-group">
    {!! Form::label('roleradio', 'Rolle') !!}<p>
      <div class="radio radio-success">
        <label>
          {!! Form::radio('role_id', '0') !!}
          <span class="circle"></span><span class="check"></span>
          {{ __('Vanlig bruker') }}
        </label>
      </div>
      <div class="radio radio-success">
        <label>
          {!! Form::radio('role_id', '1') !!}
          <span class="circle"></span><span class="check"></span>
          {{ __('Firmaadministrator') }}
        </label>
      </div>
  </div>

  <div class="form-group">
    {!! Form::label('company_id', 'Firma: ') !!}
    {!! Form::select('company_id', $companies, null, ['id' => 'companies_list', 'class' => 'form-control']) !!}
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Passord') }}
      <input type="password" name="password" class="form-control">
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{ __('Bekreft passord') }}
      <input type="password" name="password_confirmation" class="form-control">
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <button type="submit">{{ __('Registrer') }}</button>
    </div>
  </div>

{!! Form::close() !!}

@stop