@extends('profile.index')

@section('friendship')
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
@stop
