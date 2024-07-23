
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="csrf-token" content="{{csrf_token()}}">
        <title>Laravel - Privacy Policy</title>
        <link rel="stylesheet" href="{{URL::asset('/plugins/bootstrap/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{URL::asset('/css/styles.css')}}">
        <link rel="stylesheet" href="{{URL::asset('/css/style.css')}}">
        <link rel="shortcut icon" href="{{URL::asset('images/favicon.png')}}" />
    </head>
    <body class="fix-header fix-sidebar card-no-border">
        <div class="container-scroller">
            <div class="container-fluid page-body-wrapper">

			    <div class="content-wrapper">

			        {!! $cms->content !!}
			    </div>
			</div>
		</div>
	</body>
</html>