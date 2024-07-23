@extends('layouts.backend.app')
@section('title', 'Locations')

@section('content')
	<div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">Locations</h3> </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('dispensaries')}}">Locations</a></li>
                    <li class="breadcrumb-item active">Bulk Import</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Import</h4>

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{$url}}" enctype="multipart/form-data">

                            {{ csrf_field() }}
                            <div class="sampleSheet col-12">
                                <div class="dt-buttons">
                                    <a href="{{ asset('public/download/Sample_location_File.csv') }}" class="btn dt-button py-2" download>Download Sample File</a>
                                </div>
                                <div class="variantRow">
                                    <h6>Points to Remember:</h6>
                                    <p>
                                        <ol>
                                            <li>Please download the sample csv sheet from the button above before importing the data.</li>
                                            <li>Multiple csv file make single zip file and upload it.</li>
                                            <li>In Location description, you can add any type of data: HTML or plain text.</li>
                                            <li>Do not leave any blank row between two new Locations.</li>
                                        </ol>
                                    </p>
                                </div>
                            </div>
                            @include('layouts.backend.message_middle')
                            @include('layouts.backend.message')
                            <div class="col-md-12 p-0">
                                <div class="form-group col-md-6 m-t-20 float-left">
                                    <label>File</label><sup class="text-reddit"> *</sup>

                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                        <div class="form-control" data-trigger="fileinput"> <i class="glyphbanner glyphbanner-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file(Allowed Extensions - .csv,.zip)</span> <span class="fileinput-exists">Change</span>
                                        <input type="file" required name="location_import" accept=".csv,.zip" > </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Upload</button>
                                <a href="{{route('dispensaries')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Logs</h4>

                        <div class="table-responsive m-t-40">
                            <table id="logsTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Title</th>
                                        <th>Description</th>
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
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.2.7/js/dataTables.select.min.js"></script>
    <script type="text/javascript">
        $(function(){
            var table = $('#logsTable').DataTable({
                "ajax": {
                    url:"{{route('locationLogsAjax')}}",
                    dataSrc:"data",
                    // type: "get"
                },
                 paging: true,
                pageLength: 100,
                "bProcessing": true,
                "bServerSide": true,
                "bLengthChange": true,
                "aoColumns": [
                    { "data": "sno" },
                    { "data": "title" },
                    { "data": "description" },
                ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                "columnDefs": [
                    {"targets": [0],"orderable": false},
                ],
                "aaSorting": [],
            });

        });
    </script>
@endpush