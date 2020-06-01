@extends('layout')

@section('content')

    @include('partials.usersidebar')

    <div class="col-md-9 col-md-offset-3">

        <h4>Logg over tilgangsstyring for {{$user->name}}</h4>

        Tilganger er i dette tilfellet definert som en rettighet man gir eller får knyttet til enkeltklienter.
        Rettighetene innebærer å lese all informasjon om pasienten, samt å ha tilgang til å opprette nye notater og signere / endre disse.
        Det er ved slike tilganger ikke anledning til å gi tilganger videre til andre, og det er heller ikke anledning til å endre notater skrevet av andre.
        <hr/>

        <h5>Tilganger gitt til andre</h5>
        <table class="table">
            <th>Gitt av</th>
            <th>Gitt til</th>
            <th>Klient</th>
            <th>Årsak</th>
            <th>Tidspunkt</th>
            @if (!count($user->gaveaccess))
                <tr>
                    <td colspan="5">Denne brukeren har ikke gitt tilganger til andre brukere</td>
                </tr>
            @else

                @foreach ($user->gaveaccess as $ar)
                    <tr>
                        <td>@if ($ar->givenby !== null){{$ar->givenby->name}}@endif</td>
                        <td>{{$ar->user->name}}</td>
                        <td>{{Crypt::decrypt($ar->clients->firstname)}} {{Crypt::decrypt($ar->clients->lastname)}}</td>
                        <td>{{$ar->reason}}</td>
                        <td>{{$ar->datetime}}</td>
                    </tr>
                @endforeach
            @endif
        </table>

        <hr/>


        <h5>Tilganger til andre som har blitt tilbaketrukket</h5>
        <table class="table">
            <tr>
                <th>Tilbaketrukket av</th>
                <th>Annen bruker</th>
                <th>Klient</th>
                <th>Årsak</th>
                <th>Tidspunkt</th>
            </tr>

            @if (!count($user->revokedaccess))
                <tr>
                    <td colspan="5">Denne brukeren har ikke gitt tilganger til andre brukere</td>
                </tr>
            @else

                @foreach ($user->revokedaccess as $ar)
                    <tr>
                        <td>@if ($ar->revokedby !== null){{$ar->revokedby->name}}@endif</td>
                        <td>{{$ar->user->name}}</td>
                        <td>{{Crypt::decrypt($ar->clients->firstname)}} {{Crypt::decrypt($ar->clients->lastname)}}</td>
                        <td>{{$ar->reason}}</td>
                        <td>{{$ar->datetime}}</td>
                    </tr>
                @endforeach
            @endif
        </table>


        <hr/>


        <h5>Tilganger mottatt fra andre</h5>
        <table class="table">
            <tr>
                <th>Gitt av</th>
                <th>Gitt til</th>
                <th>Klient</th>
                <th>Årsak</th>
                <th>Tidspunkt</th>
            </tr>

            @if (!count($user->givenaccess))
                <tr>
                    <td colspan="5">Denne brukeren har ikke mottatt tilganger fra andre brukere</td>
                </tr>
            @else

                @foreach ($user->givenaccess as $ar)
                    @unless ($ar->revoked_by !== null)
                    <tr>
                        <td>@if ($ar->givenby !== null){{$ar->givenby->name}}@endif</td>
                        <td>{{$ar->user->name}}</td>
                        <td>{{Crypt::decrypt($ar->clients->firstname)}} {{Crypt::decrypt($ar->clients->lastname)}}</td>
                        <td>{{$ar->reason}}</td>
                        <td>{{$ar->datetime}}</td>
                    </tr>
                    @endunless
                @endforeach
            @endif
        </table>

        <hr/>


        <h5>Tilganger fra andre som har blitt trukket tilbake</h5>
        <table class="table">
            <tr>
                <th>Tilbaketrukket av</th>
                <th>Bruker</th>
                <th>Klient</th>
                <th>Årsak</th>
                <th>Tidspunkt</th>
            </tr>

            @if (!count($user->givenaccess))
                <tr>
                    <td colspan="5">Denne brukeren har ikke mottatt tilganger fra andre brukere</td>
                </tr>
            @else

                @foreach ($user->givenaccess as $ar)
                    @unless ($ar->given_by !== null)
                        <tr>
                            <td>@if ($ar->revoked_by !== null){{$ar->revokedby->name}}@endif</td>
                            <td>{{$ar->user->name}}</td>
                            <td>{{Crypt::decrypt($ar->clients->firstname)}} {{Crypt::decrypt($ar->clients->lastname)}}</td>
                            <td>{{$ar->reason}}</td>
                            <td>{{$ar->datetime}}</td>
                        </tr>
                    @endunless
                @endforeach
            @endif
        </table>
        <br/>
        <br/>
        <hr/>
        <h4>Logg for klientoverføringer for {{$user->name}}</h4>

        Klientoverføringer er i dette tilfellet definert som en total overføring av tilganger til en annen bruker.
        Det betyr at man ved en overføring ikke lengre har tilgang til klientdata selv i etterkant av overføringen.
        <hr/>

        <h5>Overføringer av klienter til andre</h5>
        <table class="table">
            <th>Overført av</th>
            <th>Overført til</th>
            <th>Klient</th>
            <th>Årsak</th>
            <th>Tidspunkt</th>
            @if (!count($user->transferredclient))
                <tr>
                    <td colspan="4">Brukeren har ikke overført klienter til andre</td>
                </tr>
            @else

                @foreach ($user->transferredclient as $tl)
                    <tr>
                        <td>{{$tl->transferredby->name}}</td>
                        <td>{{$tl->transferredto->name}}</td>
                        <td>{{Crypt::decrypt($tl->clients->firstname)}} {{Crypt::decrypt($tl->clients->lastname)}}</td>
                        <td>{{$tl->reason}}</td>
                        <td>{{$tl->datetime}}</td>
                    </tr>
                @endforeach
            @endif
        </table>

        <hr/>

        <h5>Overføringer der brukeren har mottatt klienter fra andre</h5>
        <table class="table">
            <th>Overført av</th>
            <th>Overført til</th>
            <th>Klient</th>
            <th>Årsak</th>
            <th>Tidspunkt</th>
            @if (!count($user->receivedclient))
                <tr>
                    <td colspan="4">Brukeren har ikke fått klienter overført fra andre</td>
                </tr>
            @else

                @foreach ($user->receivedclient as $tl)
                    <tr>
                        <td>{{$tl->transferredby->name}}</td>
                        <td>{{$tl->transferredto->name}}</td>
                        <td>{{Crypt::decrypt($tl->clients->firstname)}} {{Crypt::decrypt($tl->clients->lastname)}}</td>
                        <td>{{$tl->reason}}</td>
                        <td>{{$tl->datetime}}</td>
                    </tr>
                @endforeach
            @endif
        </table>

        <hr/>
    </div>


@stop