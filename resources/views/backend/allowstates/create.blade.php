@extends('layouts.backend.app')
@section('title', ucfirst($type).' Allow State')

@section('content')
<style type="text/css">
    #map{
        height: 300px !important; 
    }
</style> 
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Allow State</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('allowstates')}}">Allow States</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($type)}} Allow State</li>
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
                            <h4>Fill In Allow State Details</h4>
                        @elseif($type == 'edit')
                            <h4>Edit Allow State Details</h4>
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
                            <div class="col-md-12 p-0">
                                
                                <div class="form-group col-md-6 m-t-20 float-left">
                                    <label>Name</label><sup class="text-reddit"> *</sup>
                                    <input type="text" class="form-control form-control-line" name="state_name" value="{{old('state_name', $allowstate->name)}}" maxlength="100">
                                </div>
                                <input type="hidden" name="status" value="@if(isset($allowstate) && $allowstate->status != null) {{$allowstate->status}} @else active @endif">
                                <div class="form-group bt-switch col-md-6 m-t-20">
                                    <label class="col-md-4">Status</label>
                                    <div class="col-md-3" style="float: right;">
                                        <input type="checkbox" @if($type == 'edit') @if(isset($allowstate) && $allowstate->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusCat">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('allowstates')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
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
                    <h5 class="m-t-10 text-danger changeOffer">Are you sure you want to removed Allow State?.</h5>
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

            $(document).on('keyup',".decimalInput, .numberInput",function(e){

                if($(this).val().indexOf('-') >=0){
                    $(this).val($(this).val().replace(/\-/g,''));
                }
            })

            $(document).find(".numberInput").maskAsNumber({receivedMinus:false});
            $(document).find(".decimalInput").maskAsNumber({receivedMinus:false,decimals:6});


            

            @if($type == 'edit')
                /*$('input[name=state_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/allowstates/checkAllowState/{{$allowstate->id}}",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});*/

              
            @else
                /*$('input[name=state_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/allowstates/checkAllowState",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});*/
            @endif
            $('.confirmBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

            $('.cancelBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

        });
    </script>
@endpush