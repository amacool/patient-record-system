<div class="row">
  <div class="col-md-2">
    <div class="sidebar-nav-fixed affix">
      <div class="well">
        <ul class="nav ">
          <li class="nav-header">{{ $user->name }}</li>
          <li><a href="{{ route('companies.users.edit', [$company ? $company->id : 0, $user->id]) }}">{{ __('Innstillinger') }}</a></li>
          <li><a href="{{ route('companies.users.access_logs', [$company ? $company->id : 0, $user->id]) }}">{{ __('Innloggingsforsøk') }}</a></li>
        </ul>
      </div>
      <!--/.well -->
    </div>
    <!--/sidebar-nav-fixed -->
  </div>
  <!--/span-->