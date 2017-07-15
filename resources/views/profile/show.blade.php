@extends('layout')

@section('nav-settings')
	@if ($profile->isCurrentUser())
		<a @click="showProfileInfoForm" class="link-enhanced clearfix settings">
			<img src="/img/icon/gear-64x64.png">
			<p class="fake-link">Settings</p>
		</a>
	@endif
@stop

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

		{{-- Personal Profile --}}
		@if($profile->isCurrentUser())
			<profile-info-form v-if="profileInfoForm.visible"
				@hide="profileInfoForm.visible = false">
		    </profile-info-form>

			{{-- @if($profile->pendingFriendRequests->count())
				<request-popup :type="'friend'" >
		    	</request-popup>
			@endif

			@if($profile->pendingTripRequests->count())
				<request-popup :type="'trip'" >
		    	</request-popup>
			@endif --}}

		{{-- Other User's Profile--}}
		@elseif (Auth::user()->activated)
			@if (! empty($friendship))
				@if ($friendship->confirmed == 0)
					@if($friendship->requester_id == $profile->id)
						<div class="centered">
							<h6>
								<em>{{ $profile->first_name }}
									has requested to be your friend
								</em>
							</h6>
							<div class="btn" @click="resolveRequest(1)">Accept</div>
							<div class="btn" @click="resolveRequest(-1)">Decline</div>
						</div>
					@else
						<p class="centered"><em>Friend request pending</em></p>
					@endif
				@endif

			@else
				<div class="btn friend-add" @click="addFriend">
					+ Add Friend
				</div>
			@endif
		@endif

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
				friendManager: { visible: false },
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

				hideAll() {
					this.friendManager.visible = false;
				},

				showFriendManager() {
					this.friendManager.visible = true;
				},

				showProfileInfoForm() {
					this.profileInfoForm.visible = true;
				}
        	}
         });
    </script>
@overwrite
