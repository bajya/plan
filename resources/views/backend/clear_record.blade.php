@extends('layouts.backend.app')
@section('title', 'Clear Record')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Clear Record</h3> </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('clears')}}">Clear Record</a></li>
                    <li class="breadcrumb-item active">Clear Record</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">All Data Remove</h4>

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{ route('clearRecord') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="sampleSheet col-12">
                                <div class="variantRow">
                                    <h6>Points to Remember:</h6>
                                    <p>
                                        <ol>
                                            <li>User & Doctor Management Data Clear.</li>
                                            <li>State, Company & Location Management Data Clear.</li>
                                            <li>Category, Type, Strain & Product Management Data Clear.</li>
                                            <li>Transaction, Support & Feedback Management Data Clear.</li>
                                        </ol>
                                    </p>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="all">
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Clear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    
                    <div class="card-body">
                        <h4 class="card-title">Product Related Data Remove</h4>

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{ route('clearRecord') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="sampleSheet col-12">
                                <div class="variantRow">
                                    <h6>Points to Remember:</h6>
                                    <p>
                                        <ol>
                                            <li>Category Management Data Clear.</li>
                                            <li>Product Type Management Data Clear.</li>
                                            <li>Strain Management Data Clear.</li>
                                            <li>Product Management Data Clear.</li>
                                        </ol>
                                    </p>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="product">
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Clear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection