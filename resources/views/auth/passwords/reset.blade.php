@extends('auth.layout')

@section('content')

<div style="margin: 0 .6rem;">
    <h2 class="centered">Reset Password</h2>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form role="form" class="form-small" method="POST" action="{{ url('/password/reset') }}">

        {{ csrf_field() }}

        <input type="hidden" name="token" value="{{ $token }}">

        @if ($errors->has('email'))
                <p>{{ $errors->first('email') }}</p>
        @endif
        <input id="email" type="email" name="email" placeholder="Email"
            value="{{ $email or old('email') }}" placeholder="Email" required autofocus>

        @if ($errors->has('password'))
            <p>{{ $errors->first('password') }}</p>
        @endif
        <input id="password" type="password" name="password" placeholder="Password" required>

        @if ($errors->has('password_confirmation'))
            <p>{{ $errors->first('password_confirmation') }}</p>
        @endif
        <input id="password-confirm" type="password" name="password_confirmation"
            placeholder="Confirm Password" required>

        <button type="submit" class="btn-full btn-thin">Reset Password</button>
    </form>
</div>

@endsection
