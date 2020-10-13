@extends('layout')

@section('content')

@include('partials.usersidebar')

<div class="col-md-10 col-md-offset-3">
  <h3>{{ __('Logg over innloggingsforsøk : ') }} {{ $user->name }}</h3>
  <hr />

  <div class="row">

    <table class="table">
      <caption>1) {{ __('Siste vellykkede innlogginger') }}</caption>
      <tr>
        <th>{{ __('Tidspunkt') }}</th>
        <th>{{ __('IP') }}</th>
      </tr>
      @foreach ($logins as $login)
      <tr>
        <td>{{ $login->created_at->format('d/m/Y - H:i:s') }}</td>
        <td>{{ $login->ip }}</td>
      </tr>
      @endforeach
    </table>

    <hr />

    <table class="table">
      <caption>2) {{ __('Siste innloggingsforsøk med riktig brukernavn, men feil passord') }}</caption>
      <tr>
        <th>{{ __('Tidspunkt') }}</th>
        <th>{{ __('IP') }}</th>
      </tr>
      @if (!count($wrongPassword))
      <tr>
        <td colspan="2">{{ __('Dette har ikke forekommet på din konto') }}</td>
      </tr>
      @else

      @foreach ($wrongPassword as $attempt)
      <tr>
        <td>{{ $attempt->created_at->format('d/m/Y - H:i:s') }}</td>
        <td>{{ $attempt->ip }}</td>
      </tr>
      @endforeach

      @endif
    </table>

    <hr />

    <table class="table">
      <caption>3) {{ __('Siste innloggingsforsøk med feil sms/app kode.') }}</caption>

      <tr>
        <th>{{ __('Tidspunkt') }}</th>
        <th>{{ __('IP') }}</th>
      </tr>
      @if (!count($crackedPassword))
      <tr>
        <td colspan="2">{{ __('Dette har ikke forekommet på din konto') }}</td>
      </tr>
      @else

      @foreach ($crackedPassword as $attempt)
      <tr>
        <td>{{ $attempt->created_at->format('d/m/Y - H:i:s') }}</td>
        <td>{{ $attempt->ip }}</td>
      </tr>
      @endforeach

      @endif
    </table>

  </div>
</div>

@stop