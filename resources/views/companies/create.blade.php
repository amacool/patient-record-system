@extends('layout')

@section('content')

    @include('partials.companiessidebar')
    <div class="col-md-10 col-md-offset-3">

    <h4>Registrer et nytt firma</h4>
        <hr/>

    {!! Form::open(array('route' => 'companies.store')) !!}

    @include('partials.createcompanyform')

    {!! Form::close() !!}

@stop