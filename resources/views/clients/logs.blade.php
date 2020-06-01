@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-9 col-md-offset-3">

        <h4>Loggede hendelser for {{$client->firstname}} {{$client->lastname}}</h4>
        <hr/>

        <h5>Tilganger gitt og tilbaketrukket</h5>
        <table class="table">
            <th>Gitt av</th>
            <th>Tilbaketrukket av</th>
            <th>Bruker</th>
            <th>Årsak<th>
            <th>Tidspunkt</th>
            @if (!count($client->accessrights))
                <tr>
                    <td colspan="5">Ingen tilganger er gitt eller tilbaketrukket for denne klienten</td>
                </tr>
            @else

        @foreach ($client->accessrights as $ar)
<tr>
            <td>@if ($ar->givenby !== null){{$ar->givenby->name}}@endif</td>
            <td>@if ($ar->revokedby !== null){{$ar->revokedby->name}}@endif</td>
            <td>{{$ar->user->name}}<td>
            <td>{{$ar->reason}}<td>
            <td>{{$ar->datetime}}</td>
</tr>
            @endforeach
                @endif
        </table>

        <hr/>

        <h5>Overføringer knyttet til pasienten</h5>
        <table class="table">
            <th>Overført av</th>
            <th>Overført til</th>
            <th>Årsak<th>
            <th>Tidspunkt</th>
            @if (!count($client->transfers))
                <tr>
                    <td colspan="4">Ingen overføringer er gjort for denne klienten</td>
                </tr>
            @else

            @foreach ($client->transfers as $tl)
                <tr>
                    <td>{{$tl->transferredby->name}}</td>
                    <td>{{$tl->transferredto->name}}</td>
                    <td>{{$tl->reason}}<td>
                    <td>{{$tl->datetime}}</td>
                </tr>
            @endforeach
                @endif
        </table>

        <hr/>

        <h5>Notater lest</h5>
        <table class="table">
            <th>Lest av</th>
            <th>Notat ID</th>
            <th>Åpnet</th>

            @foreach ($client->readrecords as $rr)
                @unless ($rr->user->id == 1)
                <tr>
                    <td>{{$rr->user->name}}</td>
                    <td>{{$rr->record_id}}</td>
                    <td>{{$rr->timestamp}}</td>
                </tr>
                @endunless
            @endforeach
        </table>

        <hr/>

        <hr/>

        <h5>Notater endret</h5>
        <table class="table">
            <th>Endret av</th>
            <th>Notat ID</th>
            <th>Utført</th>
            @if (!count($client->changedrecords))
                <tr>
                    <td colspan="3">Ingen notater er redigert etter første lagring for denne klienten</td>
                </tr>
            @else

            @foreach ($client->changedrecords as $cr)
                <tr>
                    <td>{{$cr->user->name}}</td>
                    <td>{{$cr->record_id}}</td>
                    <td>{{$cr->timestamp}}</td>
                </tr>
            @endforeach
                @endif
        </table>

        <hr/>

        <h5>Signeringer og avsigneringer</h5>
        <table class="table">
            <th>Signert av</th>
            <th>Avsignert av</th>
            <th>Årsak til avsignering<th>
            <th>Tidspunkt</th>
            @if (!count($client->signlogs))
                <tr>
                    <td colspan="4">Ingen notater er signert eller avsignert for denne klienten</td>
                </tr>
            @else

            @foreach ($client->signlogs as $sl)
                <tr>
                    <td>@if ($sl->signed_by !== null){{$sl->signedby->name}}@endif</td>
                    <td>@if ($sl->unsigned_by !== null){{$sl->unsignedby->name}}@endif</td>
                    <td>@if ($sl->reason !== null){{$sl->reason}}@endif<td>
                    <td>{{$sl->timestamp}}</td>
                </tr>
            @endforeach
                @endif
        </table>

        <hr/>



        </div>


@stop