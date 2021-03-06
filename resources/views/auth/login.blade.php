@extends('auth.layout')

@section('form')
<form id="login" class="form-small form-transparent" role="form" method="POST" action="{{ url('/login') }}">

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif
    @if (session('warning'))
        <p><strong>{{ session('warning') }}</strong></p>
    @endif

    <h2>Log In</h2>

    <p>
        Don't have an account?
        <a class="paleBlue" href="/signup" >Sign up</a>
    </p>

    <p>
        <a class="subtext" href="{{ url('/password/reset') }}">
            Forgot your Password?
        </a>
    </p>

    {{ csrf_field() }}

    <input type="text" placeholder="Email / Username" name="login"
        value="{{ old('login') }}" required autofocus>
    @if ($errors->has('login'))
        <p>{{ $errors->first('login') }}</p>
    @endif

    <input type="password" placeholder="Password" name="password" required>

    <div class="ui-input-duo clearfix">
        <div class="rememberMe" @click="toggleRemember">

            <input type="checkbox" name="remember" class="hidden" :checked="remember">

            <div class="ui-input-btn" v-html="rememberIcon"></div>
            <div class="input-fake transparent hasBtn centered">
                Remember Me
            </div>

        </div>

        <button type="submit" class="btn-full btn-thin">Login</button>
    </div>
</form>
@stop

@section('vue')
    <script>
        new Vue({
            el: '#app',

            data: { remember: false },

            created() {
                this.remember = {{ old('remember') ? 'true' : 'false' }};

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
