@extends('layout')

@section('content')

    @include('partials.usersidebar')
    <div class="col-md-10 col-md-offset-3">

    <h3>Innstillinger for {{$user->name}}</h3>
    <hr/>

    <div class="row">
<h5>Endre passord:</h5>

            {!! Form::open(array('route' => array('companies.users.update', $company->id, $user->id), 'method' => 'PUT')) !!}

        <div class="col-md-4">
            <div class="form-group">
                Gammelt passord
                <input type="password" name="oldpassword">
            </div>
        </div>
</div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                Nytt passord
                <input type="password" name="password">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                Bekreft  nytt passord
                <input type="password" name="password_confirmation">
                </div>
            </div>

            <div class="col-md-1">
                <div class="form-group">
                {!! Form::submit('Lagre') !!}
                </div>
            </div>

    {!! Form::close() !!}

        </div>
        <hr/>
        <div class="row">
            <h5>Hemmelig spørsmål og svar:</h5>
            (Kan brukes som del av identifikasjonsprosess ved kontakt med administrator. <br/>
            Bruk informasjon om deg selv som ikke er enkelt tilgjengelig for andre. <br/>
            Spørsmål og svar vises ikke under, men er lagret i database dersom du har oppgitt det tidligere. <br/>
            Fyll inn på nytt for å oppdatere).
            <p>

            {!! Form::open(array('route' => array('companies.users.secretquestion', $company->id, $user->id))) !!}

            <div class="col-md-8">
                <div class="form-group">
                    {!! Form::label('secretquestion', 'Spørsmål: ') !!}
                    {!! Form::text('secretquestion', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8">
                <div class="form-group">
                    {!! Form::label('secretanswer', 'Svar: ') !!}
                    {!! Form::text('secretanswer', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::submit('Lagre') !!}
                </div>
            </div>

            {!! Form::close() !!}

        </div>
        <hr/>

        <div class="row">
            <h5>Legg inn en standardtittel på journalnotatene dine:</h5>

            {!! Form::model($user, array('route' => array('companies.users.standardtitle', $company->id, $user->id))) !!}

            <div class="col-md-8">
                <div class="form-group">
                    {!! Form::label('standardtitle', 'Tittel: ') !!}
                    {!! Form::text('standardtitle', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::submit('Lagre') !!}
                </div>
            </div>

            {!! Form::close() !!}

            </div>


        <div class="row">
            <hr/>
            <h5>Status for registrering hos Authy (To-faktor-autentisering)</h5>

            @if ($authystatus == null)
                Brukeren er ikke registrert hos authy, men er registrert med følgende telefonnummer på profilen: {{$user->phone}}, landskode {{$user->country_code}}
                    @if (\Auth::user()->role == 2)
                {!! Form::open(array('route' => array('companies.users.registerauthy', $company->id, $user->id))) !!}

                {!! Form::hidden('phone', $user->phone) !!}
                {!! Form::hidden('country_code', $user->country_code) !!}

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Registrer') !!}
                    </div>
                </div>

                {!! Form::close() !!}
                    @endif
                @endif
            @if ($authystatus !== null)
                Brukeren har følgende detaljer hos Authy: <br/>

                Authy-id = {{$authystatus->authy_id}} <br/>
                Bekreftet konto: {{$authystatus->confirmed}} <br/>
                Registrert: {{$authystatus->registered}} <br/>
                Landskode: {{$authystatus->country_code}} <br/>
                Telefon: {{$authystatus->phone_number}} <br/>
                Enheter:
                @foreach ($authystatus->devices as $device)
                    {{$device}} ,
                @endforeach


                <br/>

                @if (\Auth::user()->role == 2)

                {!! Form::open(array('route' => array('companies.users.deleteauthy', $company->id, $user->id))) !!}

                {!! Form::hidden('authy_id', $user->authy_id) !!}

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Slett hos Authy') !!}
                    </div>
                </div>

                {!! Form::close() !!}

                @endif

            @endif


        </div>
        @if (\Auth::user()->role == 2)
        <div class="row">

            <hr/>
                <h5>Hemmelig Spørsmål</h5>

                Spørsmål: @if ($user->secretquestion !== ''){{Crypt::decrypt($user->secretquestion)}}@endif <br/>
                Svar: @if ($user->secretanswer !== '') {{Crypt::decrypt($user->secretanswer)}} @endif

            <hr/>
            <h5>Bytt hvilket firma brukeren tilhører:</h5>

            {!! Form::model($user, array('route' => array('companies.users.changecompany', $company->id, $user->id))) !!}

            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::select('company_id', $companies, $user->company->id, ['id' => 'company_list', 'class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::submit('Lagre') !!}
                </div>
            </div>

            {!! Form::close() !!}

        </div>

            <div class="row">
                <hr/>
                <h5>Endre brukerens rolle:</h5>

                {!! Form::model($user, array('route' => array('companies.users.changerole', $company->id, $user->id))) !!}

                <div class="form-group">
                    {!! Form::label('roleradio', 'Rolle') !!}<p>
                    <div class="radio radio-success">
                        <label>
                            {!! Form::radio('role', '0') !!}
                            <span class="circle"></span><span class="check"></span>
                            Vanlig bruker
                        </label>
                    </div>
                    <div class="radio radio-success">
                        <label>
                            {!! Form::radio('role', '1') !!}
                            <span class="circle"></span><span class="check"></span>
                            Firmaadministrator
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Lagre') !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

            <div class="row">
                <hr/>
                <h5>Endre brukerens telefonnummer:</h5>

                {!! Form::model($user, array('route' => array('companies.users.changephone', $company->id, $user->id))) !!}

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('country_code', 'Landskode: ') !!}
                        {!! Form::text('country_code', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('phone', 'Telefon: ') !!}
                        {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Lagre') !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

            <div class="row">
                <hr/>
                <h5>Transfer old records for a specific client</h5>

                {!! Form::model($user, array('route' => array('companies.users.transferclientrecords', $company->id, $user->id))) !!}


                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('client_id', 'Klient ID: ') !!}
                        {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Lagre') !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

            <div class="row">
                <hr/>
                <h5>Transfer signle old record for a specific client</h5>

                {!! Form::model($user, array('route' => array('companies.users.transfersinglewprecord', $company->id, $user->id))) !!}

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('wprecordid', 'WP record: ') !!}
                        {!! Form::text('wprecordid', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::submit('Lagre') !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

            <div>
                {!! Form::open(array('route' => array('companies.users.delete', $user->company->id, $user->id), 'method' => 'POST')) !!}
                <button type="submit"


                        class="btn btn-danger" onclick="return confirm('Er du sikker?')">SLETT DENNE BRUKEREN</button>


                {!! Form::close() !!}
            </div>


        @endif







    </div>
    </div>

@stop