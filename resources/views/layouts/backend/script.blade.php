<script src="{{URL::asset('/js/vendor.bundle.base.js')}}"></script>
<script src="{{URL::asset('/js/vendor.bundle.addons.js')}}"></script>
<script src="{{URL::asset('/js/off-canvas.js')}}"></script>
<script src="{{URL::asset('/js/misc.js')}}"></script>
<script src="{{URL::asset('/js/jasny-bootstrap.js')}}"></script>

<script src="{{URL::asset('/plugins/bootstrap-switch/bootstrap-switch.min.js')}}"></script>
<script src="{{URL::asset('/plugins/styleswitcher/jQuery.style.switcher.js')}}"></script>
<script src="{{URL::asset('/js/jquery.validate.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/plugins/nestable/jquery.nestable.js')}}"></script>
<script src="{{URL::asset('/js/jquery.validate.min.js')}}"></script>
<script src="{{URL::asset('/js/jquery.validate-init.js')}}"></script>
<script src="{{URL::asset('/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>
<script src="{{URL::asset('/plugins/bootstrap-select/bootstrap-select.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/plugins/summernote/summernote.js') }}"></script>


        @stack('scripts')


        <script>
            $(document).ready(function() {
              var res = '{{ Request::segment(2)}}';
              if (res == 'states') {
                $('.allow').removeClass('active');
              }
              if (res == 'allowstates') {
                $('.state').removeClass('active');
              }
            });
           var $loading = $('#ajaxSpinnerImage').hide();
           $(document)
             .ajaxStart(function () {
                 $loading.show();
             })
           .ajaxStop(function () {
                $loading.hide();
            });
        </script>
        <script type="text/javascript">
            $('div.alert').delay(10000).slideUp(500);

            $(".bt-switch input[type='checkbox']:visible").bootstrapSwitch();
            $(".tab-pane .bt-switch input[type='checkbox']").bootstrapSwitch();
            $(".preloader").fadeOut();

            $('body').tooltip({selector: '[data-toggle="tooltip"]'});
            $('body').tooltip({selector: '[data-toggle="popover"]'});

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#from_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false }).on('change', function(e, date){
                $('#end_date').bootstrapMaterialDatePicker('setMinDate', date);
                $('#end_date').val('');
            });
            $('#end_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });

            $('#start_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false, minDate: new Date() }).on('change', function(e, date){
                $('#end_date').bootstrapMaterialDatePicker('setMinDate', date);
                $('#end_date').val('');
            });
            $('#end_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });

            $('form').submit(function(e){
                if($(this).valid()){
                    $('button[type="submit"]').prop('disabled',true);
                }else{
                    $('button[type="submit"]').prop('disabled',false);
                }
            });
        </script>