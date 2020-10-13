@extends('layout')

@section('content')

@include('partials.templatesidebar')
<div class="col-md-10">

  <div class="panel panel-default">
    <div class="panel-heading">
      {{$template->title}}

      {!! Form::open(array('route' => array('templates.set_favorite', $template->id), 'method' => 'POST')) !!}
      <button type="submit" @if ($user->favtemplate == $template->id)
        class="btn btn-default btn-sm pull-right">Fjern som standard</button>
      @endif

      @if ($user->favtemplate !== $template->id)
      class="btn btn-default btn-sm pull-right">Sett som standard</button>
      @endif

      {!! Form::close() !!}
      <a class="btn btn-default btn-sm pull-right" href="{{ route('templates.edit', $template->id) }}" role="button">ENDRE</a>
      <br />
    </div>
    <div class="panel-body">{!! $template->content !!}</div>
  </div>

</div>
@stop