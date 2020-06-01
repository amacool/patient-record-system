@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-9 col-md-offset-3">

    <table class="table">
        <tr>
            <th>Overskrift</th>
            <th>Innhold</th>
        </tr>

        <tr>
            <td>Fornavn</td>
            <td>{{$client->firstname}}</td>
        </tr>
        <tr>
            <td>Etternavn</td>
            <td>{{$client->lastname}}</td>
        </tr>
        <tr>
            <td>Født</td>
                <td>{{$client->born->format('d-m-Y')}}</td>
        </tr>
        <tr>
            <td>Fødselsnummer</td>
            <td>{{$client->ssn}}</td>
        </tr>
        <tr>
            <td>Sivilstatus</td>
            <td>{{$client->civil_status}}</td>
        </tr>
        <tr>
            <td>Arbeidsstatus</td>
            <td>{{$client->work_status}}</td>
        </tr>
        <tr>
            <td>Medisiner</td>
            <td>{{$client->medication}}</td>
        </tr>
        <tr>
            <td>Gateadresse</td>
            <td>{{$client->street_address}}</td>
        </tr>
        <tr>
            <td>Postnummer</td>
            <td>{{$client->postal_code}}</td>
        </tr>
        <tr>
            <td>By</td>
            <td>{{$client->city}}</td>
        </tr>
        <tr>
            <td>Telefonnummer</td>
            <td>{{$client->phone}}</td>
        </tr>
        <tr>
            <td>Nærmeste pårørende</td>
            <td>{{$client->closest_relative}}</td>
        </tr>
        <tr>
            <td>Nærmeste pårørendes tlf-nr</td>
            <td>{{$client->closest_relative_phone}}</td>
        </tr>
        <tr>
            <td>Barn</td>
            <td>{{$client->children}}</td>
        </tr>
        <tr>
            <td>Fastlege</td>
            <td>{{$client->gp}}</td>
        </tr>
        {{--<tr>
            <td>Individuell Plan</td>
            <td>{{$client->individual_plan}}</td>
        </tr>--}}
        <tr>
            <td>Annen viktig informasjon</td>
            <td>{{$client->other_info}}</td>
        </tr>

    </table>
@stop