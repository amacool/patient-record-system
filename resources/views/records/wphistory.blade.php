@extends('layout')

@section('content')

<div class="col-md-10">

  {{-- <h4>Klient: <a href="{{ route('clients.records.index', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a>
  - Endringer gjort {{$record->timestamp->format('d-m-Y, H:i')}} by {{$record->user->name}}
  </h4>--}}

  <hr />
  @foreach ($revisions as $r)

  {{$r->post_date_gmt}} <br />
  {{$r->post_content}} <br />

  <hr />

  @endforeach
</div>

@stop