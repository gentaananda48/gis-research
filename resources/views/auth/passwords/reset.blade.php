@extends('layouts.app')

@section('content')
<section class="material-half-bg">
    <div class="cover"></div>
</section>
<section class="login-content">
    <div class="logo">
        <h1>CREANOVA ERP</h1>
    </div>
    <div class="login-box">
        <form class="login-form" method="POST" action="{{ route('password.request') }}">
            {{ csrf_field() }}
            <input type="hidden" name="token" value="{{ $token }}">
            <h3 class="login-head"><i class="fa fa-lg fa-fw fa-lock"></i>Reset Password</h3>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}"">
                <label for="email" class="control-label">Email</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" placeholder="Email" required autofocus>
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password" class="control-label">Password</label>
                <input id="password" class="form-control" type="password" name="password" placeholder="Password">
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                <label for="password-confirmation" class="control-label">Password Confirmation</label>
                <input id="password-confirm" class="form-control" type="password" name="password_confirmation" placeholder="Password Confirmation">
                @if ($errors->has('password_confirmation'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group btn-container">
                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-unlock fa-lg fa-fw"></i>Reset</button>
            </div>
        </form>
    </div>
</section>
@endsection

@section('script')
    <script>
        $(function () {
            $('form').bootstrapValidator({
                container: 'tooltip',
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    email: {
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
@stop


