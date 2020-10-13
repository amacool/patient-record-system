@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Liste over opplastede filer for klient {{$client->firstname}} {{$client->lastname}}
    <a class="btn btn-default btn-sm pull-right" href="{{ route('clients.files.create', [$client->id]) }}" role="button">LAST OPP</a>
  </h4>
  <hr />

  <table class="table">
    <tr>
      <th>Beskrivelse</th>
      <th>Lastet opp av</th>
      <th>Last ned</th>
    </tr>
    @foreach ($client->files as $file)

    <tr>
      <td>{{$file->description}}</td>
      <td>{{$file->user->name}}</td>
      <td><a href="{{ route('clients.files.download', [$client->id, $file->file]) }}" target="_blank">Download</a></td>
      <td></td>
    </tr>

    @endforeach
  </table>

  @stop
