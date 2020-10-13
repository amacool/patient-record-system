@extends('layout')

@section('content')

@include('partials.confirmdelete')
@include('partials.templatesidebar')

<div class="col-md-10">

  <h4>Alle maler</h4>

  <table class="table">
    <tr>
      <th>Tittel</th>
      <th>Standard</th>
      <th>Endre</th>
      <th>Slett</th>
    </tr>

    @foreach ($templates as $template)
      <tr>
        <td><a href="{{ route('templates.show', [$template->id]) }}">{{ $template->title }} </a></td>
        <td>@if ($user->favtemplate == $template->id) Ja @endif</td>
        <td><a class="btn btn-default" href="{{ route('templates.edit', [$template->id]) }}" role="button">Endre</a></td>
        <td>
          {!! Form::open(array('route' => array('templates.destroy', $template->id), 'method' => 'delete', 'onsubmit' => 'return ConfirmDelete()')) !!}
          <button type="submit" class="btn btn-danger">Slett</button>
          {!! Form::close() !!}
        </td>
      </tr>
    @endforeach
  </table>

  @stop