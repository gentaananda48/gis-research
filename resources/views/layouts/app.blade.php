<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-BBZWF67BCH"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-BBZWF67BCH');
    </script>

    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap/dist/css/bootstrap.min.css') !!}
    {!! Html::style('css/app.css')!!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/font-awesome/css/font-awesome.min.css') !!}
    {!! Html::style('bootstrapvalidator-0.5.2/css/bootstrapValidator.min.css')!!}
    
    <title>Surat Perintak Kerja</title>
    <link rel="shortcut icon" type="img/png" href="/img/logo.png"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries-->
    <!--if lt IE 9
    script(src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js')
    script(src='https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js')
    -->
  </head>
  <body>
    @yield('content')
    <!-- Javascripts-->
    {!! Html::script('AdminLTE-2.4.18/bower_components/jquery/dist/jquery.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/bootstrap/dist/js/bootstrap.min.js') !!}
    {!! Html::script('bootstrapvalidator-0.5.2/js/bootstrapValidator.min.js') !!}
    {!! Html::script('/js/plugins/pace.min.js') !!}
    @yield('script')
  </body>
</html>