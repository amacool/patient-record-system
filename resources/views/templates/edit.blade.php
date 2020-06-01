@extends('layout')

@section('content')

    @include('partials.templatesidebar')
    <div class="col-md-10">

    <h4>Endre mal</h4>
        <hr/>

    {!! Form::model($template, array('route' => array('templates.update', $template->id), 'method' => 'put')) !!}

    @include('partials.createtemplateform')

    {!! Form::close() !!}

    </div>

@stop