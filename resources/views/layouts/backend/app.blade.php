<?php /*

<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel 8 User Roles and Permissions Tutorial') }}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Laravel 8 User Roles and Permissions - ItSolutionStuff.com
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
    
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto"></ul>


                    <ul class="navbar-nav ml-auto">
                        @guest
                            <li><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                            <li><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                        @else
                            <li><a class="nav-link" href="{{ route('users.index') }}">Manage Users</a></li>
                            <li><a class="nav-link" href="{{ route('roles.index') }}">Manage Role</a></li>
                            <li><a class="nav-link" href="{{ route('admins.index') }}">Manage Admin</a></li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>


                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>


                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>


        <main class="py-4">
            <div class="container">
            @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

*/?>


<!DOCTYPE html>
<html>
    <head>
        @include('layouts.backend.head')
    </head>
    <body class="fix-header fix-sidebar card-no-border">
        <!-- <div class="preloader">
            <div class="loader">
                <div class="loader__figure"></div>
                <p class="loader__label">{{ config('app.name', 'Laravel') }}</p>
            </div>
        </div> -->
        <div class="ajaxSpinnerContainer" id="ajaxSpinnerImage">
             <img src="{{ asset('images/spinner.gif') }}" id="" title="working..." />
        </div>
        <div class="container-scroller">
            <div class="container-fluid page-body-wrapper">
                @include('layouts.backend.header')
                @include('layouts.backend.sidebar')

                <div class="main-panel">

                    @yield('content')

                    @include('layouts.backend.footer')
                </div>
            </div>
        </div>
        @include('layouts.backend.script')
    </body>
</html>