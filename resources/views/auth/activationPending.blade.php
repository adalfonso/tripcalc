@extends('layout')

@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>
		<logout></logout>
	</nav>
@overwrite

@section('content')
	<div style="margin: .5rem">

		@if(Session::has('response'))
			<p><strong>** {{ Session::get('response') }} **</strong></p>
		@endif

		<h3>Account activation pending</h3>
		<p>Help us help you. We first need to know that you aren't a robot or something. Please follow the activation link in an email we sent you.</p>
		<p><a href="/user/activation/resend">Click here</a> if you need us to resend that for you!</p>

	</div>
@stop
