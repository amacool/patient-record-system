<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <!-- No indexing from search engines -->
    <meta name="robots" content="noindex, nofollow">

    <title>PBJS</title>

    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    {!! HTML::script('js/ckeditor/ckeditor.js') !!}

    <!-- jQuery js -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

    <!-- Latest compiled and minified Bootstrap js -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>

    <!-- Authy js -->
    <link href="https://www.authy.com/form.authy.min.css" media="screen" rel="stylesheet" type="text/css">
    <script src="https://www.authy.com/form.authy.min.js" type="text/javascript"></script>

</head>
<body>

    <div class="col-md-10 col-md-offset-1">

        <div class="panel panel-default">
            <div class="panel-heading">

                "{{$record->title}}", forfatter {{$record->user->name}} (opprettet {{$record->created_at->format('d/m/Y')}}).

                    <span class="pull-right">

                        Avtaledato
                        @if (($record->app_date->format('d/m/Y')) == "30/11/-0001")
                            ikke angitt
                        @else
                            {{$record->app_date->format('d/m/Y')}}
                        @endif

                    </span>

                <br/>Notatet ble sist oppdatert {{$record->updated_at->format('d/m/Y')}}
                @if ($record->updated_at == $record->signed_date)
                    (signering)
                @endif

                <span class="pull-right">
                    @if ($record->signed_by == null)
                        Ikke signert
                    @endif

                    @if ($record->signed_by !== null)
                        Signert {{$record->signed_date->format('d/m/Y')}} av {{$record->user->name}}
                    @endif
                        </span>
                <br/>
                Klient: {{Crypt::decrypt($record->client->firstname)}} {{Crypt::decrypt($record->client->lastname)}} (født {{$record->client->born->format('d-m-Y')}})
            </div>
            <div class="panel-body">{!! $record->content !!}</div>
        </div>
    </div>

</body>
</html>