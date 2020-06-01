@extends('layout')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-md-4 col-md-offset-4">
                <h1 class="text-center login-title">Innlogging</h1>
                <hr/>

    {!! Form::open(array('route' => 'loginusers')) !!}

        <div>
            <label for="email">Brukernavn</label>
            <input type="text" name="email" class="form-control" value="{{ old('email') }}">
        </div>
                <hr/>
        <div>
            <label for="password">Passord</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
                <hr/>
        <div>
            <button type="submit" class="btn btn-lg btn-primary btn-block">Logg inn</button>
        </div>

    {!! Form::close() !!}

                </div>
        </div>
    </div>

    </div>

@stop