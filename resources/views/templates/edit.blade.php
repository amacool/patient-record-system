@extends('layout')

@section('content')

@include('partials.templateSidebar')

<div class="col-md-10">
  <h4>Endre mal</h4>
  <hr />

  {!! Form::model($template, array('route' => array('templates.update', $template->id), 'method' => 'put')) !!}
    {!! Form::hidden('category_id', $template->category_id) !!}
    @include('partials.createTemplateForm')
  {!! Form::close() !!}
</div>

@stop