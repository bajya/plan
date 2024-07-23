@extends('layouts.backend.app')
@section('title', 'Doctors')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Doctors</h3> </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('doctors')}}">Doctors</a></li>
                    <li class="breadcrumb-item active">Bulk Image</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Bulk Image Upload</h4>

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{ route('bulkImageUploadDoctor') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="sampleSheet col-12">
                                <div class="variantRow">
                                    <h6>Points to Remember:</h6>
                                    <p>
                                        <ol>
                                            <li>Multiple image make single zip file and upload it.</li>
                                        </ol>
                                    </p>
                                </div>
                            </div>
                            @include('layouts.backend.message_middle')
                            @include('layouts.backend.message')
                            <div class="col-md-12 p-0">
                                <div class="form-group col-md-6 m-t-20 float-left">
                                    <label>File</label><sup class="text-reddit"> *</sup>

                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                        <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions - .zip)</span> <span class="fileinput-exists">Change</span>
                                        <input type="file" required accept=".zip" name="image_upload"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Upload</button>
                                <a href="{{route('doctors')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection