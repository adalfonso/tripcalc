<div id="profile-friends">
	<h5>
		Friends
		<small>
			({{ $friends->count() }})
		</small>
	</h5>
	<ul class="friendList clearfix">
		@foreach ($friends->shuffle()->take(6) as $friend)
			<a href="/profile/{{ $friend->username }}">
			<li>
				{{ $friend->first_name }} {{ $friend->last_name }}
			</li>
			</a>
		@endforeach
	</ul>
</div>
