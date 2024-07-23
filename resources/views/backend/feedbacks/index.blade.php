@extends('layouts.backend.app')
@section('title', 'Feedback')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Feedback</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Feedback</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">
                        <h4 class="card-title">Feedback</h4>

                        <div class="table-responsive m-t-40">
                            <table id="feedbacksTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Device</th>
                                        <th>Smiley</th>
                                        <th>Subject</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Device</th>
                                        <th>Smiley</th>
                                        <th>Subject</th>
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
    		var table = $('#feedbacksTable').DataTable({
                "ajax": {
                    url:"{{route('feedbacksAjax')}}",
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
                    { "data": "smiley" },
                    { "data": "category" },
                    { "data": "action" },
                ],
                dom: 'Bfrtip',
                    buttons: [
                        'pageLength',
                    ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                "columnDefs": [
                    {"targets": [0,1,2,3,4],"orderable": false},
                    {"targets": [], visible: false}
                ],
                "aaSorting": [],
		    });

    	});
    </script>
@endpush