@extends('layout')

@section('content')

<h1>
  <a href="{{route('users.index')}}">{{ __('Active users') }}</a>
  -
  <a href="{{route('users.inactive_index')}}">{{ __('Inactive users') }}</a>

</h1>
<table class="table">
  <tr>
    <th>{{ __('Navn') }}</th>
    <th>{{ __('Firma') }}</th>
    <th>{{ __('Rolle') }}</th>
    <th>{{ __('Email') }}</th>
    <th>{{ __('Telefon') }}</th>
    <th>{{ __('2FA') }}</th>
    <th>{{ __('Betalingsadvarsel') }}</th>
    <th>{{ __('Låst') }}</th>
    <th>{{ __('Aktivering') }}</th>
  </tr>
  @foreach ($users as $user)
  <tr>
    <td>
      <a href="{{route('companies.users.edit', [$user->company ? $user->company->id : 0, $user->id])}}">{{$user->name}}</a>
    </td>
    <td>{{ $user->company ? $user->company->name : "" }}</td>
    <td>{{ $user->role }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->phone }}</td>
    <td>
      @if (Auth::user()->role === 2)
        {{ Form::open(array('route' => array('companies.users.change_two_factor', $user->company ? $user->company->id : 0, $user->id), 'method' => 'POST')) }}
          @if ($user->tfa == 0)
            <button type="submit" class="btn btn-danger">{{ __('SLÅ PÅ') }}</button>
          @elseif ($user->tfa == 1)
            <button type="submit" class="btn btn-success">{{ __('SLÅ AV') }}</button>
          @endif
        {!! Form::close() !!}
      @endif
    </td>

    <td>
      {!! Form::open(array('route' => array('companies.users.payment_warning', $user->company ? $user->company->id : 0, $user->id), 'method' => 'POST')) !!}
        @if (!$user->payment_missing)
          <button type="submit" class="btn btn-default">{{ __(' Advar') }}</button>
        @else
          <button type="submit" class="btn btn-warning">{{ $user->payment_missing }}</button>
        @endif
      {!! Form::close() !!}
    </td>

    <td>
      {!! Form::open(array('route' => array('companies.users.suspend_user', $user->company ? $user->company->id : 0, $user->id), 'method' => 'POST')) !!}
        @if (!$user->suspended)
          <button type="submit" class="btn btn-default">{{ __(' Lås') }}</button>
        @else
          <button type="submit" class="btn btn-warning">{{ $user->suspended }}</button>
        @endif
      {!! Form::close() !!}
    </td>

    <td>
      {!! Form::open(array('route' => array('companies.users.activate_toggle', $user->company ? $user->company->id : 0, $user->id), 'method' => 'POST')) !!}
        @if ($user->active == 0)
          <button type="submit" class="btn btn-default">{{ __(' Activate') }}</button>
        @else
          <button type="submit" class="btn btn-warning">{{ __(' Inactivate') }}</button>
        @endif
      {!! Form::close() !!}
    </td>
  </tr>
  @endforeach
</table>
<hr />

@stop
