@extends('profile.index')

@section('friendship')
	@if (! empty($friendship))
		@if ($friendship->confirmed == 1)
			<p><em>You are friends with {{ $profile->first_name }}</em></p>
		@elseif($friendship->confirmed == 0)
			@if($friendship->requester_id == $profile->id)
				<h6>{{ $profile->first_name }} has requested to be your friend</h6>
				<div class="btn" @click="resolveRequest(1)">Accept</div>
				<div class="btn" @click="resolveRequest(-1)">Decline</div>
			@else
				<p><em>Friend request pending</em></p>
			@endif
		@endif
	@else
		<div id="addFriend" class="btn" @click="addFriend">
			+ Add Friend
		</div>
	@endif
@stop

@section('vue')
    <script>
        new Vue({
        	el: '#app',

        	methods: {
        		resolveRequest(resolution) {
        			axios.post(`/friend/requests/{{ $profile->id }}`, {
        				resolution: resolution
	                })
	                .then(response => {
	                	location.reload();
	                });
        		},

        		addFriend() {
        			axios.post(`/users/{{ $profile->id }}/request`, {

	                })
	                .then(response => {
	                	location.reload();
	                });
        		}
        	}
         });
    </script>
@stop
