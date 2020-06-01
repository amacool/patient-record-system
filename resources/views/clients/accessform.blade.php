@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-9 col-md-offset-3">

    <h4>Gi bruker {{$user->name}} tilgang til klient {{$client->firstname}} {{$client->lastname}}</h4>

    <section>
        <div class="container">

    {!! Form::open(array('route' => 'clients.accessformpost')) !!}

            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::hidden('client_id', $client->id) !!}

            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('reason', 'Årsak til tilgangen: ') !!}
                    {!! Form::textarea('reason', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-12">
                {!! Form::submit('Gi tilgang') !!}
            </div>

    {!! Form::close() !!}

        </div>
    </section>

@stop