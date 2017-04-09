@extends('auth.layout')

@section('form')
<form id="login" role="form" method="POST" action="{{ url('/login') }}">

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif
    @if (session('warning'))
        <p><strong>{{ session('warning') }}</strong></p>
    @endif

    <h2>Log In</h2>
    <p>Don't have an account? <a href="/signup">Sign up</a></p>
    <p>
        {{-- <a class="subtext" href="{{ url('/password/reset') }}">
            Forgot Your Password?
        </a> --}}
    </p>

    {{ csrf_field() }}

    <input type="text" class="transparent" placeholder="Email / Username"
        name="login" value="{{ old('login') }}" required autofocus>
    @if ($errors->has('login'))
        <p>{{ $errors->first('login') }}</p>
    @endif

    <input type="password" placeholder="Password" class="form-control"
        name="password" required>

    <div class="ui-inputDuo clearfix">
        <div class="rememberMe first" @click="toggleRemember">

            <input type="checkbox" name="remember" class="hidden" :checked="remember">

            <div class="ui-input-btn" v-html="rememberIcon"></div>
            <div class="input-placeholder transparent hasBtn centered">
                Remember Me
            </div>

        </div>

        <button type="submit" class="btn-full btn-thin second">Login</button>
    </div>

    @if ($errors->has('resendActivationEmail'))
        <a class="margin-bottom" ng-show="data.resendActivationEmail"
            @click="resendActivationEmail">
            Resend my activation email, please!
        </a>
    @endif
</form>
@stop

@section('vue')
    <script>
        new Vue({
            el: '#app',

            data: { remember: false },

            created() {
                this.remember = {{ old('remember') ? 'true' : 'false'}};

                document.querySelector('#body').style.paddingTop = 0;
            },

            computed: {
                rememberIcon() {
                    return this.remember ?  '&#10004;' : '';
                }
            },

            methods: {
                toggleRemember() {
                    this.remember = !this.remember;
                }
            }
        });
    </script>
@overwrite
