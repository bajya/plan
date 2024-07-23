@extends('layouts.backend.app')
@section('title', ucfirst($type).' Product')

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Product</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('products')}}">Products</a></li>
                    <li class="breadcrumb-item active">{{ucfirst($type)}} Product</li>
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
                            <h4>Fill In Product Details</h4>
                        @elseif($type == 'edit')
                            <h4>Edit Product Details</h4>
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
                        @php $pdi=1;  @endphp
                        <form class="form-material m-t-50 row form-valide" method="post" action="{{$url}}" enctype="multipart/form-data">

                            {{csrf_field()}}
                            <div class="col-md-12 p-0">
                                <div class="form-group col-md-6 m-t-20 float-left">
                                    <label>Front Image</label><sup class="text-reddit"> *</sup>
                                    <input type="hidden" name="image_exists" id="image_exists{{$pdi}}" value="1">

                                    @if($type == 'add' || ($type == 'edit' && $product->image == null))
                                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                            <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span>
                                            <input type="file" required name="product_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                            <p>upload this size for better resolution(480x385)</p>
                                        </div>
                                    @elseif($type == 'edit')
                                        <br>
                                        <div id="productImage{{$pdi}}">
                                            <img src="@if($product->image != null && file_exists(public_path('/uploads/products/'.$product->image))){{URL::asset('/uploads/products/'.$product->image)}}@endif" width="70" />
                                            &nbsp;&nbsp;&nbsp;<a id="changeImage{{$pdi}}" onclick="remove('{{$pdi++}}')" href="javascript:void(0)" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Delete">Change</a>


                                        </div>
                                    @endif
                                </div>
                               <?php /* <div class="form-group col-md-6 m-t-20" id="parentcol">
                                    <label>Location <i class="fa fa-question-circle" class="aria-hidden" data-toggle="tooltip" data-placement="top" title="Location"></i></label>
                                    <select class="form-control" name="dispensary_id" id="dispensary_id">
                                        @if(count($dispensaries) > 0)
                                            <option value='0'>Select Location</option>
                                            @if($dispensaries)
                                                @foreach($dispensaries as $id=> $des)
                                                    <option value="{{$des->id}}" @if($product->dispensary_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Location found</option>
                                        @endif
                                    </select>
                                </div> */?>
                                <div class="form-group col-md-6 m-t-20" id="brandcol" >
                                    <label>Company</label><sup class="text-reddit"> *</sup>
                                    <select class="form-control" name="brand_id" id="brand_id">
                                        @if(count($brands) > 0)
                                            <option value='0'>Select Company</option>
                                            @if($brands)
                                                @foreach($brands as $id=> $des)
                                                    <option value="{{$des->id}}" @if($product->brand_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                                @endforeach
                                            @endif
                                        @else
                                            <option value=''>No Company found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Location<i class="fa fa-question-circle" class="aria-hidden" data-toggle="tooltip" data-placement="top" title="Location"></i></label><sup class="text-reddit"> *</sup>
                                <select class="form-control" name="dispensary_id" id="dispensary_id">
                                    <option value='0'>Select Location</option>
                                    @if(count($dispensarys) > 0)
                                        @if($dispensarys)
                                            @foreach($dispensarys as $id=> $des)
                                                <option value="{{$des->id}}" @if($product->dispensary_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                            @endforeach
                                        @endif
                                    @else
                                        <option value=''>No Location found</option>
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-6 m-t-20" id="typecol">
                                <label>Strain</label>
                                <select class="form-control" name="strain_id" id="strain_id">
                                    @if(count($strains) > 0)
                                        <option value=''>Select Strain</option>
                                        @if($strains)
                                            @foreach($strains as $id=> $des)
                                                <option value="{{$des->id}}" @if($product->strain_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                            @endforeach
                                        @endif
                                    @else
                                        <option value=''>No Strain found</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-6 m-t-20" id="typecol">
                                <label>Category</label>
                                <select class="form-control" name="category_id" id="category_id">
                                    @if(count($categories) > 0)
                                        <option value=''>Select Category</option>
                                        @if($categories)
                                            @foreach($categories as $id=> $des)
                                                <option value="{{$des->id}}" @if($product->parent_id ==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                            @endforeach
                                        @endif
                                    @else
                                        <option value=''>No Category found</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-6 m-t-20" id="typecol">
                                <label>Type</label>
                                <select class="form-control" name="type_id" id="type_id">
                                    @if(count($producttypes) > 0)
                                        <option value=''>Select Type</option>
                                        @if($producttypes)
                                            @foreach($producttypes as $id=> $des)
                                                <option value="{{$des->id}}" @if($product->type_id==$des->id) selected @endif>{{ ucfirst($des->name) }}</option>
                                            @endforeach
                                        @endif
                                    @else
                                        <option value=''>No Type found</option>
                                    @endif
                                </select>
                            </div>
                            
                            <?php /*<div class="form-group col-md-6 m-t-20">
                                <label>Strain</label><sup class="text-reddit"> *</sup>
                                <select class="form-control" name="strain_id" id="strain_id">
                                    @php
                                        $strain = 0;
                                        if($type=='edit'){
                                            $strain = (isset($product->strain->id) ? $product->strain->id :$product->substrain->parentCat->id) . ( isset($product->substrain->id) && ($product->substrain->id != null) ? '-' . $product->substrain->id : '');
                                        }
                                        $parents = [];
                                    @endphp
                                    @foreach($strains as $id=>$cat)
                                        @php
                                            $ids = explode('-',$id);
                                            $parents[$ids[0]] = count($ids);
                                        @endphp
                                    @endforeach
                                    @if(count($strains) > 0)
                                        <option value=''>Select Strain</option>
                                        @foreach($strains as $id=>$cat)
                                            @php
                                                $ids = explode('-',$id);
                                                $parent = $ids[0];
                                            @endphp
                                            <option value="{{$id}}" @if($strain==$id) selected @endif @if($parents[$parent] > 0 && $parent == $id) disabled @else style="color: #000" @endif>{!!$cat!!}</option>
                                        @endforeach
                                    @else
                                        <option value=''>No starin found</option>
                                    @endif
                                </select>
                            </div> */?>
                            <div class="form-group col-md-6 m-t-20" id="colorcol" style="display: none;">
                                <label>Price Color</label>
                                <select class="form-control" name="price_color_code" id="price_color_code">
                                    <option value='#3AAA35' @if($product->price_color_code=='#3AAA35') selected @endif>Green</option>
                                    <option value='#FFC200' @if($product->price_color_code=='#FFC200') selected @endif>yellow</option>
                                    <option value='#888888' @if($product->price_color_code=='#888888') selected @endif>gray</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Name</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="product_name" value="{{old('product_name', $product->name)}}" placeholder="Please enter name" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>SKU</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="product_sku" value="{{old('product_sku', $product->product_sku)}}" placeholder="Please enter product sku" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Amount</label>
                                <input type="text" class="form-control form-control-line" name="amount" value="{{old('amount', $product->amount)}}" placeholder="Please enter amount ex.3.5g" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Thc</label>
                                <input type="text" class="form-control form-control-line" name="thc" value="{{old('thc', $product->thc)}}" placeholder="Please enter thc ex.24%" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Cbd</label>
                                <input type="text" class="form-control form-control-line" name="cbd" value="{{old('cbd', $product->cbd)}}" placeholder="Please enter cbd ex.Varies" maxlength="100">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>MRP</label><sup class="text-reddit"> *</sup>
                                <input type="number" class="form-control form-control-line" name="price" value="{{old('price', $product->price)}}" placeholder="Please enter price">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Discount MRP</label>
                                <input type="number" class="form-control form-control-line" name="discount_price" value="{{old('discount_price', $product->discount_price)}}" placeholder="Please enter discount price">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Quantity</label><sup class="text-reddit"> *</sup>
                                <input type="number" class="form-control form-control-line" name="qty" value="{{old('qty', $product->qty)}}" placeholder="Please enter Quantity">
                            </div> 
                            <div class="form-group col-md-6 m-t-20">
                                <label>Image Url</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="image_url" value="{{old('image_url', $product->image_url)}}" placeholder="Please enter image url" maxlength="200">
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label>Product Url</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="product_url" value="{{old('product_url', $product->product_url)}}" placeholder="Please enter product url" maxlength="200">
                            </div>
                            <div class="form-group col-md-12 m-t-20 summernoteDiv">
                                <label>Description</label><sup class="text-reddit"> *</sup>
                                <textarea class="form-control form-control-line check_content" name="prod_description" rows="5" placeholder="Please enter description">{{old('prod_description', $product->description)}}</textarea>
                            </div>
                            <input type="hidden" name="manage_stock" value="@if(isset($product) && $product->manage_stock != null) {{$product->manage_stock}} @else 1 @endif">
                            <div class="form-group bt-switch col-md-6 m-t-20">
                                <label class="col-md-4">Manage Stock</label>
                                <div class="col-md-3" style="float: right;">
                                    <input type="checkbox" @if($type == 'edit') @if(isset($product) && $product->manage_stock == '1') checked @endif @endif data-on-color="success" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="mini" name="val-manage_stock" id="manageStock">
                                </div>
                            </div>
                            <input type="hidden" name="is_featured" value="@if(isset($product) && $product->is_featured != null) {{$product->is_featured}} @else 0 @endif">
                            <div class="form-group bt-switch col-md-6 m-t-20">
                                <label class="col-md-4">Featured </label>
                                <div class="col-md-3" style="float: right;">
                                    <input type="checkbox" @if($type == 'edit') @if(isset($product) && $product->is_featured == '1') checked @endif @endif data-on-color="success" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="mini" name="val-is_featured" id="quickGrab">
                                </div>
                            </div>
                            <input type="hidden" name="status" value="@if(isset($product) && $product->status != null) {{$product->status}} @else active @endif">
                            <div class="form-group bt-switch col-md-6 m-t-20">
                                <label class="col-md-4">Status</label>
                                <div class="col-md-3" style="float: right;">
                                    <input type="checkbox" @if($type == 'edit') @if(isset($product) && $product->status == 'active') checked @endif @else checked @endif data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-status" id="statusCat">
                                </div>
                            </div>
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('products')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
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
<script>
$(document).ready(function() {
    $('#brand_id').on('change', function() {
        var id = this.value;
        var attr_modal = 'Dispensary';
        var select = 'Select Location';
        $.ajax({
            type: "post",
            url: "{{route('on_changeAjax')}}",
            data: {id: id,attr_modal:attr_modal,select:select},
            success: function(res)
            {
                $('#dispensary_id').html(res);
                //$('#strain_id').html('<option value="">Select Strain</option>');
            },
            error: function(data)
            {

            }
        });
    });
    $('#dispensary_id').on('change', function() {
        /*var id = this.value;
        var brand_id = $("#brand_id option:selected").val();
        var attr_modal = 'Strain';
        var select = 'Select Strain';
        $.ajax({
            type: "post",
            url: "{{route('on_changeAjax')}}",
            data: {id: id,brand_id: brand_id,attr_modal:attr_modal,select:select},
            success: function(res)
            {
                $('#strain_id').html(res);
            },
            error: function(data)
            {

            }
        });*/
    });
    $('#category_id').on('change', function() {
        var id = this.value;
        var attr_modal = 'Category';
        var select = 'Select Type';
        $.ajax({
            type: "post",
            url: "{{route('on_changeAjax')}}",
            data: {id: id,attr_modal:attr_modal,select:select},
            success: function(res)
            {
                $('#type_id').html(res);
                
            },
            error: function(data)
            {

            }
        });
    });
    
});
</script>
    <script type="text/javascript">

        function remove(as)
        {
            $('#productImage'+as).parent().append('<div class="fileinput fileinput-new input-group" data-provides="fileinput"><div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span><input type="file" required name="product_image" accept=".jpg, .jpeg, .png"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a></div>');
            $('.tooltip').tooltip('hide');
            $('#productImage'+as).remove();
            $('#image_exists'+as).val(0);
        }

        $(function(){

            $('textarea[name=prod_description]').summernote({
                height: 350, 
                minHeight: null, 
                maxHeight: null, 
                focus: false, 
                lineWrapping:true,
                prettifyHtml:true,
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('textarea[name=prod_description]').val($('textarea[name=prod_description]').summernote('isEmpty') ? "" : contents);

                        $('form').data('validator').element($('textarea[name=prod_description]'));
                        $('textarea[name=prod_description]').rules('add','check_content');
                        $('textarea[name=prod_description]').valid();
                    }
                }
            });
            $(document).on('keyup',".decimalInput, .numberInput",function(e){

                if($(this).val().indexOf('-') >=0){
                    $(this).val($(this).val().replace(/\-/g,''));
                }
            })

            //$(document).find(".numberInput").maskAsNumber({receivedMinus:false});
            //$(document).find(".decimalInput").maskAsNumber({receivedMinus:false,decimals:6});
            $(document).on('change', 'select[name=category_id]', function(){
                if($('input[name=product_name]').val()!='')
                    $('input[name=product_name]').valid();
            });

            $(document).on('switchChange.bootstrapSwitch', 'input[name^=val-prodstatus]', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                var id = $(this).attr('id').split('_')[1];

                if($(this).is(':checked'))
                    $('input[name=prodstatus_'+id+']').val('AC');
                else
                    $('input[name=prodstatus_'+id+']').val('IN');
            });

            $('#statusCat').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#statusCat").is(':checked'))
                    $('input[name=status]').val('AC');
                else
                    $('input[name=status]').val('IN');
            });

            $('#manageStock').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#manageStock").is(':checked'))
                    $('input[name=manage_stock]').val('1');
                else
                    $('input[name=manage_stock]').val('0');
            });

            $('#quickGrab').on('switchChange.bootstrapSwitch', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                if($("#quickGrab").is(':checked'))
                    $('input[name=is_featured]').val('1');
                else
                    $('input[name=is_featured]').val('0');
            });

           

            $('form').data('validator').settings.ignore = ".note-editor *";
            $(document).find('textarea[name=prod_description]').summernote({
                height: 350, 
                minHeight: null, 
                maxHeight: null, 
                focus: false, 
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('textarea[name=prod_description]').val($('textarea[name=prod_description]').summernote('isEmpty') ? "" : contents);

                        $('form').data('validator').element($('textarea[name=prod_description]'));
                        $('textarea[name=prod_description]').rules('add','check_content');
                        $('textarea[name=prod_description]').valid();
                    }
                }
            });
            $('#changeImage').click(function(){
                $('#productImage').parent().append('<div class="fileinput fileinput-new input-group" data-provides="fileinput"><div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions -  .jpg, .jpeg, .png, .gif, .svg)</span> <span class="fileinput-exists">Change</span><input type="file" required name="product_image"> </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a></div>');
                $('.tooltip').tooltip('hide');
                $('#productImage').remove();
                $('#image_exists').val(0);
            });

            @if($type == 'edit')
                $('input[name=product_name]').rules('add', {remote: {
                    url: "{{ url('/admin/products/checkProduct/ ' . $product->id) }}",
                    type: "post",
                    data: {  
                        category: function() {
                            return $( "#category_id" ).val();
                        }
                    }
                }});
            @endif
        });
    </script>
@endpush