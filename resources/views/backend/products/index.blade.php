@extends('layouts.backend.app')
@section('title', 'Products')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Products</h3> </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Products</li>
                </ol>
            </div>
        </div>
        <form action="" method="get" id="filter_form">
        @include("layouts.backend.filter")
        </form>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">
                        <h4 class="card-title">Products</h4>

                        @if (App\Library\Helper::checkAccess(route('createProduct')))
                            <!-- <div class="dt-buttons float-right">
                                <a href="{{ route('exportView') }}" data-toggle="tooltip" data-placement="bottom" title="Only quantity can be updated" class="toolTip btn dt-button py-2">Stock Update</a>
                            </div>
                            <div class="dt-buttons float-right">
                                <a href="javascript:;" data-toggle="tooltip" data-placement="bottom" title="Use this file for bulk update" class="toolTip bulkExport btn dt-button py-2">Export</a>
                            </div> -->
                            <div class="dt-buttons float-right">
                                <a href="{{route('imageUpload')}}" class="btn dt-button py-2">Bulk Image Upload</a>
                            </div>
                            <div class="dt-buttons float-right">
                                <a href="{{route('importProducts')}}" class="btn dt-button py-2">Bulk Import</a>
                            </div>
                            <div class="dt-buttons float-right">
                                <a href="{{route('createProduct')}}" class="btn dt-button py-2">Add Product</a>
                            </div>
                            <div class="dt-buttons float-right">
                                <a href="javascript:void(0)" data-href="{{route('changeStatusProductsAll')}}" data-status="inactive" class="btn dt-button py-2  bulkActionAll">Deactive All</a>
                            </div>
                            <div class="dt-buttons float-right">
                                <a href="javascript:void(0)" data-href="{{route('changeStatusProductsAll')}}" data-status="active" class="btn dt-button py-2 bulkActionAll">Active All</a>
                            </div>
                            
                        @endif
                        <div class="table-responsive m-t-40">
                            <div class="dt-buttons">
                                <a href="javascript:void(0)" data-href="{{route('deleteProducts')}}" class="btn btn-secondary disabled bulkAction deleteProducts">Delete</a>
                            </div>
                            <div class="dt-buttons">
                                <a href="javascript:void(0)" data-href="{{route('changeStatusProducts')}}" class="btn btn-secondary disabled bulkAction changeStatusProducts">Activate/Deactivate</a>
                            </div>

                            <table id="productsTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><div class="form-check form-check-flat selectAll"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_ids[]">Select</label></div></th>
                                        <th>Id</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Strain</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <!-- <th>Description</th> -->
                                        <th>Image</th>
                                        <th>In Stock</th>
                                        
                                        <!-- <th>Featured</th> -->
                                        <th>Status</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <!-- <th>Favorite Count</th> -->
                                        <th>Notification</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th><div class="form-check form-check-flat selectAll"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_ids[]">Select</label></div></th>
                                        <th>Id</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Strain</th>
                                        <th>Price</th>
                                        <th>Name</th>
                                        <!-- <th>Description</th> -->
                                        <th>Image</th>
                                        <th>In Stock</th>
                                        <!-- <th>Featured</th> -->
                                        <th>Status</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <!-- <th>Favorite Count</th> -->
                                        <th>Notification</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <div class="dt-buttons">
                            <a href="javascript:void(0)" data-href="{{route('deleteProducts')}}" class="btn btn-secondary disabled bulkAction deleteProducts">Delete</a>
                        </div>
                        <div class="dt-buttons">
                            <a href="javascript:void(0)" data-href="{{route('changeStatusProducts')}}" class="btn btn-secondary disabled bulkAction changeStatusProducts">Activate/Deactivate</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>

    <div class="modal fade" id="userstatusModal" tabindex="-1" role="dialog" aria-labelledby="userstatusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userstatusModalLabel">Confirm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-valide" method="post" id="blockForm" action="{{route('changeStatusProduct')}}">
                        {{csrf_field()}}
                        <input type="hidden" name="statusid" id="statusid">
                        <input type="hidden" name="status" id="status">
                        <h5 class="m-t-10 text-danger">Are you sure you want to <span class="categorystatus"></span> category : <span id="statuscode"></span></h5>
                        <button type="button" class="btn btn-secondary btn-flat cancelBtn m-b-30 m-t-30" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info btn-flat confirmBtn m-b-30 m-t-30">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- <form class="form-valide" method="post" id="deleteForm" action="{{route('deleteCategory')}}"> --}}
                        {{csrf_field()}}
                        <input type="hidden" name="deleteid" id="deleteid">
                        <input type="hidden" name="deleteurl" id="deleteurl">
                        <h5 class="m-t-10 text-danger">Are you sure you want to delete selected product(s)?</h5>
                        <button type="button" class="btn btn-secondary btn-flat cancelBtn m-b-30 m-t-30" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info btn-flat confirmDeleteBtn m-b-30 m-t-30">Confirm</button>
                    {{-- </form> --}}
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')

    <script src="{{URL::asset('/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.2.7/js/dataTables.select.min.js"></script> -->
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
       /* var id = this.value;
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
    $('#parent_id').on('change', function() {
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
    	$(function(){
        	var table;
            function myDataTableFunction(){
                table = $('#productsTable').DataTable({
                    "ajax": {
                        url:"{{route('productsAjax')}}",
                        dataSrc:"data",
                        type:"POST",
                        data:{
                            from_date: $('input[name=from_date]').val(),
                            end_date: $('input[name=end_date]').val(),
                            status: $('select[name=status]').val(),
                            manage_stock: $('select[name=manage_stock]').val(),
                            is_featured: $('select[name=is_featured]').val(),
                            brand_id: $('select[name=brand_id]').val(),
                            parent_id: $('select[name=parent_id]').val(),
                            type_id: $('select[name=type_id]').val(),
                            strain_id: $('select[name=strain_id]').val(),
                            dispensary_id: $('select[name=dispensary_id]').val(),
                            price: $('select[name=price]').val(),
                            name: $('input[name=name]').val(),
                        }
                    },
                    paging: true,
                    pageLength: 50,
                    "lengthMenu": [10, 20, 50, 100],
                    lengthChange: true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "bLengthChange": true,
                    'serverMethod': 'post',
                    'searching': false,
                    "aoColumns": [
                        { "data": "select" },//0
                        { "data": "product_sku" },//1
                        { "data": "brand_id" },//2
                        { "data": "dispensary" },//3
                        { "data": "category_id" },//4
                        { "data": "type_id" },//5
                        { "data": "strain_id" },//6
                        { "data": "name" },//7
                        { "data": "price" },//7
                       // { "data": "description" },//5
                        { "data": "image" },//8
                        { "data": "manage_stock" },//7
                        //{ "data": "is_featured" },//8
                        { "data": "status" },//9
                        { "data": "activate" },//10
                        { "data": "action" },//11
                       // { "data": "fav_count" },//12
                        { "data": "notification" },//13
                    ],
                    "drawCallback": function(settings){
                        $(".bt-switch input[type='checkbox']").bootstrapSwitch();
                    },

    		        dom: 'Bfrtip',
                    buttons: [
                        'pageLength',
                        {
                            extend: 'pdf',
                            exportOptions: {columns: '1,2,3,4,5,6,7'},
                            pageSize: 'LETTER',
                            customize: function(doc, config) {
                                doc.pageOrientation = 'landscape';
                            }
                        },
                        {extend: 'excel',exportOptions: {columns: '1,2,3,4,5,6,7'}},
                    ],
                    select: {
                        style: 'multi',
                        selector: 'td:first-child'
                    },
                    "columnDefs": [
                        {"targets": [0,2,3,4,5,6,7,8,9,10,11,12,13,14],"orderable": false},
                        {"targets": [1,11], visible: false}
                    ],
                    "aaSorting": [],
    		    });
            }
            if ($.fn.DataTable.isDataTable("#productsTable")) {
                $('#productsTable').DataTable().clear().destroy();
            }
            myDataTableFunction();
            $('#filter_form').on('submit', function(e) {
                e.preventDefault();
                if ($.fn.DataTable.isDataTable("#productsTable")) {
                  $('#productsTable').DataTable().clear().destroy();
                }
                myDataTableFunction();
            });
            $(".reset").on('click', function(e) {
                 e.preventDefault();
                $(this).closest('form').find("input[type=text], input[type=number], input[type=email], input[type=radio], input[type=checkbox], textarea, select").val("");
               
                if ($.fn.DataTable.isDataTable("#productsTable")) {
                  $('#productsTable').DataTable().clear().destroy();
                }
                myDataTableFunction();
            });
            $(document).on('click','input[name="user_ids[]"]',function(){
                $(document).find('input[name="user_ids[]"]').prop('checked', $(this).prop('checked'));
                $(document).find('input[name="user_id[]"]').prop('checked', $(this).prop('checked'));
                var length = $('input[name="user_id[]"]:checked').length;

                if(length > 0)
                {
                    $('.deleteProducts').removeClass('disabled');
                    $('.changeStatusProducts').removeClass('disabled');
                    table.rows().select();
                }else{
                    $('.deleteProducts').addClass('disabled');
                    $('.changeStatusProducts').addClass('disabled');
                    table.rows().deselect();
                }
            });

            /*$('tbody').on( 'click', 'tr', function () {
                $(this).toggleClass('selected');
                var check = $(this).find('input[type=checkbox]');
                check.prop('checked',!check.prop("checked"));
                var length = $('input[name="user_id[]"]:checked').length;
                if(length > 0)
                {
                    $('.deleteProducts').removeClass('disabled');
                    $('.changeStatusProducts').removeClass('disabled');
                    table.rows().select();
                }else{
                    $('.deleteProducts').addClass('disabled');
                    $('.changeStatusProducts').addClass('disabled');
                    table.rows().deselect();
                }

            } );*/

            $(document).on('click','input[name="user_id[]"]',function(){
                var length = $('input[name="user_id[]"]:checked').length;
                var row = $(this).closest('tr');
                var index = row.index();

                if(length > 0)
                {
                    $('.deleteProducts').removeClass('disabled');
                    $('.changeStatusProducts').removeClass('disabled');
                    table.row(index).select();
                }else{
                    $('.deleteProducts').addClass('disabled');
                    $('.changeStatusProducts').addClass('disabled');
                    table.row(index).deselect();
                }

                if($(this).prop('checked'))
                {
                    table.row(index).select();
                }else{
                    table.row(index).deselect();
                }

                if(length == $('input[name="user_id[]"]').length){
                    $(document).find('input[name="user_ids[]"]').prop('checked', true);
                }else{
                    $(document).find('input[name="user_ids[]"]').prop('checked', false);
                }

            });

            $(document).on('switchChange.bootstrapSwitch', '.statusProduct', function (event, state) {
                var x;

                if($(this).is(':checked'))
                    x = 'active';
                else
                    x = 'inactive';

                var id = $(this).data('id');
                $('#statuscode').text($(this).data('code'));
                if(x == 'inactive')
                {
                    $('#statusid').val(id);
                    $('#status').val('inactive');
                    $('.categorystatus').text('deactivate');
                }
                else
                {
                    $('#statusid').val(id);
                    $('#status').val('active');
                    $('.categorystatus').text('activate');
                }

                $.ajax({
                    type: "post",
                    url: "{{route('changeStatusAjaxProduct')}}",
                    data: {statusid: id,status:x},
                    success: function(res)
                    {
                        var data = JSON.parse(res);
                        if(data.status == 1)
                        {
                            
                            table.ajax.reload();
                            toastr.success(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                        else
                        {
                            
                            table.ajax.reload();
                            toastr.error(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                    },
                    error: function(data)
                    {
                        
                            table.ajax.reload();
                        toastr.error("Unable to update product.","Status",{
                            timeOut: 5000,
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": true,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut",
                            "tapToDismiss": false

                        });

                    }
                });
                // $('#offerstatusModal').modal('show');
            });

            $(document).on('switchChange.bootstrapSwitch', '.stockProduct', function (event, state) {
                var x;

                if($(this).is(':checked'))
                    x = '1';
                else
                    x = '0';

                var id = $(this).data('id');
                

                $.ajax({
                    type: "post",
                    url: "{{route('changeStockAjaxProduct')}}",
                    data: {statusid: id,status:x},
                    success: function(res)
                    {
                        var data = JSON.parse(res);
                        if(data.status == 1)
                        {
                            
                            table.ajax.reload();
                            toastr.success(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                        else
                        {
                            
                            table.ajax.reload();
                            toastr.error(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                    },
                    error: function(data)
                    {

                            table.ajax.reload();
                        toastr.error("Unable to update product.","Status",{
                            timeOut: 5000,
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": true,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut",
                            "tapToDismiss": false

                        });

                    }
                });
            });
            $(document).on('click','.deleteProduct',function(){
                var id = $(this).data('id');
                $('#deleteid').val(id);
                $('#deleteurl').val("{{route('deleteProduct')}}");

                $('#confirmDeleteModal').modal('show');
            });

            $(document).on('click', '.confirmDeleteBtn', function (event, state) {

                $.ajax({
                    type: "post",
                    url: $('#deleteurl').val(),
                    data: {deleteid: $('#deleteid').val()},
                    success: function(res)
                    {
                        var data = JSON.parse(res);
                        if(data.status == 1)
                        {
                            toastr.success(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                            table.ajax.reload();
                            $('.deleteProducts').addClass('disabled');
                            $('.changeStatusProducts').addClass('disabled');
                        }
                        else
                        {
                            toastr.error(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                    },
                    error: function(data)
                    {

                        toastr.error("Unable to delete product.","Status",{
                            timeOut: 5000,
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": true,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut",
                            "tapToDismiss": false

                        });

                    }
                });
                $('#confirmDeleteModal').modal('hide');
            });

            $('.bulkAction').click(function(){
                var url = $(this).data('href');
                var id = $(this).attr('class');
                var ids = [];
                $.each($('input[name="user_id[]"]:checked'), function(){
                    ids.push($(this).val());
                });

                 if(url.includes('delete')){
                    $('#deleteid').val(ids.join(','));
                    $('#deleteurl').val(url);
                    $('#confirmDeleteModal').modal('show');
                }
                else{
                    $.ajax({
                        type: "post",
                        url: url,
                        data: {ids: ids},
                        success: function(res)
                        {
                            var data = JSON.parse(res);
                            if(data.status == 1)
                            {
                                table.ajax.reload();

                                $.each($('input[name="user_id[]"]:checked'), function(){
                                    $(this).prop('checked', false);
                                });
                                $.each($('input[name="user_ids[]"]:checked'), function(){
                                    $(this).prop('checked', false);
                                });

                                toastr.success(data.message,"Status",{
                                    timeOut: 5000,
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": true,
                                    "progressBar": true,
                                    "positionClass": "toast-top-right",
                                    "preventDuplicates": true,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "1000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut",
                                    "tapToDismiss": false

                                });
                            }
                            else
                            {
                                toastr.error(data.message,"Status",{
                                    timeOut: 5000,
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": true,
                                    "progressBar": true,
                                    "positionClass": "toast-top-right",
                                    "preventDuplicates": true,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "1000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut",
                                    "tapToDismiss": false

                                });
                            }

                            $('.deleteProducts').addClass('disabled');
                            $('.changeStatusProducts').addClass('disabled');
                        },
                        error: function(data)
                        {

                            toastr.error("Unable to update products.","Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });

                        }
                    })
                }
                $(this).blur();
            })


            $('.bulkActionAll').click(function(){
                var url = $(this).data('href');
                var status = $(this).data('status');
                $.ajax({
                    type: "post",
                    url: url,
                    data: {status: status},
                    success: function(res)
                    {
                        var data = JSON.parse(res);
                        if(data.status == 1)
                        {
                            table.ajax.reload();
                            toastr.success(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                        else
                        {
                            toastr.error(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }

                        $('.deleteProducts').addClass('disabled');
                        $('.changeStatusProducts').addClass('disabled');
                    },
                    error: function(data)
                    {

                        toastr.error("Unable to update products.","Status",{
                            timeOut: 5000,
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": true,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut",
                            "tapToDismiss": false

                        });

                    }
                })
                $(this).blur();
            })

            $(document).on('click','.sendNotificationAction',function(){
                var url = $(this).data('href');
                var id = $(this).data('id');
                $.ajax({
                    type: "post",
                    url: url,
                    data: {id: id},
                    success: function(res)
                    {
                        var data = JSON.parse(res);
                        if(data.status == 1)
                        {
                            table.ajax.reload();
                            toastr.success(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                        else
                        {
                            toastr.error(data.message,"Status",{
                                timeOut: 5000,
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": true,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "tapToDismiss": false

                            });
                        }
                    },
                    error: function(data)
                    {

                        toastr.error("Unable to update products.","Status",{
                            timeOut: 5000,
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": true,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut",
                            "tapToDismiss": false

                        });

                    }
                })
                $(this).blur();
            })

    	});
    </script>
    <script>
        $('.bulkExport').click(function(){
            var ids = $('input[name="user_id[]"]:checked').map(function() {
                return this.value;
            }).toArray();

            var url = "{{url('/admin/products/export/?id=')}}" + ids;
            window.open(url, '_blank');
        })
    </script>
@endpush