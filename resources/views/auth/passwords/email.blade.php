@extends('auth.layout')

<!-- Main Content -->
@section('content')
    <h2 class="centered">Reset Password</h2>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form role="form" class="form-small" method="POST" action="{{ url('/password/email') }}">
        {{ csrf_field() }}

        <input id="email" type="email" class="form-control" name="email"
            value="{{ old('email') }}" placeholder="Email" required>

        @if ($errors->has('email'))
            <p>{{ $errors->first('email') }}</p>
        @endif

        <button type="submit" class="btn-full btn-thin">
            Send Password Reset Link
        </button>
    </form>
@endsection
