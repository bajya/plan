@extends('layouts.backend.app')
@section('title', ucfirst($type).' User')

@section('content')
    <div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} User</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('users')}}">Users</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($type)}} User</li>
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
                            <h4>Fill In User Details</h4>
                        @elseif($type == 'edit')
                            <h4>Edit User Details</h4>
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
                            <div id="usersForm" class="row" style="margin: 0">
                                <div class="row" style="margin: 0">
                                    <div class="form-group col-md-6 ">
                                    <label>Image</label><sup class="text-reddit"> *</sup>
                                    <input type="hidden" name="image_exists" id="image_exists1" value="1">

                                    @if($type == 'add' || ($type == 'edit' && $user->avatar == null))
                                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                            <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span>
                                            <input type="file" required name="user_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                            <p>upload this size for better resolution(480x385)</p>
                                        </div>
                                    @elseif($type == 'edit')
                                        <br>
                                        <div id="userImage1">
                                            <img src="@if($user->avatar != null && file_exists(public_path('/img/avatars/'.$user->avatar))){{URL::asset('/img/avatars/'.$user->avatar)}}@endif" width="70" />
                                            &nbsp;&nbsp;&nbsp;<a id="changeImage1" onclick="remove(1)" href="javascript:void(0)" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Delete">Change</a>


                                        </div>
                                    @endif
                                </div>
                                    <div class="form-group col-md-6">
                                        <label>Name</label><sup class="text-reddit"> *</sup>
                                        <input type="text" class="form-control form-control-line" name="name" value="{{ isset($user->name) && !empty($user->name) ? $user->name : ''}}" maxlength="100">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Mobile</label><sup class="text-reddit"> *</sup>
                                        <input type="text" class="form-control form-control-line" name="mobile" value="{{ isset($user->mobile) && !empty($user->mobile) ? $user->mobile : ''}}" maxlength="100">
                                    </div>
                                    <input type="hidden" name="roles[]" value="2">
                                    <?php /*<div class="form-group col-md-12">
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
                                            {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','multiple')) !!}
                                        @endif
                                    </div> */?>
                                    <input type="hidden" name="status" value="@if(isset($user) && $user->status != null) {{$user->status}} @else active @endif">
                                    <div class="form-group bt-switch col-md-12">
                                        <label class="col-md-4">Status</label>
                                        <div class="col-md-3" style="float: right;">
                                            <input type="checkbox" @if($type == 'edit') @if(isset($user) && $user->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusUsers">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('users')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
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
            $('#statusUsers').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusUsers").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
            $('#changeImage1').click(function(){
                $('#userImage1').parent().append('<div class="fileinput fileinput-new input-group" data-provides="fileinput"><div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span><input type="file" name="user_image" required accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a></div>');
                $('.tooltip').tooltip('hide');
                $('#userImage1').remove();
                $('#image_exists').val(0);
            });
            @if($type == 'edit')
                $('input[name=mobile]').rules('add', {remote: APP_NAME + "/admin/users/checkUsers/{{$user->id}}"});
            @endif
        });

    </script>
@endpush