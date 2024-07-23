@extends('layouts.backend.app')
@section('title', ucfirst($type).' Location')

@section('content')
<style type="text/css">
    #map{
        height: 300px !important; 
    }
</style>
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Location</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('dispensaries')}}">Locations</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($type)}} Location</li>
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
                            <h4>Fill In Location Details</h4>
                        @elseif($type == 'edit')
                            <h4>Edit Location Details</h4>
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
                                    <label>Image</label><sup class="text-reddit"> *</sup>
                                    <input type="hidden" name="image_exists" id="image_exists" value="1">

                                    @if($type == 'add' || ($type == 'edit' && $dispensary->image == null))
                                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                            <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span>
                                            <input type="file" required name="dis_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                        </div>
                                    @elseif($type == 'edit')
                                        <br>
                                        <div id="catImage">

                                            <img src="@if($dispensary->image != null && file_exists(public_path('/uploads/brands/'.$dispensary->image))){{URL::asset('/uploads/brands/'.$dispensary->image)}}@endif" width="70"" />
                                            &nbsp;&nbsp;&nbsp;<a id="changeImage" href="javascript:void(0)" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Delete">Change</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group col-md-6 m-t-20" id="brandcol">
                                    <label>Company</label>
                                    <select class="form-control" name="brand_id" id="type_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}" @if($dispensary->brand_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Name</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="dis_name" value="{{old('dis_name', $dispensary->name)}}" maxlength="100">
                            </div> 
                            <div class="form-group col-md-6 m-t-20">
                                <label>Location Id</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="location_id" value="{{old('location_id', $dispensary->location_id)}}" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Phone Number</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="phone_number" value="{{old('phone_number', $dispensary->phone_number)}}" placeholder="Please enter phone number">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Location Email</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="location_email" value="{{old('location_email', $dispensary->location_email)}}" placeholder="Please enter location email">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Location URL</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="location_url" value="{{old('location_url', $dispensary->location_url)}}" placeholder="Please enter location url">
                            </div>
                            <div class="form-group col-md-12 m-t-20">
                                <label>Location/City/Address</label><sup class="text-reddit"> *</sup>
                                <input type="text" name="address" id="address" value="{{old('address', $dispensary->address)}}" class="form-control form-control-line" placeholder="">
                                <input type="hidden" id="country" name="country" class="form-control form-control-line" value="{{ isset($dispensary->country) && !empty($dispensary->country) ? $dispensary->country : ''}}">
                                <input type="hidden" id="state" name="state" class="form-control form-control-line" value="{{ isset($dispensary->state) && !empty($dispensary->state) ? $dispensary->state : ''}}">
                                <input type="hidden" id="city" name="city" class="form-control form-control-line" value="{{ isset($dispensary->city) && !empty($dispensary->city) ? $dispensary->city : ''}}">
                            </div>
                            <div class="form-group col-md-12 m-t-20" id="mapArea">
                                <label>Google Map</label><sup class="text-reddit"> *</sup>
                                <div id="map" class="shopMap"></div>
                            </div>
                            <div class="col-md-12 p-0">
                                <div class="form-group col-md-6 m-t-20 float-left" id="latitudeArea">
                                    <label>Latitude</label><sup class="text-reddit"> *</sup>
                                    <input type="text" id="latitude" name="lat" class="form-control form-control-line" value="{{ isset($dispensary->lat) && !empty($dispensary->lat) ? $dispensary->lat : '-7.0157404'}}" readonly>
                                </div> 
                                <div class="form-group col-md-6 m-t-20" id="longtitudeArea">
                                    <label>Longitude</label><sup class="text-reddit"> *</sup>
                                    <input type="text" name="lng" id="longitude" class="form-control form-control-line" value="{{ isset($dispensary->lng) && !empty($dispensary->lng) ? $dispensary->lng : '110.4171283'}}" readonly>
                                </div>
                            </div>
                            <div class="form-group col-md-12 m-t-20">
                                <label>Description</label><sup class="text-reddit"> *</sup>
                                <textarea class="form-control form-control-line" name="description" rows="5">{{old('description', $dispensary->description)}}</textarea>
                            </div>

                            <input type="hidden" name="status" value="@if(isset($dispensary) && $dispensary->status != null) {{$dispensary->status}} @else active @endif">
                            <div class="form-group bt-switch col-md-6 m-t-20">
                                <label class="col-md-4">Status</label>
                                <div class="col-md-3" style="float: right;">
                                    <input type="checkbox" @if($type == 'edit') @if(isset($dispensary) && $dispensary->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusCat">
                                </div>
                            </div>
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('dispensaries')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
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
                    <h5 class="m-t-10 text-danger changeOffer">Are you sure you want to removed Location?.</h5>
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


            $('#changeImage').click(function(){
                $('#catImage').parent().append('<div class="fileinput fileinput-new input-group" data-provides="fileinput"><div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span><input type="file" required name="dis_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a></div>');
                $('.tooltip').tooltip('hide');
                $('#catImage').remove();
                $('#image_exists').val(0);
            });

            /*@if($type == 'edit')
                $('input[name=dis_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/dispensaries/checkDispensary/{{$dispensary->id}}",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});

              
            @else
                $('input[name=dis_name]').rules('add', {remote: {
                    url: APP_NAME + "/admin/dispensaries/checkDispensary",
                    type: "post",
                    data: {
                      parent: function() {
                        return $( "#parent_id" ).val();
                      }
                    }
                  }});
            @endif*/
            $('.confirmBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

            $('.cancelBtn').click(function(){
                $('#confirmDeleteModal').modal('hide');
            });

        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('MAP_API_KEY') }}&callback=initMap&libraries=places&v=weekly" async></script>
    <script>
        /*$(document).ready(function () {
            $("#latitudeArea").addClass("d-none");
            $("#longtitudeArea").addClass("d-none");
            $("#mapArea").addClass("d-none");
        });*/
    </script>
    <script>
         function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: parseFloat($('#latitude').val()),
                    lng:  parseFloat($('#longitude').val())
                },
            zoom: 12
            });
            var input = (document.getElementById('address'));
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);
            var myLatlng = new google.maps.LatLng(parseFloat($('#latitude').val()), parseFloat($('#longitude').val()));
            var geocoder = new google.maps.Geocoder();
            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29),
                position: myLatlng,
                draggable: true
            });
            google.maps.event.addListener(marker, 'dragend', function() {
                geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            console.log(results[0]);
                            var address = results[0].formatted_address;
                            var city = '';
                            var state = '';
                            var country = '';
                            var place_name = '';
                            if (results[0].address_components) {
                                var arrComponents = results[0].address_components;
                                for (var i = 0; i < arrComponents.length; i++)
                                {
                                    var types = arrComponents[i].types[0];
                                    if (types == "premise") {
                                       place_name = arrComponents[i].short_name;
                                    }
                                    if (types == "locality" || types == "political" || types == 'sublocality') {
                                       city = arrComponents[i].long_name;
                                    }
                                    if (types == "administrative_area_level_1" || types == "political") {
                                       state = arrComponents[i].long_name;
                                    }
                                    if (types == "country" || types == "political") {
                                       country = arrComponents[i].long_name;
                                    }
                                }
                            }
                            $("input[name=address]").val(address);
                            $('#country').val(country);
                            $('#state').val(state);
                            $('#city').val(city);
                            $('#latitude').val(marker.getPosition().lat().toFixed(5));
                            $('#longitude').val(marker.getPosition().lng().toFixed(5));
                            $("#latitudeArea").removeClass("d-none");
                            $("#longtitudeArea").removeClass("d-none");
                            $("#mapArea").removeClass("d-none");
                            infowindow.setContent('<div><strong>' + place_name + '</strong><br>' + address);
                            infowindow.open(map, marker);
                        }
                    }
                });
            })
            autocomplete.addListener('place_changed', function() {
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            if (!place.geometry) {
              window.alert("Autocomplete's returned place contains no geometry");
              return;
            }
            if (place.geometry.viewport) {
              map.fitBounds(place.geometry.viewport);
            } else {
              map.setCenter(place.geometry.location);
              map.setZoom(12); 
            }
            marker.setIcon(({
              url: "{{ asset('images/red.png')}}",
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(17, 34),
              scaledSize: new google.maps.Size(35, 35)
            }));
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
            console.log(place);
            var address = place.formatted_address;
            var city = '';
            var state = '';
            var country = '';
            if (place.address_components) {
                var arrComponents = place.address_components;
                for (var i = 0; i < arrComponents.length; i++)
                {
                    var types = arrComponents[i].types[0];
                    if (types == "locality" || types == "political" || types == 'sublocality') {
                       city = arrComponents[i].long_name;
                    }
                    if (types == "administrative_area_level_1" || types == "political") {
                       state = arrComponents[i].long_name;
                    }
                    if (types == "country" || types == "political") {
                       country = arrComponents[i].long_name;
                    }
                }
            }
            $('#country').val(country);
            $('#state').val(state);
            $('#city').val(city);
            $('#latitude').val(place.geometry.location.lat().toFixed(5));
            $('#longitude').val(place.geometry.location.lng().toFixed(5));
            $("#latitudeArea").removeClass("d-none");
            $("#longtitudeArea").removeClass("d-none");
            $("#mapArea").removeClass("d-none");
            infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            infowindow.open(map, marker);
          });
          function setupClickListener(id, types) {
                var radioButton = document.getElementById(id);
                radioButton.addEventListener('click', function() {
                  autocomplete.setTypes(types);
                });
          }
          setupClickListener('changetype-all', []);
          setupClickListener('changetype-address', ['address']);
          setupClickListener('changetype-establishment', ['establishment']);
          setupClickListener('changetype-geocode', ['geocode']);
        }


    </script>


@endpush