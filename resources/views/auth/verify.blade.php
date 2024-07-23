@extends('layouts.frontend.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center"></div>
        <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 offset-xl-8">
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <div class="card z-index-0">
                        <div class="card-header text-center pt-4">
                            <h5>{{ __('Verify Your Email Address') }}</h5>
                        </div> 
                        <div class="card-body">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email') }},
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}"  class="text-start">
                                @csrf
                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-info w-100 my-4 mb-2">{{ __('click here to request another') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection

