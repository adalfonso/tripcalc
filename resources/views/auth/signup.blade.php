@extends('auth.layout')

@section('form')
<form id="signup" role="form" method="POST" action="{{ url('/register') }}">
    <h2>Sign Up</h2>
    <p>Already have an account? <a a href="/login">Log in</a></p>

    {{ csrf_field() }}

    @if ($errors->has('first_name'))
        <p>{{ $errors->first('first_name') }}</p>
    @endif

    @if ($errors->has('last_name'))
        <p>{{ $errors->first('last_name') }}</p>
    @endif

    <input class="firstname" type="text" name="first_name" placeholder="First Name"
        maxlength="15" value="{{ old('first_name') }}" required autofocus>

    <input class="lastname" type="text" name="last_name" placeholder="Last Name"
        maxlength="15" value="{{ old('last_name') }}" required>

    @if ($errors->has('username'))
        <p>{{ $errors->first('username') }}</p>
    @endif
    <input type="text" name="username" placeholder="Username" maxlength="15"
        value="{{ old('username') }}" required>

    @if ($errors->has('email'))
        <p>{{ $errors->first('email') }}</p>
    @endif
    <input type="email" name="email" placeholder="Email" maxlength="60"
        value="{{ old('email') }}" required>

    @if ($errors->has('email_confirmation'))
        <p>{{ $errors->first('email_confirmation') }}</p>
    @endif
    <input type="email" name="email_confirmation" placeholder="Confirm Email"
        maxlength="60" value="{{ old('email_confirmation') }}" required>

    @if ($errors->has('password'))
        <p>{{ $errors->first('password') }}</p>
    @endif

    <input class="password1" type="password" name="password" placeholder="Password"
        maxlength="30" required>

    <input class="password2" type="password" name="password_confirmation"
        placeholder="Confirm Password" maxlength="30" required>

    <button class="btn-full btn-thin" type="submit">Sign Up</button>
</form>
@endsection