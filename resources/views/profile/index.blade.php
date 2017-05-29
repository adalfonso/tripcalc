@extends('layout')

@section('content')
<div id="profile">
	<div id="profile-info" class="left-col section">

		@php
			$path = $profile->currentPhoto->thumbnailPath ?? null;
		@endphp

		<div class="profile-info">
			<profile-photo :path="'{{ $path }}'" :id="{{ $profile->id }}"
				:uploadable="{{ (integer) $profile->isCurrentUser() }}">
			</profile-photo>

			<h3>{{ $profile->first_name }} {{ $profile->last_name }}</h3>

			<h5 id="profile-username">{{ '@' . $profile->username }}</h5>

			@if($profile->about)
				<p>{{ $profile->about}}</p>
			@endif
		</div>

		@if(Auth::user()->activated)
			@yield('friendship')
		@endif

		@yield('personal')

		@if($friends->count() > 0)
			@include('profile.friends')
		@endif

	</div>
	<div id="profile-feed" class="right-col section">
		<h3>Profile Feed</h3>

		<post-form ref="post" :id="{{ $profile->id }}" :type="'profile'"></post-form>

		<profile-feed :id="{{ $profile->id }}" :feed="{{ json_encode($posts) }}"
			:is-owner="{{ $profile->id === Auth::id() ? 1 : 0 }}">
		</profile-feed>

	</div>
</div>

@stop

@section('vue')
    <script>
        new Vue({
        	el: '#app',

			data: {
		        profileInfoForm: { visible: false }
		    },

        	methods: {
        		resolveRequest(resolution) {
        			axios.post(`/friend/{{ $profile->id }}/resolveRequest`, {
        				resolution: resolution
	                })
	                .then(response => {
	                	location.reload();
	                });
        		},

        		addFriend() {
        			axios.post(`/friend/{{ $profile->id }}/request`, {})
	                .then(response => {
	                	location.reload();
	                });
        		},

				showProfileInfoForm() {
					this.profileInfoForm.visible = true;
				}
        	}
         });
    </script>
@overwrite
