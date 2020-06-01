@extends('layout')

@section('content')



    @if (!\Auth::user())

        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-4 col-md-offset-4">
                    <h1 class="text-center login-title">Innlogging</h1>
                    <hr/>

                    {{--<div class="alert alert-danger" role="alert">
                        INFO: På grunn av arbeid på serveren vil nettsiden være utilgjengelig søndag 28. januar ca mellom kl 09.00 og 10.00.
                        Ikke benytt siden innenfor dette tidsrommet, ettersom du kan risikere å miste arbeidet du gjør.
                    </div>--}}

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


    @endif

    @if (\Auth::user())

        <h1>PsykJournal v2</h1>

        <br/>
        Du er logget inn som
        @if (\Auth::user()->role == 0) ordinær bruker @endif
        @if (\Auth::user()->role == 1) firmaadministrator for {{\Auth::user()->company->name}} @endif
        @if (\Auth::user()->role == 2) systemadministrator @endif


        <hr>

        <div class="alert alert-danger" role="alert">
                        04.07.18: PLANLAGT VEDLIKEHOLD: På grunn av arbeid på serveren vil journalsystemet være ustabilt
                        i ca 15 minutter fra torsdag 5.juli klokken 23.50. Det anbefales å ikke skrive notater i dette
                        tidsrommet, da du kan risikere at notatet ikke blir lagret.
        </div>

    @endif
@stop