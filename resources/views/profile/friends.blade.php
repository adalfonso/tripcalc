<div id="profile-friends">
	<h5>
		Friends
		<small>
			({{ $friends->count() }})
		</small>
	</h5>
	<div class="friendList clearfix">
		@foreach ($friends->shuffle()->take(6) as $friend)
			<a href="/profile/{{ $friend->username }}">

			<div class="tile">
				<img src="{{ $friend->currentPhoto->thumbnailPath or '' }}">
				<p>{{ $friend->first_name }} {{ $friend->last_name }}</p>
			</div>

			</a>
		@endforeach
	</div>
</div>
