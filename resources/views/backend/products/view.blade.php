@extends('layouts.backend.app')
@section('title', 'Product - ' . ucfirst($product->name))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Show Product</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('products')}}">Products</a></li>
                    <li class="breadcrumb-item active">View Product</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                        <center class="m-t-30"> <img class="card-title" src="@if($product->image_url != null){{ $product->image_url }} @endif" width="100%" />
                            <h4 class="m-t-10 m-b-0">{{ ucfirst($product->name) }}</h4>
                        </center>
                    </div>

                </div>
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active show" data-toggle="tab" href="#profile" role="tab">Details</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#settings" role="tab">Settings</a> </li>
                        
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div>
                                    <h5 class="p-t-20 db">Brand</h5><small class="text-success db">{{ ucfirst($product->brand->name) }}</small>
                                    <h5 class="p-t-20 db">Dispensary</h5><small class="text-success db">{{ ucfirst($product->dispensary->name) }}</small>
                                    <h5 class="p-t-20 db">Name</h5><small class="text-success db">{{ ucfirst($product->name) }}</small>
                                    <h5 class="p-t-20 db">Product Id</h5><small class="text-success db">{{$product->product_sku}}</small>
                                    <h5 class="p-t-20 db">Category</h5><small class="text-success db">@if(isset($product->category)) {{($product->category!=null)?$product->category->name:$product->subcategory->parentCat->name}} @endif</small>
                                    @if($product->subcategory!=null)
                                        <h5 class="p-t-20 db">Sub-Category</h5><small class="text-success db">{{$product->subcategory->name}}</small>
                                    @endif
                                    <h5 class="p-t-20 db">Type</h5><small class="text-success db">{{ isset($product->type->name) && !empty($product->type->name) ? $product->type->name : '-' }}</small>
                                    <h5 class="p-t-20 db">Strain</h5><small class="text-success db">{{ isset($product->strain->name) && !empty($product->strain->name) ? $product->strain->name : '-' }}</small>
                                    <h5 class="p-t-20 db">Featured</h5><small class="text-success db">{{ucfirst(config('constants.CONFIRM.'.$product->is_featured))}}</small>
                                    <h5 class="p-t-20 db">Created On</h5><small class="text-success db">{{date('Y, M d', strtotime($product->created_at))}}</small>
                                    <h5 class="p-t-20 db">Status</h5><small class="text-success db">{{ucfirst(config('constants.STATUS.'.$product->status))}}</small>
                                    <h5 class="p-t-20 db">Description</h5><small class="text-success db">{!! $product->description !!}</small>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="card-body">
                                <form class="form-horizontal form-material" method="post" action="{{route('changeStatusProduct')}}">
                                    {{csrf_field()}}
                                    <div class="form-group bt-switch">
                                        <div class="col-md-6">
                                            <label class="col-md-6" for="val-block">Status</label>
                                            <input type="hidden" name="statusid" value="{{$product->id}}">
                                            <input type="hidden" name="status" value="{{$product->status}}">
                                            <input type="hidden" name="quick_grab" value="{{$product->is_featured}}">
                                            <input type="hidden" name="is_exclusive" value="{{$product->is_exclusive}}">
                                            <div class="col-md-2" style="float: right;">
                                                <input type="checkbox" @if($product->status == 'active') checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" id="statusProduct">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group bt-switch">
                                        <div class="col-md-6">
                                            <label class="col-md-6" for="val-block">Featured</label>
                                            <input type="hidden" name="is_featured" value="{{$product->is_featured}}">
                                            <div class="col-md-2" style="float: right;">
                                                <input type="checkbox" @if($product->is_featured == '1') checked @endif data-on-color="success" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="mini" name="cquickGrab" id="quickGrab">
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


            $('#statusProduct').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusProduct").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
            $('#quickGrab').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#quickGrab").is(':checked'))
                    $('input[name=is_featured]').val('1');
                else
                    $('input[name=is_featured]').val('0');
            });
        });
    </script>
@endpush
