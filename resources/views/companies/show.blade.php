@extends('layout')

@section('content')

    @include('partials.companysidebar')
    <div class="col-md-10">

    <table class="table">
        <caption>Ansatte</caption>
        <tr>
            <th>Navn</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>2FA</th>

            @if (\Auth::user()->role == 2)
                <th>Betalingsadvarsel</th>
                <th>Lås</th>
            @endif

        </tr>
        @foreach ($company->user as $user)
        <tr>
            <td>
                @if (\Auth::user()->role == 2)
                <a href="{{route('companies.users.edit', [$company->id, $user->id])}}">{{$user->name}}</a>
                @endif
                @if (\Auth::user()->role == 1)
                        {{$user->name}}
                @endif
            </td>
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

                @if (\Auth::user()->role == 1)

                    @if ($user->tfa == 0)
                        AV
                    @endif
                    @if ($user->tfa == 1)
                        PÅ
                    @endif

                @endif

            </td>

            @if (\Auth::user()->role == 2)

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

            @endif

        </tr>
            @endforeach
    </table>

    <hr/>
    Det er totalt {{count($company->user)}} brukerprofiler tilknyttet dette firmaet. <br/>
    Abonnementet tillater registrering av ytterligere {{$company->seats - (count($company->user))}}.

@stop