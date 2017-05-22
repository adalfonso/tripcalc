@extends('auth.layout')

<!-- Main Content -->
@section('content')
    <h2 class="centered">Reset Password</h2>

    <form role="form" class="form-small" method="POST" action="{{ url('/password/email') }}">

        @if (session('status'))
            <p><strong>{{ session('status') }}</strong></p>
        @endif

        {{ csrf_field() }}

        @if ($errors->has('email'))
            <p>{{ $errors->first('email') }}</p>
        @endif

        <input id="email" type="email" name="email"
            value="{{ old('email') }}" placeholder="Email" required>

        <button type="submit" class="btn-full btn-thin">
            Send Password Reset Link
        </button>
    </form>
@endsection
