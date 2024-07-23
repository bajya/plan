@extends('layouts.backend.app')
@section('title', 'Product Favourite')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Product Favourite</h3> </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Product Favourite</li>
                </ol>
            </div>
        </div>
            @include("layouts.backend.filter")
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">
                        <h4 class="card-title">Product Favourite</h4>
                        <div class="dt-buttons float-right">
                            <input type="hidden" name="user_id" value="{{ $_GET['id'] }}">
                        </div>
                        <h6 class="card-subtitle">Export data to Excel, PDF</h6>
                        <div class="table-responsive m-t-40">
                            <table id="usersTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Days</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Days</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')

    <script src="{{URL::asset('/plugins/datatables/jquery.dataTables.min.js')}}"></script>
   <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript">
    	$(function(){
            var table;
            function myDataTableFunction(){
                    table = $('#usersTable').DataTable({
                    "ajax": {
                        url:"{{route('usersFavProdAjax')}}",
                        dataSrc:"data",
                        type:"POST",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data:{
                            from_date: $('input[name=from_date]').val(),
                            end_date: $('input[name=end_date]').val(),
                            status: $('select[name=status]').val(),
                            user_id: $('input[name=user_id]').val(),
                        }
                        // type: "get"
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
                        { "data": "sno" },
                        { "data": "name" },
                        { "data": "image" },
                        { "data": "status" },
                        { "data": "pause" },
                        { "data": "action" },
                    ],
                    "drawCallback": function(settings){
                        $(".bt-switch input[type='checkbox']").bootstrapSwitch();
                    },

                    dom: 'Bfrtip',
                    buttons: [
                        'pageLength',
                        {
                            extend: 'pdf',
                            exportOptions: {columns: '1,2,3'},
                            pageSize: 'LETTER',
                            customize: function(doc, config) {
                                doc.pageOrientation = 'landscape';
                            }
                        },
                        {extend: 'excel',exportOptions: {columns: '1,2,3'}},
                    ],
                    select: {
                        style: 'multi',
                        selector: 'td:first-child'
                    },
                    "columnDefs": [
                        {"targets": [0,1,2,3,4,5],"orderable": false},
                        {"targets": [], visible: false}
                    ],
                    "aaSorting": [],
                });
            }
            if ($.fn.DataTable.isDataTable("#usersTable")) {
                $('#usersTable').DataTable().clear().destroy();
            }
            myDataTableFunction();
            $('#filter_form').on('submit', function(e) {
                e.preventDefault();
                if ($.fn.DataTable.isDataTable("#usersTable")) {
                  $('#usersTable').DataTable().clear().destroy();
                }
                myDataTableFunction();
            });
            $(".reset").on('click', function(e) {
                 e.preventDefault();
                $(this).closest('form').find("input[type=text], input[type=number], input[type=email], input[type=radio], input[type=checkbox], textarea, select").val("");
               
                if ($.fn.DataTable.isDataTable("#usersTable")) {
                  $('#usersTable').DataTable().clear().destroy();
                }
                myDataTableFunction();
            });
            
    	});
    </script>
@endpush