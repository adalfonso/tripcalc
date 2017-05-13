@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<a href="/profile">My Profile</a>

		@unless (isset($trips))
			<a href="/trips/dashboard">My Trips</a>
		@endif

		@if (isset($friendsInvitable))
			<a @click="showInviteFriendsForm">Invite Friends</a>
		@endif

		<logout></logout>

		<search-people></search-people>
	</nav>
@stop
