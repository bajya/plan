@extends('layouts.backend.app')
@section('title', ucfirst($type).' Admin')

@section('content')
    <div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Admin</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admins')}}">Admins</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($type)}} Admin</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">

                        @if($type == 'add')
                            <h4>Fill In Admin Details</h4>
                        @elseif($type == 'edit')
                            <h4>Edit Admin Details</h4>
                        @endif
                        <hr>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{$url}}" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div id="adminsForm" class="row" style="margin: 0">
                                <div class="row" style="margin: 0">
                                    <div class="form-group col-md-12">
                                        <label>Name</label><sup class="text-reddit"> *</sup>
                                        <input type="text" class="form-control form-control-line" name="name" value="{{ isset($admin->name) && !empty($admin->name) ? $admin->name : ''}}" maxlength="100">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Email</label><sup class="text-reddit"> *</sup>
                                        <input type="email" class="form-control form-control-line" name="email" value="{{ isset($admin->email) && !empty($admin->email) ? $admin->email : ''}}" maxlength="100">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Password</label><sup class="text-reddit"> *</sup>
                                        <input type="password" id="password" class="form-control form-control-line" name="password" value="" maxlength="100">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Confirm Password</label><sup class="text-reddit"> *</sup>
                                        <input type="password" class="form-control form-control-line" name="confirm-password" value="" maxlength="100">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Roles</label><sup class="text-reddit"> *</sup>
                                        @if($type == 'add')
                                            {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
                                        @else
                                            {!! Form::select('roles[]', $roles,$adminRole, array('class' => 'form-control','multiple')) !!}
                                        @endif
                                    </div>
                                    <input type="hidden" name="status" value="@if(isset($admin) && $admin->status != null) {{$admin->status}} @else active @endif">
                                    <div class="form-group bt-switch col-md-12">
                                        <label class="col-md-4">Status</label>
                                        <div class="col-md-3" style="float: right;">
                                            <input type="checkbox" @if($type == 'edit') @if(isset($admin) && $admin->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusAdmins">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success submitBtn m-r-10" @if($type != 'edit') disabled="" @endif>Save</button>
                                <a href="{{route('admins')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')
<script src="{{URL::asset('/js/jquery-mask-as-number.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('#statusAdmins').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusAdmins").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
            @if($type == 'edit')
                $('input[name=email]').rules('add', {remote: APP_NAME + "/admin/admins/checkAdmins/{{$admin->id}}"});
            @endif
        });
    </script>
@endpush