@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<a href="/profile">My Profile</a>

		@unless (isset($trips))
			<a href="/trips">My Trips</a>
		@endif

		@yield('nav-left')

		<logout></logout>		

		@yield('nav-right')

		<search-people></search-people>
	</nav>
@stop
