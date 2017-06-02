@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<a href="/profile">My Profile</a>

		@unless (isset($trips))
			<a href="/trips">My Trips</a>
		@endif

		@if (isset($friendsInvitable))
			<a @click="showInviteFriendsForm">Invite Friends</a>
		@endif

		<logout></logout>

		@if (isset($profile) && $profile->isCurrentUser())
			<a @click="showProfileInfoForm" class="settings">Settings</a>
		@endif

		<search-people></search-people>		
	</nav>
@stop
