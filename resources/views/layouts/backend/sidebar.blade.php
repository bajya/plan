<?php //dd(Request::segment(2));?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <div class="text-center navbar-brand-wrapper d-flex align-items-top justify-content-center">
                <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                    <!-- <b><img src="{{URL::asset('/images/logo.jpg')}}" alt="homepage" class="dark-logo" style="width: 10%;"/></b> -->
                     <b><img src="{{ asset('images/logo_back.png') }}" alt="homepage" class="dark-logo" style="width: 60%;"></b>
                    <!-- <h3>{{ config('app.name', 'Laravel') }}</h3> -->
                </a>
                <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                   <b><img src="{{ asset('images/logo_back.png') }}" alt="homepage" class="dark-logo" style="width: 60%;"></b>
                    <!-- <h3>{{ config('app.name', 'Laravel') }}</h3> -->
                </a>
            </div>
            <div class="nav-link d-flex d-lg-none">
                <div class="user-wrapper">
                    <div class="text-wrapper">
                        <p class="profile-name">{{ config('app.name', 'Laravel') }}</p>
                        <div>
                            <small class="designation text-muted">{{ Auth::user()->name }}</small>
                            <span class="status-indicator online"></span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
    <ul class="nav sidebarLinks">
        <li class="nav-item {{ request()->is('admin') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('dashboard')}}"><i class="menu-icon mdi mdi-gauge"></i><span class="menu-title">Dashboard</a>
        </li>
        <?php /*<li class="nav-item {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('roles')}}"><i class="menu-icon fa fa-unlock-alt"></i><span class="menu-title">Role Management</span></a>
        </li> 
        <li class="nav-item {{ request()->is('admin/admins') || request()->is('admin/admins/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('admins')}}"><i class="menu-icon fa fa-user"></i><span class="menu-title">Admins</span></a>
        </li>
        */?>
        <li class="nav-item allow @if(Request::segment(2) == 'allowstates') active @endif">
            <a class="nav-link" href="{{ route('allowstates') }}"><i class="menu-icon fa fa-map-marker"></i><span class="menu-title">Allow State</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('users')}}"><i class="menu-icon fa fa-user"></i><span class="menu-title">Users</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/doctors') || request()->is('admin/doctors/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('doctors') }}"><i class="menu-icon fa fa-stethoscope"></i><span class="menu-title">Doctor Managment</span></a>
        </li>
        
        <li class="nav-item state @if(Request::segment(2) == 'states') active @endif">
            <a class="nav-link" href="{{ route('states') }}"><i class="menu-icon fa fa-map-marker"></i><span class="menu-title">State Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/brands') || request()->is('admin/brands/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('brands') }}"><i class="menu-icon fa fa-adn"></i><span class="menu-title">Company Managment</span></a>
        </li> 
        <li class="nav-item {{ request()->is('admin/dispensaries') || request()->is('admin/dispensaries/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dispensaries') }}"><i class="menu-icon fa fa-map-marker"></i><span class="menu-title">Location Managment</span></a>
        </li>
        
        
        <li class="nav-item {{ request()->is('admin/categories') || request()->is('admin/categories/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('categories') }}"><i class="menu-icon fa fa-list-alt"></i><span class="menu-title">Category Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/types') || request()->is('admin/types/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('types') }}"><i class="menu-icon fa fa-file-text-o"></i><span class="menu-title">Product Type Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/strains') || request()->is('admin/strains/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('strains') }}"><i class="menu-icon fa fa-star"></i><span class="menu-title">Strain Managment</span></a>
        </li> 
        
        <li class="nav-item {{ request()->is('admin/products') || request()->is('admin/products/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('products') }}"><i class="menu-icon fa fa-product-hunt"></i><span class="menu-title">Product Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/plan/list') || request()->is('admin/plan/list/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('plan') }}"><i class="menu-icon fa fa-rocket"></i><span class="menu-title">Subscription Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/transactions/list') || request()->is('admin/transactions/list/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('transactions') }}"><i class="menu-icon fa fa-money"></i><span class="menu-title">Transaction Managment</span></a>
        </li> 
        <li class="nav-item {{ request()->is('admin/cms/list') || request()->is('admin/cms/list/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('cms') }}"><i class="menu-icon fa fa-grav"></i><span class="menu-title">CMS Managment</span></a>
        </li>

        <li class="nav-item {{ request()->is('admin/supports') || request()->is('admin/supports/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('supports') }}"><i class="menu-icon fa fa-support"></i><span class="menu-title">Support Managment</span></a>
        </li>
        <li class="nav-item {{ request()->is('admin/feedbacks') || request()->is('admin/feedbacks/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('feedbacks') }}"><i class="menu-icon fa fa-comments-o"></i><span class="menu-title">Feedback Managment</span></a>
        </li>
         <?php /* <li class="nav-item {{ request()->is('admin/pushs/list') || request()->is('admin/pushs/list/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pushs') }}"><i class="menu-icon fa fa-bell"></i><span class="menu-title">Push Notifications</span></a>
        </li> */?>
        <li class="nav-item {{ request()->is('admin/filemanagers') || request()->is('admin/filemanagers/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('filemanagers') }}"><i class="menu-icon fa fa-file"></i><span class="menu-title">Media Managment</span></a>
        </li>

        
        <li class="nav-item {{ request()->is('admin/clears') || request()->is('admin/clears/*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('clears') }}"><i class="menu-icon fa fa-remove"></i><span class="menu-title">Clear Record</span></a>
        </li>
    </ul>
</nav>