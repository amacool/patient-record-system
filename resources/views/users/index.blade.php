@extends('layout')

@section('content')

    <h1>
        <a href="{{route('users.index')}}">Active users</a>
        -
        <a href="{{route('users.inactiveindex')}}">Inactive users</a>

    </h1>
    <table class="table">
        <tr>
            <th>Navn</th>
            <th>Firma</th>
            <th>Rolle</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>2FA</th>
            <th>Betalingsadvarsel</th>
            <th>Låst</th>
            <th>Aktivering</th>
        </tr>
        @foreach ($users as $user)
        <tr>
            <td>
                <a href="{{route('companies.users.edit', [$user->company->id, $user->id])}}">{{$user->name}}</a>
            </td>
            <td>{{$user->company->name}}</td>
            <td>{{$user->role}}</td>
            <td>{{$user->email}}</td>
            <td>{{$user->phone}}</td>
            <td>
                @if (\Auth::user()->role == 2)

          {!! Form::open(array('route' => array('companies.users.changetwofactor', $user->company->id, $user->id), 'method' => 'POST')) !!}
        <button type="submit"

        @if ($user->tfa == 0)
                class="btn btn-danger">SLÅ PÅ</button>
                @endif

                    @if ($user->tfa == 1)
                        class="btn btn-success">SLÅ AV</button>
                    @endif

                    {!! Form::close() !!}

                @endif
            </td>

            <td>
                {!! Form::open(array('route' => array('companies.users.paymentwarning', $user->company->id, $user->id), 'method' => 'POST')) !!}
                <button type="submit"

                    @if ($user->paymentmissing == "0000-00-00")
                        class="btn btn-default"> Advar
                        @endif

                    @if ($user->paymentmissing !== "0000-00-00")
                        class="btn btn-warning"> {{$user->paymentmissing}}
                    @endif

                </button>
                {!! Form::close() !!}

            </td>

            <td>
                {!! Form::open(array('route' => array('companies.users.suspenduser', $user->company->id, $user->id), 'method' => 'POST')) !!}
                <button type="submit"

                @if ($user->suspended == "0000-00-00")
                        class="btn btn-default"> Lås
                    @endif

                    @if ($user->suspended !== "0000-00-00")
                        class="btn btn-warning"> {{$user->suspended}}
                    @endif

                </button>
                {!! Form::close() !!}

            </td>

            <td>
                {!! Form::open(array('route' => array('companies.users.activatetoggle', $user->company->id, $user->id), 'method' => 'POST')) !!}
                <button type="submit"

                        @if ($user->active == 0)
                        class="btn btn-default"> Activate
                    @endif

                    @if ($user->active == 1)
                        class="btn btn-warning"> Inactivate
                    @endif

                </button>
                {!! Form::close() !!}

            </td>
        </tr>
            @endforeach
    </table>

    <hr/>


@stop