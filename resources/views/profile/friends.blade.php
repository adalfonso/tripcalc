<div id="profile-friends">

	<div class="friend-header clearfix">
		<h5>
			Friends
			<small>
				({{ $friends->count() }})
			</small>
		</h5>

		@if (! empty($friendship) && $friendship->confirmed == 1)
			<friend-preferences :id="{{ $profile->id }}" :refresh="true">
			</friend-preferences>
		@endif

		@if($profile->isCurrentUser())
			<friend-manager v-if="friendManager.visible" @hide="hideAll">
			</friend-manager>
			<a class="friend-manage" @click="showFriendManager">
				[Manage]
			</a>
		@endif
	</div>

	<div class="friendList clearfix">
		@foreach ($friends->shuffle()->take(6) as $friend)
			<a href="/profile/{{ $friend->username }}">
				<div class="tile">
					<img src="{{ $friend->currentPhoto->thumbnailPath or '' }}">
					<p class="friend-name">{{ $friend->first_name }} {{ $friend->last_name }}</p>
				</div>
			</a>
		@endforeach
	</div>
</div>
