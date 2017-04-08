@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<a href="/profile">My Profile</a>

		@unless (isset($trips))
			<a a href="/trips/dashboard">My Trips</a>
		@endif

		@if (isset($inTrip))
			<a @click="inviteFriend.visible = true">Invite Friends</a>
		@endif

		<logout></logout>

		<search-people></search-people>
	</nav>
@stop