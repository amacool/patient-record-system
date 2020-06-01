<div class="row">
    <div class="col-md-2">
        <div class="sidebar-nav-fixed affix">
            <div class="well">
                <ul class="nav ">
                    <li class="nav-header">{{$user->name}}</li>
                    <li><a href="{{ route('companies.users.edit', [$company->id, $user->id]) }}">Innstillinger</a></li>
                    <li><a href="{{ route('companies.users.accesslogs', [$company->id, $user->id]) }}">Innloggingsforsøk</a></li>
                </ul>
            </div>
            <!--/.well -->
        </div>
        <!--/sidebar-nav-fixed -->
    </div>
    <!--/span-->