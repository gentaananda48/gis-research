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
        <form class="login-form" method="POST" action="{{ route('password.email') }}">
            {{ csrf_field() }}
            <h3 class="login-head"><i class="fa fa-lg fa-fw fa-lock"></i>Forgot Password ?</h3>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}"">
                <label class="control-label">Email</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required>
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group btn-container">
                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-unlock fa-lg fa-fw"></i>Reset</button>
            </div>
            <div class="form-group mt-20">
                <p class="semibold-text mb-0"><a href="{{ url("/login") }}"><i class="fa fa-angle-left fa-fw"></i> Back to Login</a></p>
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
                    }
                }
            });
        });
    </script>
@stop

