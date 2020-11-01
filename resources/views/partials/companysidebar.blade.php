<div class="row">
  <style>
    @media (min-width: 992px) {
      .sidebar-nav-fixed.affix {
        max-width: 165px;
      }
    }
  </style>
  <div class="col-md-2">
    <div class="sidebar-nav-fixed affix">
      <div class="well">
        <ul class="nav ">
          <li class="nav-header">{{ $company->name }}</li>
          <li><a href="{{ route('companies.edit', [$company->id]) }}">Endre</a></li>
          <li><a href="{{ route('companies.show', [$company->id]) }}">Brukere</a></li>
          @if (Auth::user()->role === 2 || (Auth::user()->role === 1 && Auth::user()->company_id === $company->id))
            <li><a href="{{ route('companies.clients', [$company->id]) }}">Klienter</a></li>
          @endif
          @if ($company->seats - count($company->user) > 0 )
            <li><a href="{{ route('register-page') }}">Ny bruker</a></li>
          @endif
          @if (Auth::user()->role === 2)
            <li><a href="{{ route('companies.export', [$company->id]) }}">Eksporter data</a></li>
          @endif
        </ul>
      </div>
      <!--/.well -->
    </div>
    <!--/sidebar-nav-fixed -->
  </div>
  <!--/span-->
