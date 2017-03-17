@extends('profile.index')

@section('personal')

	@if($friendRequests > 0)
		<request-popup :type="'friend'" >
    	</request-popup>
	@endif

	@if($tripRequests > 0)
		<request-popup :type="'trip'" >
    	</request-popup>
	@endif

	@if(sizeof($friends) > 0)
		<div id="profile-friends">
			<h5>Friends</h5>
			<ul class="friendList clearfix">
				@foreach ($friends as $friend)
					<a href="/profile/{{ $friend->username }}">
					<li>
						{{ $friend->first_name }} {{ $friend->last_name }}
					</li>
					</a>
				@endforeach
			</ul>
		</div>
	@endif
@stop