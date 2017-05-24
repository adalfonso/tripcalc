@extends('layout')

@section('content')
<div id="profile">
	<div id="profile-info" class="left-col section">
		<h2>
			{{ $profile->first_name }} {{ $profile->last_name }}
			@if($profile->id === Auth::user()->id)
				<img class="btn-edit" src="/img/icon/edit.png"
					@click="profileInfoForm.visible = true">
			@endif
		</h2>

		<h4 id="profile-username">{{ $profile->username }}</h4>

		@if(Auth::user()->activated)
			@yield('friendship')
		@endif

		@yield('personal')

		@if($friends->count() > 0)
			@include('profile.friends')
		@endif

	</div>
	<div id="profile-feed" class="right-col section">
		<h2>Profile Feed</h2>
		<p>{{ $profile->first_name }} did something.</p>
		<p>{{ $profile->first_name }} did something else.</p>
		<p>{{ $profile->first_name }} did something else... again.</p>
	</div>
</div>
@stop
