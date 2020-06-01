@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-10">

    <h4>Klient: <a href="{{ route('clients.records.index', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a>
        - Endringer gjort {{$record->timestamp->format('d-m-Y, H:i')}} av {{$record->user->name}}
        </h4>

    <hr/>
    Tidligere avtaledato: {{$record->formerapp_date->format('d-m-Y')}} <br/>
    Ny avtaledato: {{$record->newapp_date->format('d-m-Y')}} <br/>
    <hr/>
    Tidligere tittel: {{$record->formertitle}}<br/>
    Ny tittel: {{$record->newtitle}}
    <hr/>
    Tidligere innhold: <br/>
    {!! $parser->parse($record->formercontent) !!} <br/>
    <hr/>
    Nytt innhold: <br/>
    {!! $parser->parse($record->newcontent) !!}

@stop