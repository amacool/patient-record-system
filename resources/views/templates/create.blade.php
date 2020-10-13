@extends('layout')

@section('content')
@include('partials.templatesidebar')
<div class="col-md-10">

  <h4>Opprett ny mal</h4>
  <hr />

  {!! Form::open(array('route' => 'templates.store')) !!}
    {!! Form::hidden('category_id', 1) !!}

    {{--<div class="col-md-10">
          <div class="form-group">
              {!! Form::label('category_id', 'Category: ') !!}
              {!! Form::select('category_id', $categories, null, ['id' => 'categories_list', 'class' => 'form-control']) !!}
          </div>
      </div>--}}

    @include('partials.createtemplateform')

  {!! Form::close() !!}

</div>

@stop