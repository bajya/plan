@extends('layouts.backend.app')
@section('title', 'Location - ' . ucfirst($dispensary->name))

@section('content')
    <div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Location</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('dispensaries')}}">Locations</a></li>
                    <li class="breadcrumb-item active">View Location</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            @include('layouts.backend.message')
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                        <span class="mr-5">Image</span> <img class="card-title mt-2" src="@if($dispensary->image != null){{URL::asset('/uploads/brands/'.$dispensary->image)}} @endif" width="40%" /><br>
                        <h4 class="m-t-10 m-b-0 text-center">{{$dispensary->name}}</h4>
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
                                    <h5 class="p-t-20 db">Company</h5><small class="text-success db">{{ ucfirst($dispensary->brand->name) }}</small>
                                    <h5 class="p-t-20 db">Location Id</h5><small class="text-success db">{{ ucfirst($dispensary->location_id) }}</small>
                                    
                                    <h5 class="p-t-20 db">Location Name Website</h5><small class="text-success db">{{ ucfirst($dispensary->location_name_website) }}</small>
                                    <h5 class="p-t-20 db">Name</h5><small class="text-success db">{{ ucfirst($dispensary->name) }}</small>
                                    <h5 class="p-t-20 db">Location Phone Website</h5><small class="text-success db">{{ $dispensary->location_phone_website }}</small>
                                    <h5 class="p-t-20 db">Phone Number</h5><small class="text-success db">{{ $dispensary->phone_code.' '.$dispensary->phone_number }}</small>
                                    <h5 class="p-t-20 db">Email</h5><small class="text-success db">{{ $dispensary->location_email }}</small>
                                    <h5 class="p-t-20 db">Country</h5><small class="text-success db">{{ ucfirst($dispensary->country) }}</small>
                                    
                                    <h5 class="p-t-20 db">Location State Website</h5><small class="text-success db">{{ ucfirst($dispensary->location_state_website) }}</small>
                                    <h5 class="p-t-20 db">State</h5><small class="text-success db">{{ ucfirst($dispensary->state) }}</small>
                                    <h5 class="p-t-20 db">City</h5><small class="text-success db">{{ ucfirst($dispensary->city) }}</small>
                                    <h5 class="p-t-20 db">Latitude/Longtitude</h5><small class="text-success db">{{ $dispensary->lat.'/'.$dispensary->lng }}</small>
                                    
                                    <h5 class="p-t-20 db">Location Address Website</h5><small class="text-success db">{{ ucfirst($dispensary->location_address_website) }}</small>
                                    <h5 class="p-t-20 db">Address</h5><small class="text-success db">{{ ucfirst($dispensary->address) }}</small>
                                    <h5 class="p-t-20 db">Url</h5><small class="text-success db">{{ ucfirst($dispensary->location_url) }}</small>

                                    <h5 class="p-t-20 db">Created On</h5><small class="text-success db">{{date('Y, M d', strtotime($dispensary->created_at))}}</small>
                                    <h5 class="p-t-20 db">Status</h5><small class="text-success db">{{ucfirst(config('constants.STATUS.'.$dispensary->status))}}</small>
                                    <h5 class="p-t-20 db">Description</h5><small class="text-success db">{!! $dispensary->description !!}</small>
                                    <h5 class="p-t-20 db">Website Times</h5>
                                    @if(!empty($dispensary->location_times_website))
                                        @foreach($dispensary->location_times_website as $v)
                                            <small class="text-success db">{{ $v }},</small>
                                        @endforeach
                                    @endif
                                    <h5 class="p-t-20 db">Times</h5>
                                    @if(!empty($dispensary->location_times))
                                        @foreach($dispensary->location_times as $v)
                                            <small class="text-success db">{{ $v }},</small>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="card-body">
                                <form class="form-horizontal form-material" method="post" action="{{route('changeStatusDispensary')}}">
                                    {{csrf_field()}}
                                    <div class="form-group bt-switch">
                                        <div class="col-md-6">
                                            <label class="col-md-6" for="val-block">Status</label>
                                            <input type="hidden" name="statusid" value="{{$dispensary->id}}">
                                            <input type="hidden" name="status" value="{{$dispensary->status}}">
                                            <div class="col-md-2" style="float: right;">
                                                <input type="checkbox" @if($dispensary->status == 'active') checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" id="statusDispensary">
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
            
            $('#statusDispensary').on('switchChange.bootstrapSwitch', function (event, state) {

                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusDispensary").is(':checked'))
                    $('input[name=status]').val('active');
                else
                    $('input[name=status]').val('inactive');
            });
        });
    </script>
@endpush
