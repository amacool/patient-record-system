<div class="row">
    <div class="col-md-2">
        <div class="sidebar-nav-fixed affix">
            <div class="well">
                <ul class="nav ">
                    <li class="nav-header">{{$company->name}}</li>
                    @if ($company->seats - count($company->user) > 0 )
                    <li><a href="{{ route('registrationpage') }}">Ny bruker</a></li>
                        @endif

                </ul>
            </div>
            <!--/.well -->
        </div>
        <!--/sidebar-nav-fixed -->
    </div>
    <!--/span-->