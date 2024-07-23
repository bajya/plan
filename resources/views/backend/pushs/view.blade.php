@extends('layouts.backend.app')
@section('title', 'Notification - ' . ucfirst($push->title))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Notification</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('pushs')}}">Notifications</a></li>
                    <li class="breadcrumb-item active">View Notification</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                        @include('layouts.backend.message')
                        <center class="m-t-30">
                            <h4 class="card-title m-t-10 m-b-0">{{ ucfirst($push->title)}}</h4>
                        </center>
                    </div>
                    <div>
                        <hr> </div>
                    
                </div>
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">Details</a> </li>

                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div>
                                    <h6 class="p-t-20">Title</h6><small class="text-primary font-15 db">{{ ucfirst($push->title) }}</small>
                                    <h6 class="p-t-20">Description</h6><small class="text-primary font-15 db">{{ ucfirst($push->description) }}</small>
                                    <h6 class="p-t-20">Created At</h6><small class="text-primary font-15 db">{{date('Y, M d', strtotime($push->created_at))}}</small>
                                    <h6 class="p-t-20">Is Send</h6><small class="text-primary font-15 db">{{ucfirst(config('constants.CONFIRM.'.$push->is_send))}}</small>
                                    <h6 class="p-t-20">Users</h6><small class="text-primary font-15 db">@if(!empty($push->push_user()))
                                        @foreach($push->push_user as $key => $push_user)
                                            @if(isset($push_user->user->name))
                                                <label class="badge badge-success">{{ ucfirst($push_user->user->name) ?? '' }}</label>
                                            @endif
                                        @endforeach
                                    @endif</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')
   
@endpush
