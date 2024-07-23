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
                            
                            <h5>Reset Password</h5>
                        </div> 
                        <div class="card-body">
                            <form method="POST" action="{{ route('password.email') }}"  class="text-start">
                                @csrf
                                <div class="mb-3">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-info w-100 my-4 mb-2">{{ __('Send Password Reset Link') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection
