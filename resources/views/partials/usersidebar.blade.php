<div class="row">
  <div class="col-md-2">
    <div class="sidebar-nav-fixed affix">
      <div class="well">
        <ul class="nav ">
          <li class="nav-header">{{ $user->name }}</li>
          <li><a href="{{ route('companies.users.edit', [$company ? $company->id : 0, $user->id]) }}">{{ __('Innstillinger') }}</a></li>
          <li><a href="{{ route('companies.users.access_logs', [$company ? $company->id : 0, $user->id]) }}">{{ __('Innloggingsforsøk') }}</a></li>
          @if ($user->id == Auth::user()->id || Auth::user()->role === 2)
            <li><a href="{{ route('companies.users.access_transfer_logs', [$company ? $company->id : 0, $user->id]) }}">{{ __('Accesses and Transfers') }}</a></li>
          @endif
          @if (Auth::user()->role === 2 || (Auth::user()->company_id == $company->id && Auth::user()->role === 1))
            <li><a href="{{ route('companies.clients.active', [$company ? $company->id : 0, $user->id]) }}">{{ __('Aktive klienter') }}</a></li>
            <li><a href="{{ route('companies.clients.archive', [$company ? $company->id : 0, $user->id]) }}">{{ __('arkiv') }}</a></li>
            <li><a href="{{ route('companies.clients.coop', [$company ? $company->id : 0, $user->id]) }}">{{ __('Coop Clients') }}</a></li>
          @endif

        </ul>
      </div>
      <!--/.well -->
    </div>
    <!--/sidebar-nav-fixed -->
  </div>
  <!--/span-->
