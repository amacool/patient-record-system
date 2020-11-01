<div class="row">
  <div class="col-md-2">
    <div class="sidebar-nav-fixed affix" style="max-width: 160px">
      <div class="well">
        <ul class="nav ">
          <li><a href="{{ route('clients.index') }}">Alle klienter</a></li>
          <li><a href="{{ route('clients.active_index') }}">Aktive (egne)</a></li>
          <li><a href="{{ route('clients.archive_index') }}">Arkiverte (egne)</a></li>
          <li><a href="{{ route('clients.coop_index') }}">Mottatt tilgang</a></li>
          <li><a href="{{ route('clients.create') }}">Ny klient</a></li>
        </ul>
      </div>
      <!--/.well -->
    </div>
    <!--/sidebar-nav-fixed -->
  </div>
  <!--/span-->

  <div class="col-md-10">
