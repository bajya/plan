@extends('layouts.backend.app')
@section('title', 'Edit CMS - '.$cms->name)

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} CMS</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('cms')}}">CMS</a></li>
                    <li class="breadcrumb-item active">Edit CMS</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">

                        <h4>Edit {{$cms->name}} Content</h4>
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

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{route('updateCMS',['id'=>$cms->id])}}" enctype="multipart/form-data">

                            {{csrf_field()}}

                            @if($cms->slug == 'faq')
                                <div class="dt-buttons float-right">
                                    <a href="javascript:void(0)" class="btn dt-button toolTip m-l-20 float-right addFaq" data-placement="bottom" title="Add FAQ">Add FAQ</a>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="no_faqs" id="no_faqs" value="0">
                                </div>
                                <div id="faqs">
                                </div>
                            @else
                                <div class="form-group col-md-12 m-t-20">
                                    <label>{{$cms->name}}</label><sup class="text-reddit"> *</sup>
                                    <textarea class="form-control form-control-line" name="cms_content" rows="10">{{old($cms->content, $cms->content)}}</textarea>
                                </div>

                            @endif


                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('cms')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>

    <div id="dummyRow" style="display: none;">
        <div class="faqRow" id="row_0">
            <div class="float-right">
                <a href="javascript:void(0)" class="deleteRow" id="deleterow_0"><i class="fa fa-trash" aria-hidden="true"></i></a>
            </div><br>
            <input type="hidden" name="faqid_0" value="0">
            @if($cms['slug']=='faq')
                <input type="hidden" name="faqtype_0" value="faq">
            @endif
            <div class="row">
                <div class="form-group bt-switch col-md-12 m-t-20">
                    <label class="col-md-4">Question</label>
                    <textarea class="form-control form-control-line" name="question_0" rows="5"></textarea>
                </div>
                <div class="form-group bt-switch col-md-12 m-t-20">
                    <label class="col-md-4">Answer</label>
                    <textarea class="form-control form-control-line" name="answer_0" rows="5"></textarea>
                </div>
                <input type="hidden" name="faqstatus_0" value="active">
                <div class="form-group bt-switch col-md-6 m-t-20">
                    <label class="col-md-4">Status</label>
                    <div class="col-md-3" style="float: right;">
                        <input type="checkbox" data-on-color="success" checked data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="val-faqstatus_0" class="faqStatus" id="faqstatus_0">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{URL::asset('/js/jquery-mask-as-number.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('textarea[name=cms_content]').summernote({
                height: 350, 
                minHeight: null, 
                maxHeight: null, 
                focus: false, 
                lineWrapping:true,
                prettifyHtml:true,
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('textarea[name=cms_content]').val($('textarea[name=cms_content]').summernote('isEmpty') ? "" : contents);

                        $('form').data('validator').element($('textarea[name=cms_content]'));
                        $('textarea[name=cms_content]').rules('add','check_content');
                        $('textarea[name=cms_content]').valid();
                    }
                }
            });

            var count = {{count($faqs)}};
            if(count==0){
                addRow();
                $('.deleteRow').hide();
            }
            else{
                var row = 1;

                @foreach($faqs as $value)
                    addRow();
                    $('#faqs').find('input[name=faqid_'+row+']').val("{{$value->id}}");
                    $('#faqs').find('input[name=faqtype_'+row+']').val("{{$value->type}}");
                    $('#faqs').find('textarea[name=question_'+row+']').val("{{$value->question}}");
                    $('#faqs').find('textarea[name=answer_'+row+']').val("{{htmlentities($value->answer)}}");

                    $('#faqs').find('textarea[name=answer_'+row+']').summernote('pasteHTML' ,"{!! $value->answer !!}");

                    var state="{{$value->status}}" == 'active' ? true:false;

                    $('#faqs').find('input[name=val-faqstatus_'+row+']').bootstrapSwitch('state', "{{$value->status}}" == 'active' ? true:false, false);
                    if(!state){
                        var width = Number($('#faqs').find('input[name=val-faqstatus_'+row+']').closest('.bootstrap-switch').css('width').split('px')[0])+10;
                        $('#faqs').find('input[name=val-faqstatus_'+row+']').closest('.bootstrap-switch').css('width',width+'px');
                    }
                    // $('#faqs input[name=val-faqstatus_'+row+']').bootstrapSwitch();

                    //variation cannot be deleted once placed in cart or ordered
                    if(count > 1){
                        $('#faqs').find('#deleterow_'+count).show();
                    }
                    else{
                        $('#faqs').find('#deleterow_'+count).hide();
                    }
                    row++;
                @endforeach
            }

            function addRow(){
                var row = Number($('#no_faqs').val())+1;
                var html = $('#dummyRow').html();
                var updated = html.replace(/_0/g, '_'+row);
                $('#faqs').append(updated);
                $('#no_faqs').val(row);
                $('.deleteRow').show();
                addRules(row);
            }

            function addRules(id){
                $('form').data('validator').settings.ignore = ".note-editor *";
                $('textarea[name=question_'+id+']').rules('add','required');

                $(document).find('textarea[name=answer_'+id+']').summernote({
                    height: 200, 
                    minHeight: null, 
                    maxHeight: null, 
                    focus: false, 
                    lineWrapping: true,
                    prettifyHtml: true,
                    callbacks: {
                        onChange: function(contents, $editable) {

                            $('textarea[name=answer_'+id+']').val($('textarea[name=answer_'+id+']').summernote('isEmpty') ? "" : contents);

                            $('form').data('validator').element($('textarea[name=answer_'+id+']'));
                            $('textarea[name=answer_'+id+']').rules('add','check_content');
                            $('textarea[name=answer_'+id+']').valid();
                        }
                    }
                }).on('summernote.paste', function (customEvent, nativeEvent) {
                    setTimeout(function () {
                        // $('.note-editable').selectText();
                        $('textarea[name=answer_'+id+']').summernote("removeFormat");
                    }, 100);
                });;
                $('textarea[name=answer_'+id+']').rules('add','required');
                $('#faqs input[name=val-faqstatus_'+id+']').bootstrapSwitch();

            }

            function removeRules(id){
                $('textarea[name=question_'+id+']').rules('remove','required');
                $('textarea[name=answer_'+id+']').rules('remove','required');
            }

            $(document).on('click','.deleteRow',function(){
                var id = $(this).attr('id').split('_')[1];
                var deleteId = $('input[name=faqid_'+id+']').val();
                var selft = $(this);

                if(deleteId>0){
                    $.ajax({
                        type: "post",
                        url: "{{route('deleteFAQ')}}",
                        data: {id: deleteId},
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

                            toastr.error("Unable to delete faq.","Status",{
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
                }

                removeRules(id);
                selft.closest('.faqRow').remove();
                $('#no_faqs').val($('#no_faqs').val()-1);
                if($('#no_faqs').val() == 1)
                    $('.deleteRow').hide();

                var count = 1;
                $('#faqs .faqRow').each(function(index,elem){
                    var id = $(elem).attr('id').split('_')[1];
                    $(elem).attr('id','row_' + count);
                    $('#faqs').find('textarea[name=answer_'+id+']').summernote('destroy');
                    var question = $('#faqs').find('textarea[name=question_'+id+']').val();
                    var answer = $('#faqs').find('textarea[name=answer_'+id+']').val();
                    var faqstatus = $('#faqs').find('input[name=faqstatus_'+id+']').val();
                    $('#faqs input[name=val-faqstatus_'+id+']').bootstrapSwitch('destroy');

                    var replace = "_"+id;
                    var re = new RegExp(replace,"g");
                    var html = $(this).html().replace(re, '_' + count);
                    $(elem).html(html);

                    //check this
                    $('#faqs').find('textarea[name=question_'+count+']').val(question);
                    $('#faqs').find('textarea[name=answer_'+count+']').val(answer);
                    $('#faqs').find('input[name=faqstatus_'+count+']').val(faqstatus);

                    addRules(count);
                    var state= (faqstatus == 'active') ? true:false;

                    $('#faqs').find('input[name=val-faqstatus_'+count+']').bootstrapSwitch('state', state, false);
                    if(!state){
                        var width = Number($('#faqs').find('input[name=val-faqstatus_'+count+']').closest('.bootstrap-switch').css('width').split('px')[0])+10;
                        $('#faqs').find('input[name=val-faqstatus_'+count+']').closest('.bootstrap-switch').css('width',width+'px');
                    }

                    count++;
                });

            })

            $('.addFaq').click(function(){
                addRow();
                $(this).find('.deleteRow').show();
            })

            $(document).on('switchChange.bootstrapSwitch', 'input[name^=val-faqstatus]', function (event, state) {
                var x = $(this).data('on-text');
                var y = $(this).data('off-text');
                var id = $(this).attr('id').split('_')[1];

                if($(this).is(':checked'))
                    $('input[name=faqstatus_'+id+']').val('active');
                else
                    $('input[name=faqstatus_'+id+']').val('inactive');
            });

        });
    </script>
@endpush