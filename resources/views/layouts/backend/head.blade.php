<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- Tell the browser to be responsive to screen width -->
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<meta name="csrf-token" content="{{csrf_token()}}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/apple-icon.png')}}">
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png')}}">
<title>{{ config('app.name', 'Laravel') }}</title>

<meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}">
<meta property="og:title" content="{{ config('app.name', 'Laravel') }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="en">
<meta property="og:url" content="{{url('/')}}">
<meta property="og:image" content="{{ asset('images/logo.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ config('app.name', 'Laravel') }}">
<meta name="description" content="{{ config('app.name', 'Laravel') }} has ads available in India of goods for buy from services and services listings. Buy something today!" />
<meta property="og:description" content="{{ config('app.name', 'Laravel') }} has ads available in India of goods for buy from services and services listings. Buy something today!"> 
<link rel="canonical" href="{{url('/')}}"> 
<link rel="stylesheet" href="{{URL::asset('/plugins/bootstrap/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/font-awesome/css/font-awesome.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/mdi/css/materialdesignicons.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/toastr/toastr.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/vendor.bundle.base.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/vendor.bundle.addons.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/styles.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/style.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/animate.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/custom.css')}}">
<link rel="stylesheet" href="{{URL::asset('/css/pages/file-upload.css')}}">
<link href="{{URL::asset('/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css')}}" rel="stylesheet">
<link href="{{URL::asset('/plugins/bootstrap-switch/bootstrap-switch.min.css')}}" rel="stylesheet">
<link href="{{URL::asset('/css/pages/bootstrap-switch.css')}}" rel="stylesheet">
<link href="{{URL::asset('/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
<link href="{{URL::asset('/plugins/nestable/nestable.css')}}" rel="stylesheet" type="text/css" />
<link href="{{URL::asset('/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet">
<link href="{{URL::asset('/plugins/bootstrap-select/bootstrap-select.min.css')}}" rel="stylesheet">
<script src="{{URL::asset('/plugins/jquery/jquery.min.js')}}"></script>
<link href="{{URL::asset('/plugins/summernote/summernote.css') }}" rel="stylesheet">
<script type="text/javascript">
    //var APP_NAME ="{{ url('/') }}";
    var APP_NAME ="{{ env('APP_URL') }}";
   // var APP_NAME ="https://Laravel.com/appAdmin";
    //alert(APP_NAME);
</script>