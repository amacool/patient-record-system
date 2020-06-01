@extends('layout')

@section('content')

    @include('partials.companysidebar')
    <div class="col-md-10">

        <h4>Registrer en ny bruker for firmaet {{$company->name}}</h4>

    {!! Form::open(array('route' => 'registerusers')) !!}

    {!! Form::hidden('created_by', \Auth::user()->id) !!}

    <div class="col-md-12">
        <div class="form-group">
            Navn
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>
        </div>


        <div class="col-md-12">
            <div class="form-group">
            Email
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>
        </div>

            <div class="col-md-12">
                <div class="form-group">
            Landskode (telefon)
                    <select data-show-as="number" id="authy-countries" name="country_code" data-value="+47"></select>
        </div>
            </div>

                <div class="col-md-12">
                    <div class="form-group">
            Telefonnummer
            <input type="number" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
                </div>

    <div class="form-group">
        {!! Form::hidden('company_id', $company->id) !!}
    </div>

                    <div class="col-md-12">
                        <div class="form-group">
            Passord
            <input type="password" name="password" class="form-control" >
        </div>
                    </div>

                        <div class="col-md-12">
                            <div class="form-group">
            Bekreft passord
            <input type="password" name="password_confirmation" class="form-control" >
        </div>
                        </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit">Registrer</button>
        </div>
        </div>

    {!! Form::close() !!}

@stop