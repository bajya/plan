<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.frontend.head')

</head>
<style>
  .main_dv_img{
    background-size: cover;
    /*position: absolute;*/
    height: 100vh;
    width: 100%;
   
  }
</style>
<body class="bg-gray-100"> 

  <main class="main-content  mt-0">
    @if(request()->is('legal') || request()->is('privacy-policy') || request()->is('term-and-condition'))
        @yield('content')
    @else
      <div class="page-header align-items-start min-vh-50 pt-5 main_dv_img" >
        <span class="mask bg-gradient-dark opacity-6"></span>
        <img src="{{ asset('images/logo_front.png') }}" style="position: relative;z-index: 999; width: 100px; margin-left: 50px; padding-bottom: 14px;">           
          @yield('content')
      </div>
    @endif
    
  </main>
 
  @include('layouts.frontend.script')
  @include('layouts.frontend.footer')
 
</body>

</html>