@extends('layout')

@section('content')

@include('partials.companySidebar')
<div class="col-md-10">

  <table class="table">
    <caption>Ansatte</caption>
    <tr>
      <th>Navn</th>
      <th>Email</th>
      <th>Telefon</th>
      <th>2FA</th>

      @if (Auth::user()->role === 2)
      <th>Betalingsadvarsel</th>
      <th>Lås</th>
      @endif

    </tr>
    @foreach ($company->user as $user)
    <tr>
      <td>
        @if (Auth::user()->role === 2)
          <a href="{{route('companies.users.edit', [$company->id, $user->id])}}">{{ $user->name }}</a>
        @elseif (Auth::user()->role === 1)
          {{ $user->name }}
        @endif
      </td>
      <td>{{ $user->email }}</td>
      <td>{{ $user->phone }}</td>
      <td>
        @if (Auth::user()->role === 2)
          {!! Form::open(array('route' => array('companies.users.change_two_factor', $user->company->id, $user->id), 'method' => 'POST')) !!}
            @if ($user->tfa == 0)
              <button type="submit" class="btn btn-danger">SLÅ PÅ</button>
            @elseif ($user->tfa == 1)
              <button type="submit" class="btn btn-success">SLÅ PÅ</button>
            @endif
          {!! Form::close() !!}
        @endif

        @if (Auth::user()->role === 1)
          @if ($user->tfa === 0)
            AV
          @elseif ($user->tfa === 1)
            PÅ
          @endif
        @endif
      </td>

      @if (Auth::user()->role === 2)
        <td>
          {!! Form::open(array('route' => array('companies.users.payment_warning', $user->company->id, $user->id), 'method' => 'POST')) !!}
            @if (!$user->payment_missing)
              <button type="submit" class="btn btn-default"> Advar</button>
            @else
              <button type="submit" class="btn btn-warning"> {{ $user->payment_missing }}</button>
            @endif
          {!! Form::close() !!}
        </td>

        <td>
          {!! Form::open(array('route' => array('companies.users.suspend_user', $user->company->id, $user->id), 'method' => 'POST')) !!}
            @if (!$user->suspended)
              <button type="submit" class="btn btn-default"> Lås</button>
            @elseif ($user->suspended)
              <button type="submit" class="btn btn-warning">{{ $user->suspended }}</button>
            @endif
          {!! Form::close() !!}
        </td>
      @endif
    </tr>
    @endforeach
  </table>

  <hr />
  Det er totalt {{count($company->user)}} brukerprofiler tilknyttet dette firmaet. <br />
  Abonnementet tillater registrering av ytterligere {{$company->seats - (count($company->user))}}.

  @stop