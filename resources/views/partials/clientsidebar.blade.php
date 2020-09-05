<div class="row">
  <div class="col-md-2">
    <div class="sidebar-nav-fixed affix">
      <div class="well">
        <ul class="nav">
          <li class="nav-header">{{ $client->firstname }} {{ $client->lastname }}</li>
          <li><a href="{{ route('clients.edit', [$client->id]) }}">Endre personlig info</a></li>
          <li><a href="{{ route('clients.records.create', [$client->id]) }}">Skriv notat</a></li>
          <li><a href="{{ route('clients.records.list', [$client->id]) }}">Notater (oversikt)</a></li>
          <li><a href="{{ route('clients.records.view_all', [$client->id]) }}">Notater (les alle)</a></li>
          <li><a href="{{ route('clients.files.index', [$client->id]) }}">Filer</a></li>
          <li><a href="{{ route('clients.access', [$client->id]) }}">Tilganger</a></li>
          <li><a href="{{ route('clients.transfer', [$client->id]) }}">Overfør</a></li>
        </ul>
      </div>
      <!--/.well -->
    </div>
    <!--/sidebar-nav-fixed -->
  </div>
  <!--/span-->