@extends('layout')

@section('content')

@include('partials.usersidebar')
<div class="col-md-10 col-md-offset-3">

  <h3>{{ __('Innstillinger for ') }} {{ $user->name }}</h3>
  <hr />

  <div class="row">
    <h5>{{ __('Endre passord:') }}</h5>

    {!! Form::open(array('route' => array('companies.users.update', $company ? $company->id : 0, $user->id), 'method' => 'PUT')) !!}
      <div class="form-group">
        <span>{{ __('Gammelt passord') }}</span>
        <input type="password" name="old_password">
      </div>
      <div class="form-group">
        <span>{{ __('Nytt passord') }}</span>
        <input type="password" name="password">
      </div>
      <div class="form-group">
        <span>{{ __('Bekreft nytt passord') }}</span>
        <input type="password" name="password_confirmation">
      </div>
      <div class="form-group">
        {!! Form::submit('Lagre') !!}
      </div>
    {!! Form::close() !!}
  </div>

  <hr />

  <div class="row">
    <h5>Hemmelig spørsmål og svar:</h5>
    (Kan brukes som del av identifikasjonsprosess ved kontakt med administrator. <br />
    Bruk informasjon om deg selv som ikke er enkelt tilgjengelig for andre. <br />
    Spørsmål og svar vises ikke under, men er lagret i database dersom du har oppgitt det tidligere. <br />
    Fyll inn på nytt for å oppdatere).
    <p>

    {!! Form::open(array('route' => array('companies.users.secret_question', $company ? $company->id : 0, $user->id))) !!}
      <div class="col-md-8">
        <div class="form-group">
          {!! Form::label('secret_question', 'Spørsmål: ') !!}
          {!! Form::text('secret_question', null, ['class' => 'form-control']) !!}
        </div>
      </div>

      <div class="col-md-8">
        <div class="form-group">
          {!! Form::label('secret_answer', 'Svar: ') !!}
          {!! Form::text('secret_answer', null, ['class' => 'form-control']) !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::submit('Lagre') !!}
        </div>
      </div>
    {!! Form::close() !!}
  </div>

  <hr />

  <div class="row">
    <h5>Legg inn en standardtittel på journalnotatene dine:</h5>

    {!! Form::model($user, array('route' => array('companies.users.standard_title', $company ? $company->id : 0, $user->id))) !!}
      <div class="col-md-8">
        <div class="form-group">
          {!! Form::label('standard_title', 'Tittel: ') !!}
          {!! Form::text('standard_title', null, ['class' => 'form-control']) !!}
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
    <hr />
    <h5>{{ __('Status for registrering hos Authy (To-faktor-autentisering)') }}</h5>

    @if ($authyStatus == null)
      Brukeren er ikke registrert hos authy, men er registrert med følgende telefonnummer på profilen: {{$user->phone}}, landskode {{$user->country_code}}
      
      @if (Auth::user()->role === 2)
        {!! Form::open(array('route' => array('companies.users.register_authy', $company ? $company->id : 0, $user->id))) !!}
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
    
    @if ($authyStatus !== null)
      Brukeren har følgende detaljer hos Authy: <br />
      Authy-id = {{ $authyStatus->authy_id }} <br />
      Bekreftet konto: {{ $authyStatus->confirmed }} <br />
      Registrert: {{ $authyStatus->registered }} <br />
      Landskode: {{ $authyStatus->country_code }} <br />
      Telefon: {{ $authyStatus->phone_number }} <br />
      Enheter:
      @foreach ($authyStatus->devices as $device)
        {{ $device }} ,
      @endforeach

      <br />

      @if (Auth::user()->role === 2)
        {!! Form::open(array('route' => array('companies.users.delete_authy', $company ? $company->id : 0, $user->id))) !!}
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

  @if (Auth::user()->role === 2)
    <div class="row">
      <hr />
      <h5>{{ __('Hemmelig Spørsmål') }}</h5>

      Spørsmål: @if ($user->secret_question !== '') {{ Crypt::decrypt($user->secret_question) }} @endif <br />
      Svar: @if ($user->secret_answer !== '') {{ Crypt::decrypt($user->secret_answer) }} @endif

      <hr />
      <h5>{{ __('Bytt hvilket firma brukeren tilhører:') }}</h5>

      {!! Form::model($user, array('route' => array('companies.users.change_company', $company ? $company->id : 0, $user->id))) !!}
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::select('company_id', $companyPairs, $company ? $company->id : 0, ['id' => 'company_list', 'class' => 'form-control']) !!}
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
      <hr />
      <h5>{{ __('Endre brukerens rolle:') }}</h5>

      {!! Form::model($user, array('route' => array('companies.users.change_role', $company ? $company->id : 0, $user->id))) !!}
        <div class="form-group">
          {!! Form::label('roleradio', 'Rolle') !!}<p>
            <div class="radio radio-success">
              <label>
                {!! Form::radio('role', '0') !!}
                <span class="circle"></span><span class="check"></span>
                {{ __('Vanlig bruker') }}
              </label>
            </div>
            <div class="radio radio-success">
              <label>
                {!! Form::radio('role', '1') !!}
                <span class="circle"></span><span class="check"></span>
                {{ __('Firmaadministrator') }}
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
      <hr />
      <h5>{{ __('Endre brukerens telefonnummer:') }}</h5>

      {!! Form::model($user, array('route' => array('companies.users.change_phone', $company ? $company->id : 0, $user->id))) !!}
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
      <hr />
      <h5>{{ __('Transfer old records for a specific client') }}</h5>

      {!! Form::model($user, array('route' => array('companies.users.transfer_client_records', $company ? $company->id : 0, $user->id))) !!}
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
      <hr />
      <h5>{{ __('Transfer signle old record for a specific client') }}</h5>

      {!! Form::model($user, array('route' => array('companies.users.transfer_single_wprecord', $company ? $company->id : 0, $user->id))) !!}
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
      {!! Form::open(array('route' => array('companies.users.delete', $company ? $company->id : 0, $user->id), 'method' => 'POST')) !!}
        <button type="submit" class="btn btn-danger" onclick="return confirm('Er du sikker?')">SLETT DENNE BRUKEREN</button>
      {!! Form::close() !!}
    </div>
  @endif
</div>

@stop
