@extends('layout')

@section('content')

@include('partials.confirmDelete')
@include('partials.companiesSidebar')
<div class="col-md-10">

  <h4>Alle registrerte firma</h4>

  <table class="table">
    <tr>
      <th>Navn</th>
      <th>Endre</th>
      <th>Slett</th>
    </tr>
    @foreach ($companies as $company)
    <tr>
      <td><a href="{{ route('companies.show', [$company->id]) }}">{{ $company->name }} </a></td>
      <td><a class="btn btn-default" href="{{ route('companies.edit', [$company->id]) }}" role="button">Endre</a></td>
      <td>
        {!! Form::open(array('route' => array('companies.destroy', $company->id), 'method' => 'delete', 'onsubmit' => 'return ConfirmDelete()')) !!}
        <button type="submit" class="btn btn-danger">Slett</button>
        {!! Form::close() !!}
      </td>
    </tr>

    @endforeach
  </table>
  @stop