@extends('profile.index')

@section('friendship')
	@if (! empty($friendship))
		@if ($friendship->confirmed == 1)
			<p><em>You are friends with {{ $profile->first_name }}</em></p>
		@elseif($friendship->confirmed == 0)
			@if($friendship->requester_id == $profile->id)
				<div id='addFriend' class='btn'>+ Accept Request</div>
			@else
				<p><em>Friend request pending</em></p>
			@endif
		@endif
	@else
		<div id='addFriend' class='btn'>+ Add Friend</div>
	@endif
@stop