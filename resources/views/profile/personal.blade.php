@extends('profile.index')

@section('personal')

	@if($friendRequests->count() > 0)
		<request-popup :type="'friend'" :requests="{{$friendRequests}}">
    	</request-popup>
	@endif

	@if($tripRequests->count() > 0)
		<request-popup :type="'trip'" :requests="{{$tripRequests}}">
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