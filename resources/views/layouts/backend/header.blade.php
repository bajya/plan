<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row" id="topbar">
    <div class="navbar-menu-wrapper d-flex align-items-center ml-auto ml-lg-0">
        <ul class="navbar-nav navbar-nav-right">
            
           
            <li class="nav-item">
                <a class="nav-link" href="{{ route('clears') }}">Clear Record</a>
            </li>
            <li class="nav-item dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                    <span class="profile-text">Hello,  {{ Auth::user()->name }} !</span>
                    <img class="img-xs rounded-circle" src="{{URL::asset('/images/noimage.png')}}" alt="Profile image">
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <ul class="dropdown-user">
                        <li>
                            <div class="dw-user-box">
                                <div class="u-img"><img src="{{URL::asset('/images/noimage.png')}}" alt="user" class="profile-pic" /></div>
                                <div class="u-text">
                                    <h4>{{ config('app.name', 'Laravel') }}</h4>
                                    <p class="text-muted">{{ Auth::user()->name }}</p>
                                </div>
                            </div>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li><a href="{{route('changepassword')}}"><i class="fa fa-pencil"></i> Change Password</a></li>
                        <li><a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i>
                            {{ __('Logout') }}
                        </a></li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>