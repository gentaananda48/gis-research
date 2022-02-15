<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boom Sprayer</title>
    <link rel="shortcut icon" type="img/png" href="{{ url('/img/logo.png') }}"/>
    <!--head-->
    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap/dist/css/bootstrap.min.css') !!}
    {!! Html::style('AdminLTE-2.4.18/bower_components/font-awesome/css/font-awesome.min.css') !!}
    {!! Html::style('bootstrapvalidator-0.5.2/css/bootstrapValidator.min.css')!!}
    {!! Html::style('AdminLTE-2.4.18/dist/css/AdminLTE.min.css') !!}
    <style>
      .login-page {
        position: relative; 
        height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #077B04;
      }
      .login-page::before {    
        content: "";
        background-image: url(/img/background-2.jpg);
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
        position: absolute;
        top: 0px;
        right: 0px;
        bottom: 0px;
        left: 0px;
        opacity: 0.75;
      }
      .login-box {
        max-width: 400px;
        margin: auto; 
        opacity: 0.85;
        padding: 5px;
        background-color: #014601;
        border-radius: 8px;
      }
      .login-logo {
        text-align: center;
      }
      .login-logo img {
        width: 200px;
        height: auto;
      }
      .login-box .box {
        border-radius: 8px;
        margin: 0px !important;
        background-color: #F5F6F5;
      }
    </style>
  </head>
  <body class="login-page">
    <div class="login-box">
      <div class="box box-solid">
        <div class="box-body">
          <div class="login-logo">
            <img src="{{ url('/img/logo.png') }}" alt="logo">
          </div>
          <form class="login-form" method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}
            @if ($errors->any())
            <div class='alert alert-danger alert-dismissable'>
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                @foreach ( $errors->all() as $error )
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif
            <div class="form-group{{ $errors->has('identity') ? ' has-error' : '' }}">
                <label for="identity" class="control-label">Username </label>
                <input id="identity" class="form-control" type="text" name="identity" placeholder="Username" required autofocus autocomplete="off">
            </div>
            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password" class="control-label">Password</label>
                <input id="password" class="form-control" type="password" name="password" placeholder="Password" required autocomplete="off">
            </div>
            <div class="form-group btn-container">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fa fa-sign-in fa-lg fa-fw"></i>Sign In
                </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    {!! Html::script('AdminLTE-2.4.18/bower_components/jquery/dist/jquery.min.js') !!}
    {!! Html::script('AdminLTE-2.4.18/bower_components/bootstrap/dist/js/bootstrap.min.js') !!}
    {!! Html::script('bootstrapvalidator-0.5.2/js/bootstrapValidator.min.js') !!}
    <script>
        $(function () {
            $('.login-form').bootstrapValidator({
                container: 'tooltip',
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    identity: {
                        validators: {
                            notEmpty: {
                                message: "Required"
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "Required"
                            }
                        }
                    }
                }
            });
        });
    </script>
  </body>
</html>
