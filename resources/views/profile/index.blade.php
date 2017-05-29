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

		@php
			$path = $profile->currentPhoto->thumbnailPath ?? null;
		@endphp

		<profile-photo :path="'{{ $path }}'" :id="{{ $profile->id }}"
			:uploadable="{{ (integer) $profile->isCurrentUser() }}">
		</profile-photo>

		<h4 id="profile-username">{{ $profile->username }}</h4>

		@if($profile->about)
			<p>{{ $profile->about}}</p>
		@endif

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

		<post-form ref="post" :id="{{$profile->id}}" :type="'profile'"></post-form>

		<profile-feed :id="{{$profile->id}}" :feed="{{json_encode($posts)}}"
			:is-owner="{{ $profile->id === Auth::id() ? 1 : 0 }}">
		</profile-feed>

	</div>
</div>

@stop
