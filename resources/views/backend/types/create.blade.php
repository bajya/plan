@extends('layouts.backend.app')
@section('title', ucfirst($typepage).' Type')

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($typepage)}} Type</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('types')}}">Types</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($typepage)}} Type</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">

                        @if($typepage == 'add')
                            <h4>Fill In Type Details</h4>
                        @elseif($typepage == 'edit')
                            <h4>Edit Type Details</h4>
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
                            <!-- <div class="col-md-12 p-0"> -->
                                <div class="form-group col-md-6 m-t-20 float-left" style="display: none;">
                                    <label>Image</label><sup class="text-reddit"> *</sup>
                                    <input type="hidden" name="image_exists" id="image_exists" value="1">

                                    @if($typepage == 'add' || ($typepage == 'edit' && $type->image == null))
                                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                            <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span>
                                            <input type="file" name="cat_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                        </div>
                                    @elseif($typepage == 'edit')
                                        <br>
                                        <div id="catImage">

                                            <img src="@if($type->image != null && file_exists(public_path('/uploads/types/'.$type->image))){{URL::asset('/uploads/types/'.$type->image)}}@endif" width="70"" />
                                            &nbsp;&nbsp;&nbsp;<a id="changeImage" href="javascript:void(0)" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Delete">Change</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group col-md-6 m-t-20" id="parentcol">
                                    <label>Parent Category <sup class="text-reddit"> *</sup></label>
                                    <select class="form-control" name="parent_id" id="parent_id">
                                        @if(count($types) > 0)
                                            <option value=''>Select Root Category</option>
                                            @if($types)
                                                @foreach($types as $id=> $cat)
                                                    <option value="{{$id}}" @if($type->parent_id==$id) selected @endif>{{$cat}}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Types found</option>
                                        @endif

                                    </select>
                                </div>
                                
                            <!-- </div> -->
                            <div class="form-group col-md-6 m-t-20">
                                    <label>Name</label><sup class="text-reddit"> *</sup>
                                    <input type="text" class="form-control form-control-line" name="cat_name" value="{{old('cat_name', $type->name)}}" maxlength="100">
                                </div>
                            <div class="form-group col-md-12 m-t-20" style="display: none;">
                                <label>Description</label><sup class="text-reddit"> *</sup>
                                <textarea class="form-control form-control-line" name="description" rows="5">Text Description</textarea>
                            </div>

                            <input type="hidden" name="status" value="@if(isset($type) && $type->status != null) {{$type->status}} @else active @endif">
                            <div class="form-group bt-switch col-md-6 m-t-20">
                                <label class="col-md-4">Status</label>
                                <div class="col-md-3" style="float: right;">
                                    <input type="checkbox" @if($typepage == 'edit') @if(isset($type) && $type->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusCat">
                                </div>
                            </div>
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('types')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> --}}
                </div>
                <div class="modal-body">
                    <h5 class="m-t-10 text-danger changeOffer">Are you sure you want to removed type?.</h5>
                    <button type="button" class="btn btn-secondary btn-flat cancelBtn m-b-30 m-t-30" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info btn-flat confirmBtn m-b-30 m-t-30">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{URL::asset('/js/jquery-mask-as-number.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('#statusCat').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusCat").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
            $(document).find(".decimalInput, .numberInput").on('keyup',function(e){

                if($(this).val().indexOf('-') >=0){
                    $(this).val($(this).val().replace(/\-/g,''));
                }
            })

            $('#changeImage').click(function(){
                $('#catImage').parent().append('<div class="fileinput fileinput-new input-group" data-provides="fileinput"><div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span><input type="file" name="cat_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a></div>');
                $('.tooltip').tooltip('hide');
                $('#catImage').remove();
                $('#image_exists').val(0);
            });

            @if($typepage == 'edit')
                $('input[name=cat_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/types/checkType/{{$type->id}}",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});

              
            @else
                $('input[name=cat_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/types/checkType",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});
            @endif
            $('.confirmBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

            $('.cancelBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

            $('#parent_id').change(function(){
                $('input[name=cat_name]').valid();
            })

        });
    </script>
@endpush