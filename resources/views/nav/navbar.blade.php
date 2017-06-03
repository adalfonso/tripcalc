@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<div id="nav-left">
			<a href="/profile">My Profile</a>

			@unless (isset($trips))
				<a href="/trips">My Trips</a>
			@endif

			@yield('nav-left')
		</div>

		<div id="nav-right">
			@yield('nav-right')
			<logout></logout><search-people></search-people>
		</div>

	</nav>
@stop
