@extends('layouts.backend.app')
@section('title', 'Support')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Support</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Support</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">
                        <h4 class="card-title">Support</h4>

                        <div class="table-responsive m-t-40">
                            <table id="supportsTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Comment</th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Comment</th>
                                        <!-- <th>Action</th> -->
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
    		var table = $('#supportsTable').DataTable({
                "ajax": {
                    url:"{{route('supportsAjax')}}",
                    dataSrc:"data",
                    // type: "get"
                },
                paging: true,
                pageLength: 50,
"lengthMenu": [10, 20, 50, 100],
lengthChange: true,
                "bProcessing": true,
                "bServerSide": true,
                "bLengthChange": true,
                "aoColumns": [
                    { "data": "sno" },
                    { "data": "name" },
                    { "data": "email" },
                    { "data": "description" },
                    //{ "data": "action" },
                ],
                buttons: [
                    'pageLength',
                ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                "columnDefs": [
                    {"targets": [0,3],"orderable": false},
                ],
                "aaSorting": [],
		    });

    	});
    </script>
@endpush