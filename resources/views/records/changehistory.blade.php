@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-10">


    <h3>Historikk for notatet "{{$parser->parse($record->title)}}" (ID {{$record->id}}), for klient <a href="{{ route('clients.show', $client->id) }}">{{$client->firstname}} {{$client->lastname}}</a></h3>

        @if ($record->oldid !== 0)
            <hr/>
            Dette notatet (ID {{$record->id}}) er overført fra en eldre database 29.11.2015. <br/>
            Alle hendelser (lesing, endring, signering) er logget siden notatet ble opprettet, mens versjonshistorikk er lagret for perioden etter
            overføringen. <br/>
            Under vises først grunnleggende informasjon om notatet, deretter eventuelle endrede versjoner siden 29.11.2015. <br/>
            <hr/>
        @endif

        Notatet ble opprettet av {{$record->user->name}}. <br/>
    Notatet ble opprettet {{$record->created_at->format('d-m-Y, H:i ')}} <br/>
    Notatet ble sist oppdatert {{$record->updated_at->format('d-m-Y, H:i')}} av {{$record->user->name}}
        @if ($record->updated_at == $record->signed_date)
            (signering)
        @endif


    <hr/>

<h3>Se tidligere versjoner</h3>
        <br/>
        Det har vært totalt {{count($earlierversions)}} tidligere versjoner av dette notatet.

    <table class="table">
        <tr>
            <th>Skrevet / Endret av</th>
            <th>Tidspunkt</th>
        </tr>

        @foreach ($earlierversions as $version)
        <tr>
            <td>{{$version->user->name}}</td>
            <td><a href="{{ route('clients.records.changehistoryversion', [$client->id, $record->id, $version->id]) }}">
                    {{$version->timestamp->format('d-m-Y, H:i')}}</a></td>
        </tr>

            @endforeach
    </table>

@stop