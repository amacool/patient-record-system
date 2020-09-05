<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <!-- No indexing from search engines -->
  <meta name="robots" content="noindex, nofollow">

  <title>PBJS</title>

  <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
  <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>

  <!-- jQuery js -->
  {{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>--}}
  <script src="{{ asset('js/jquery214/jquery.min.js') }}"></script>

  <!-- Latest compiled and minified Bootstrap js -->
  {{--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>--}}
  <script src="{{ asset('js/bootstrap335/bootstrap.min.js') }}"></script>

  <!-- Authy js -->
  {{--<link href="https://www.authy.com/form.authy.min.css" media="screen" rel="stylesheet" type="text/css">--}}
  <link rel="stylesheet" href="{{ asset('/css/form.authy.min.css') }}">
  {{--<script src="https://www.authy.com/form.authy.min.js" type="text/javascript"></script>--}}
  <script src="{{ asset('js/authy/form.authy.min.js') }}"></script>

  <!-- jq-timeTo -->
  <link rel="stylesheet" href="{{ asset('/css/timeTo.css') }}">
  <script src="{{ asset('js/jq-timeto/jquery.time-to.min.js') }}"></script>

</head>

<body>
  @if (Auth::user())
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">{{ __('Toggle navigation') }}</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{{ url('/') }}">{{ __('Psykjournal') }}</a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          @if (Auth::user()->role === 1)
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ __('Firmaadministrator') }} <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{route('companies.index')}}">{{ __('Mitt firma') }}</a></li>
            </ul>
          </li>
          @endif

          @if (Auth::user()->role === 2)
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{route('companies.index')}}">{{ __('Firma') }}</a></li>
              <li><a href="{{route('users.index')}}">{{ __('Brukere') }}</a></li>
            </ul>
          </li>
          @endif

          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ __('Hjelpemidler') }} <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{route('templates.index')}}">{{ __('Maler') }}</a></li>
            </ul>
          </li>

          <li><a href="{{route('clients.index')}}">{{ __('Klienter') }}</a></li>

        </ul>

        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{ route('companies.users.edit', [Auth::user()->company->id, Auth()->user()->id]) }}">{{ __('Min profil') }}</a></li>
              <li><a href="{{ route('logout') }}">{{ __('Logg ut') }}</a></li>
            </ul>
          </li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>

  @endif

  <div class="container">

    <div class="col-md-10 col-md-offset-2">
      @include('partials.errorSandMessages')

      @if (Auth::user())

        @if (Auth::user()->payment_missing)
          <div class="alert alert-danger" role="alert">En advarsel ble opprettet på din konto {{Auth::user()->payment_missing}} fordi vi ikke kan se at du
            har betalt siste faktura. Dersom betaling ikke er mottatt innen 14 dager etter denne advarselen, vil kontoen din stenges inntil betaling mottas.
            Dersom du har betalt, ta kontakt med administrator for å få avklart dette.
          </div>
        @endif

        @if (Auth::user()->suspended)
          <div class="alert alert-danger" role="alert">
            Kontoen din er låst på grunn av mangelfull betaling. Vær vennlig å betal fakturaen, og kontoen din vil bli
            åpnet igjen.
          </div>
        @endif

      @endif
    </div>

    @yield('content')

  </div>

</body>

<script>
  $('.countdown').timeTo(3600, function() {
    alert('På grunn av en times inaktivitet blir du nå logget ut.' +
      'Dersom du har ulagret arbeid, kan du unngå å miste dette ved å merke alt du nå har skrevet, kopiere det til utklippstavlen og deretter lime det inn igjen ' +
      'etter innlogging');
  });
</script>

</html>