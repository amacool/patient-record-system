@extends('layout')

@section('content')

    @include('partials.usersidebar')
    <div class="col-md-10 col-md-offset-3">

    <h3>Logg over innloggingsforsøk : {{$user->name}}</h3>
    <hr/>


    <div class="row">

            <table class="table">
                <caption>1) Siste vellykkede innlogginger</caption>
                <tr>
                    <th>Tidspunkt</th>
                    <th>IP</th>
                </tr>
                @foreach ($logins as $login)
                <tr>
                    <td>{{$login->created_at->format('d/m/Y - H:i:s')}}</td>
                    <td>{{$login->ip}}</td>
                </tr>
                    @endforeach
            </table>

            <hr/>


            <table class="table">
                <caption>2) Siste innloggingsforsøk med riktig brukernavn, men feil passord</caption>
                <tr>
                    <th>Tidspunkt</th>
                    <th>IP</th>
                </tr>
                @if (!count($wrongpassword))
                    <tr>
                        <td colspan="2">Dette har ikke forekommet på din konto</td>
                    </tr>
                @else

                @foreach ($wrongpassword as $attempt)
                    <tr>
                        <td>{{$attempt->created_at->format('d/m/Y - H:i:s')}}</td>
                        <td>{{$login->ip}}</td>
                    </tr>
                @endforeach

                    @endif
            </table>

            <hr/>


            <table class="table">
                <caption>3) Siste innloggingsforsøk med feil sms/app kode.</caption>

                <tr>
                    <th>Tidspunkt</th>
                    <th>IP</th>
                </tr>
                @if (!count($crackedpassword))
                    <tr>
                    <td colspan="2">Dette har ikke forekommet på din konto</td>
                    </tr>
                @else

                @foreach ($crackedpassword as $attempt)
                    <tr>
                        <td>{{$attempt->created_at->format('d/m/Y - H:i:s')}}</td>
                        <td>{{$login->ip}}</td>
                    </tr>
                @endforeach

                    @endif
            </table>
</div>

@stop