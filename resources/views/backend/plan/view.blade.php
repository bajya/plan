@extends('layouts.backend.app')
@section('title', 'Subscription - ' . ucfirst($plan->title))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Subscription</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('plan')}}">Subscriptions</a></li>
                    <li class="breadcrumb-item active">View Subscription</li>
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
                            <h4 class="card-title m-t-10 m-b-0">{{ ucfirst($plan->title)}}</h4>
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
                                    <h6 class="p-t-20">Title</h6><small class="text-primary font-15 db">{{ ucfirst($plan->title) }}</small>
                                    <h6 class="p-t-20">Amount</h6><small class="text-primary font-15 db">$ {{ ucfirst($plan->amount) }}</small>
                                    <h6 class="p-t-20">Duration</h6><small class="text-primary font-15 db">{{ ucfirst($plan->duration_text) }}</small>
                                    <h6 class="p-t-20">Created At</h6><small class="text-primary font-15 db">{{date('Y, M d', strtotime($plan->created_at))}}</small>
                                    
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
