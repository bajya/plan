@extends('layouts.backend.app')
@section('title', 'Feedback - ' . ucfirst($feedbacks->user_id))

@section('content') 
    <div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Show Feedback</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('feedbacks')}}">Feedbacks</a></li>
                    <li class="breadcrumb-item active">View Feedback</li>
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
                            <img class="mt-2" src="@if($feedbacks->smiley != null){{URL::asset('/uploads/smiley/'.$feedbacks->smiley.'.png')}} @endif" width="40%" /><br>
                            <h4 class="card-title m-t-10 m-b-0">{{ ucfirst($feedbacks->user_id)}}</h4>
                        </center>
                    </div>
                    
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
                                    <h6 class="p-t-20">Registered On</h6><small class="text-primary font-15 db">{{date('Y, M d', strtotime($feedbacks->created_at))}}</small>
                                    <h6 class="p-t-20">Category</h6><small class="text-primary font-15 db">
                                        {{$feedbacks->category}}
                                    </small>
                                    <h6 class="p-t-20">Description</h6><small class="text-primary font-15 db">
                                        {{$feedbacks->description}}
                                    </small>
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
