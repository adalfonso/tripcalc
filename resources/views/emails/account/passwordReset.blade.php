<html>
	<head><title>Password Reset</title></head>
	<body>
		<h2>Hello!</h2>
		<p>You are receiving this email because we received a password reset request for your account.</p>
		<p><a href="{{ env('APP_URL') }}/password/reset/{{ $token }}">Reset Password</a></p>
		<p>If you did not request a password reset, no further action is required.</p>
	</body>
</html>
