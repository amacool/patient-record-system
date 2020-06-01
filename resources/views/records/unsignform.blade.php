@extends('layout')

@section('content')

    @include('partials.clientsidebar')

    <div class="col-md-8 col-md-offset-3">

    <h4>Lås opp notatet med ID {{$record->id}}?</h4>

    <section>
        <div class="container">

    {!! Form::open(array('route' => 'clients.records.unsignformpost')) !!}

            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::hidden('client_id', $client->id) !!}
            {!! Form::hidden('record_id', $record->id) !!}

            <div class="col-md-8">
                <div class="form-group">
                    {!! Form::label('reason', 'Du er i ferd med å låse opp notatet slik at det igjen kan endres.
                    Hva er årsaken til at du låser det opp? ') !!}
                    {!! Form::textarea('reason', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-12">
                {!! Form::submit('Lås opp') !!}
            </div>

    {!! Form::close() !!}

        </div>
    </section>

@stop