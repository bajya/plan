@extends('layouts.backend.app')
@section('title', 'Category - ' . ucfirst($category->name))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Category</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('categories')}}">Categories</a></li>
                    <li class="breadcrumb-item active">View Category</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                       <?php /* <span class="mr-5">Image</span> <img class="card-title mt-2" src="@if($category->image != null){{URL::asset('/uploads/categories/'.$category->image)}} @endif" width="40%" /><br> */?>
                        <h4 class="m-t-10 m-b-0 text-center">{{$category->name}}</h4>
                        @if(count($categories)>0)
                            <h5 class="p-t-20 db">Child Categories</h5>
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

                                    <h5 class="p-t-20 db">Name</h5><small class="text-success db">{{$category->name}}</small>
                                    
                                    
                                    <h5 class="p-t-20 db">Created On</h5><small class="text-success db">{{date('Y, M d', strtotime($category->created_at))}}</small>
                                    <h5 class="p-t-20 db">Status</h5><small class="text-success db">{{ucfirst(config('constants.STATUS.'.$category->status))}}</small>
                                    <h5 class="p-t-20 db">Is Default</h5><small class="text-success db">{{ ucfirst($category->is_defalt) }}</small>
                                    <?php /*<h5 class="p-t-20 db">Description</h5><small class="text-success db">{!! $category->description !!}</small> */?>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="card-body">
                                <form class="form-horizontal form-material" method="post" action="{{route('changeStatusCategory')}}">
                                    {{csrf_field()}}
                                    <div class="form-group bt-switch">
                                        <div class="col-md-6">
                                            <label class="col-md-6" for="val-block">Status</label>
                                            <input type="hidden" name="statusid" value="{{$category->id}}">
                                            <input type="hidden" name="status" value="{{$category->status}}">
                                            <div class="col-md-2" style="float: right;">
                                                <input type="checkbox" @if($category->status == 'active') checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" id="statusCategory">
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
                    if(ind in categories){
                        list = createNestList(categories[ind], list);
                    }

                    list += '</li></ol>';
                });
                return list;
            }

            var cat_id = "{{$category->id}}";
            var categories = JSON.parse(("{{json_encode($categories)}}").replace(/&quot;/g,'"'));
            var list = createNestList(categories[cat_id],'');

            $('#nestable').html(list);
            $('#statusCategory').on('switchChange.bootstrapSwitch', function (event, state) {

                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusCategory").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
        });
    </script>
@endpush
