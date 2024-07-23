<form method="POST" id="filter_form" class="fillter-inline" role="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Filters</h4>
                    <div class="row">
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input type="text" id="from_date" name="from_date" class="form-control" placeholder="Enter from date" value=''>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Enter end date" value=''>
                            </div>
                        </div>
                        @if(in_array(Request::segment(2), ["brands", "dispensaries", "users", "states", "allowstates", "doctors", "categories", "strains", "filemanagers", "products"]))
                            @if(in_array(Request::segment(3), ["fav"]))

                            @else
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" id="name" class="form-control name" placeholder="Please enter search value">
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if(in_array(Request::segment(2), ["types"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="parent_id">Category</label>
                                    <select class="form-control" name="parent_id" id="parent_id">
                                        @if(count($categorys) > 0)
                                            <option value=''>Select Category</option>
                                            @if($categorys)
                                                @foreach($categorys as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No category found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="name">Type</label>
                                    <input type="text" name="name" id="name" class="form-control name" placeholder="Please enter search value">
                                </div>
                            </div>
                        @endif
                       
                        @if(in_array(Request::segment(2), ["users"]))
                            
                        @endif

                        @if(in_array(Request::segment(2), ["roles","users","admins", "dispensaries", "states", "allowstates", "brands", "doctors", "categories", "types", "strains", "products"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control status" name="status">
                                        <option value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                       <?php /* @if(in_array(Request::segment(2), ["dispensaries"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="status">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["types"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="status">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif */?>
                        @if(in_array(Request::segment(2), ["dispensaries"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="status">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["doctors1"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="status">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["strains"]))
                            <div class="col-lg-3 col-md-4" style="display: none;">
                                <div class="form-group">
                                    <label for="brand_id">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4"  style="display: none;">
                                <div class="form-group">
                                    <label for="dispensary_id">Location</label>
                                    <select class="form-control" name="dispensary_id" id="dispensary_id">
                                        @if(count($dispensarys) > 0)
                                            <option value=''>Select Location</option>
                                            @if($dispensarys)
                                                @foreach($dispensarys as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Location found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["products"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="brand_id">Company</label>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value=''>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="dispensary_id">Location</label>
                                    <select class="form-control" name="dispensary_id" id="dispensary_id">
                                        @if(count($dispensarys) > 0)
                                            <option value=''>Select Location</option>
                                            @if($dispensarys)
                                                @foreach($dispensarys as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Location found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="parent_id">Category</label>
                                    <select class="form-control" name="parent_id" id="parent_id">
                                        @if(count($categorys) > 0)
                                            <option value=''>Select Category</option>
                                            @if($categorys)
                                                @foreach($categorys as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No category found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="type_id">Type</label>
                                    <select class="form-control" name="type_id" id="type_id">
                                        @if(count($producttypes) > 0)
                                            <option value=''>Select Type</option>
                                            @if($producttypes)
                                                @foreach($producttypes as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Type found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="strain_id">Strain</label>
                                    <select class="form-control" name="strain_id" id="strain_id">
                                        @if(count($strains) > 0)
                                            <option value=''>Select Strain</option>
                                            @if($strains)
                                                @foreach($strains as $id=> $des)
                                                    <option value="{{$des->id}}">{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Strain found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="manage_stock">In Stock</label>
                                    <select class="form-control manage_stock" name="manage_stock">
                                        <option value="">Select Option</option>
                                        <option value="0" {{ (isset($_GET['manage_stock'])) ? ($_GET['manage_stock']=='0') ? "selected" : "" : "" }}>No</option>
                                        <option value="1" {{ (isset($_GET['manage_stock'])) ? ($_GET['manage_stock']=='1') ? "selected" : "" : "" }}>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="is_featured">Featured</label>
                                    <select class="form-control is_featured" name="is_featured">
                                        <option value="">Select Option</option>
                                        <option value="0" {{ (isset($_GET['is_featured'])) ? ($_GET['is_featured']=='0') ? "selected" : "" : "" }}>No</option>
                                        <option value="1" {{ (isset($_GET['is_featured'])) ? ($_GET['is_featured']=='1') ? "selected" : "" : "" }}>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <select class="form-control price" name="price">
                                        <option value="">Select Option</option>
                                        <option value="asc">Lowest First</option>
                                        <option value="desc">Highest First</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["filemanagers"]))
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select class="form-control type" name="type">
                                        <option value="">Select Type</option>
                                        <option value="product">Product</option>
                                        <option value="doctor">Doctor</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if(in_array(Request::segment(2), ["products"]))
                        <div class="col-lg-6 col-md-12 d-flex align-items-center">
                        </div>
                            <div class="col-lg-6 col-md-12 d-flex align-items-center">
                                <center>
                                <div class="form-group flex-column flex-md-row">
                                    <input type="submit" class="btn btn-success mt-2" value="Search">
                                    <a href="javascript:void(0);" class="btn waves-effect waves-light btn-primary mt-2 reset">Reset</a>
                                </div></center>
                            </div>
                        @else
                            <div class="col-lg-3 col-md-12 d-flex align-items-center">
                                <div class="form-group flex-column flex-md-row">
                                    <input type="submit" class="btn btn-success mt-2" value="Filter">
                                    <a href="javascript:void(0);" class="btn waves-effect waves-light btn-primary mt-2 reset">Reset</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
