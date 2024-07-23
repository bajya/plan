@extends('layouts.backend.app')
@section('title', 'Media - ' . ucfirst($filemanager->name))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Media</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('filemanagers')}}">Medias</a></li>
                    <li class="breadcrumb-item active">View Media</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                        <span class="mr-5">Image</span> 
                        @if($filemanager->type == 'doctor')
                            <img class="card-title mt-2" src="@if($filemanager->image != null){{URL::asset('/uploads/doctors/'.$filemanager->image)}} @endif" width="40%" />
                        @else
                            <img class="card-title mt-2" src="@if($filemanager->image != null){{URL::asset('/uploads/products/'.$filemanager->image)}} @endif" width="40%" />
                        @endif
                        <br>
                        <h4 class="m-t-10 m-b-0 text-center">@if($filemanager->image != null){{URL::asset('/uploads/products/'.$filemanager->image)}} @endif</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab">Details</a> </li>
                        
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div>
                                    <h5 class="p-t-20 db">Name</h5><small class="text-success db">{{ $filemanager->name }}</small>
                                    <h5 class="p-t-20 db">Type</h5><small class="text-success db">{{ ucfirst($filemanager->type) }}</small>
                                    <h5 class="p-t-20 db">Url</h5><small class="text-success db">@if($filemanager->image != null){{URL::asset('/uploads/products/'.$filemanager->image)}} @endif</small>
                                    <h5 class="p-t-20 db">Created On</h5><small class="text-success db">{{date('Y, M d', strtotime($filemanager->created_at))}}</small>
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
