<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>Boom Sprayer</title>
    <link rel="shortcut icon" type="img/png" href="{{ url('/img/logo.png') }}"/>
    
    <!--head-->
    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap/dist/css/bootstrap.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/font-awesome/css/font-awesome.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/Ionicons/css/ionicons.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/select2/dist/css/select2.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') !!}
    <!-- daterange picker -->
    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap-daterangepicker/daterangepicker.css') !!}
    {!! Html::style('AdminLTE-2.4.18/plugins/timepicker/bootstrap-timepicker.min.css') !!}
    {!! Html::style('bootstrapvalidator-0.5.2/css/bootstrapValidator.min.css')!!}
    {!! Html::style('AdminLTE-2.4.18/dist/css/AdminLTE.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/dist/css/skins/skin-green.min.css')!!}
    <!-- Pace style -->
    {!! Html::style('AdminLTE-2.4.18/plugins/pace/pace.min.css')!!}
    {!! Html::style('jquery.bootgrid-1.3.1/jquery.bootgrid.min.css')!!}
    {!! Html::style('css/app.min.css')!!}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <script>
        var BASE_URL = "{{ url('/') }}";
        var CSRF = "{{ csrf_token() }}";
    </script>
    <style>
        .form-group {
            margin-bottom: 5px;
        }
        .btn {
            margin-bottom: 2px;
        }
    </style>
    @yield('style')
    <!--end-->
</head>

<!--<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">-->
<body class="hold-transition skin-green sidebar-mini">
    <div class="wrapper">
        @include('header')
        @include('navigation')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @if (Session::has('message') || $errors->any())
                <section class="content-header section-alert">
                @if (Session::has('message'))
                <div class="alert alert-info alert-dismissable alert-custom">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ Session::get('message') }}
                </div>
                @endif
                @if ($errors->any())
                <div class='alert alert-danger alert-dismissable'>
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    @foreach ( $errors->all() as $error )
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif
                </section>
            @endif

            <!-- Main Content -->
            @yield('content')
        </div>
        <!-- Main Footer -->
        @include('footer')
    </div>
    <!-- /#wrapper -->

    <!-- JS -->
    {!! Html::script('AdminLTE-2.4.18/bower_components/jquery/dist/jquery.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/bootstrap/dist/js/bootstrap.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/moment/min/moment.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/bootstrap-daterangepicker/daterangepicker.js') !!}
    {!! Html::script('AdminLTE-2.4.18/plugins/timepicker/bootstrap-timepicker.min.js') !!}
    {!! Html::script('bootstrapvalidator-0.5.2/js/bootstrapValidator.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/select2/dist/js/select2.full.min.js') !!}
    {!! Html::script('js/plugins/sweetalert.min.js') !!}
    {!! Html::script('jquery.bootgrid-1.3.1/jquery.bootgrid.js') !!}
    {!! Html::script('jquery.bootgrid-1.3.1/jquery.bootgrid.fa.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/dist/js/adminlte.min.js') !!}
    {!! Html::script('js/numeral.min.js') !!}
    {!! Html::script('js/app.js') !!}
    <!-- PACE -->
    {!! Html::script('AdminLTE-2.4.18/bower_components/PACE/pace.min.js') !!}
    @yield('script')
</body>

</html>
