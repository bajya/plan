@extends('layouts.backend.app')
@section('title', 'Change Password')

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Change Password</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </div>
        </div>
        <div class="row w-100">
            <div class="col-lg-12">
                @include('layouts.backend.message')
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4">Change Password</h4>
                        <hr>
                        <form class="form-valide col-lg-5 m-t-50" method="post" action="{{route('changepasswordPost')}}">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label class="label">Old Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="*********" name="old-pass" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="mdi mdi-check-circle-outline"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="pass" placeholder="*********" name="pass" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="mdi mdi-check-circle-outline"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="*********" name="confirm-pass" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="mdi mdi-check-circle-outline"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-flat m-b-30">Confirm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection