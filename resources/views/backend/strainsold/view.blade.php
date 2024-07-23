@extends('layouts.backend.app')
@section('title', 'Strain - ' . ucfirst($strain->name))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Strain</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('strains')}}">Strains</a></li>
                    <li class="breadcrumb-item active">View Strain</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                       <?php /* <span class="mr-5">Image</span> <img class="card-title mt-2" src="@if($strain->image != null){{URL::asset('/uploads/strains/'.$strain->image)}} @endif" width="40%" /><br> */?>
                        <h4 class="m-t-10 m-b-0 text-center">{{$strain->name}}</h4>
                        @if(count($strains)>0)
                            <h5 class="p-t-20 db">Child Strains</h5>
                            <div id="nestable">

                            </div>
                        @endif
                    </div>

                </div>
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab">Details</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#settings" role="tab">Settings</a> </li>
                        
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div>

                                    <h5 class="p-t-20 db">Name</h5><small class="text-success db">{{$strain->name}}</small>
                                    
                                    
                                    <h5 class="p-t-20 db">Created On</h5><small class="text-success db">{{date('Y, M d', strtotime($strain->created_at))}}</small>
                                    <h5 class="p-t-20 db">Status</h5><small class="text-success db">{{ucfirst(config('constants.STATUS.'.$strain->status))}}</small>
                                    <h5 class="p-t-20 db">Description</h5><small class="text-success db">{!! $strain->description !!}</small>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="card-body">
                                <form class="form-horizontal form-material" method="post" action="{{route('changeStatusStrain')}}">
                                    {{csrf_field()}}
                                    <div class="form-group bt-switch">
                                        <div class="col-md-6">
                                            <label class="col-md-6" for="val-block">Status</label>
                                            <input type="hidden" name="statusid" value="{{$strain->id}}">
                                            <input type="hidden" name="status" value="{{$strain->status}}">
                                            <div class="col-md-2" style="float: right;">
                                                <input type="checkbox" @if($strain->status == 'active') checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" id="statusStrain">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button type="submit" class="btn btn-success">Update</button>
                                        </div>
                                    </div>
                                </form>
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
    <script type="text/javascript">
        $(document).ready(function(){
             //recursive function to create list upto n nesting level
            function createNestList(arr, list){

                $.each(arr,function(ind,item){
                    list += '<ol class="dd-list"><li class="dd-item"><div class="dd-handle">'+item+'</div>';
                    if(ind in strains){
                        list = createNestList(strains[ind], list);
                    }

                    list += '</li></ol>';
                });
                return list;
            }

            var strain_id = "{{$strain->id}}";
            var strains = JSON.parse(("{{json_encode($strains)}}").replace(/&quot;/g,'"'));
            var list = createNestList(strains[strain_id],'');

            $('#nestable').html(list);
            $('#statusStrain').on('switchChange.bootstrapSwitch', function (event, state) {

                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusStrain").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
        });
    </script>
@endpush
