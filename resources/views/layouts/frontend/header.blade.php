<nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3  navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="{{ url('/') }}">
        {{ config('app.name', 'Indulge') }}
      </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </span>
      </button>
      <div class="collapse navbar-collapse w-100 pt-3 pb-2 py-lg-0" id="navigation">
        @guest
            @if (!Request::is("login"))
                <ul class="navbar-nav d-lg-block d-none">
                  <li class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-sm  bg-gradient-primary  btn-round mb-0 me-1">Login</a>
                  </li>
                </ul>
            @endif
        @else
            <ul class="navbar-nav navbar-nav-hover mx-auto">
              <li class="nav-item dropdown dropdown-hover mx-2">
                <a role="button" class="nav-link ps-2 d-flex justify-content-between cursor-pointer align-items-center " id="dropdownMenuDocs" data-bs-toggle="dropdown" aria-expanded="false">
                  {{ ucfirst(Auth::user()->name) }}
                  <img src=" {{ asset('frontend/img/down-arrow-white.svg')}}" alt="down-arrow" class="arrow ms-1 d-lg-block d-none">
                  <img src="{{ asset('frontend/img/down-arrow-dark.svg')}}" alt="down-arrow" class="arrow ms-1 d-lg-none d-block">
                </a>
                <div class="dropdown-menu dropdown-menu-animation dropdown-lg mt-0 mt-lg-3 p-3 border-radius-lg" aria-labelledby="dropdownMenuDocs">
                  <div class="d-none d-lg-block">
                    <ul class="list-group">
                      <li class="nav-item list-group-item border-0 p-0">
                        <a class="dropdown-item py-2 ps-3 border-radius-md" href="{{ url('home') }}">
                          Home
                        </a>
                        <a class="dropdown-item py-2 ps-3 border-radius-md" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                      </li>
                    </ul>
                  </div>
                </div>
              </li>
            </ul>
        @endguest
      </div>
    </div>
  </nav>