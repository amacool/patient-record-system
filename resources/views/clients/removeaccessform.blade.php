@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-9 col-md-offset-3">

    <h1>Fjern {{$user->name}} sin tilgang til klient {{$client->firstname}} {{$client->lastname}}</h1>

    {!! Form::open(array('route' => 'clients.removeaccessformpost')) !!}

            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::hidden('client_id', $client->id) !!}

            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('reason', 'Årsaken til at tilgangen inndras: ') !!}
                    {!! Form::textarea('reason', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-10">
                {!! Form::submit('Fjern tilgang') !!}
            </div>

    {!! Form::close() !!}

        </div>


@stop