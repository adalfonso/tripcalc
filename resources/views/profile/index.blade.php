@extends('layout')

@section('content')
<div id="profile">
	<div id="profile-info" class="left-col section">
		<h2>{{ $profile->first_name }} {{ $profile->last_name }}</h2>
		<h4 id="profile-username">{{ $profile->username }}</h4>
		@if(Auth::user()->activated)
			@yield('friendship')
		@endif
		@yield('personal')
	</div>
	<div id="profile-feed" class="right-col section">
		<h2>Profile Feed</h2>
		<p>{{ $profile->first_name }} did something.</p>
		<p>{{ $profile->first_name }} did something else.</p>
		<p>{{ $profile->first_name }} did something else... again.</p>
	</div>
</div>
@stop
