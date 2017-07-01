@section('nav')
	<nav id="nav" class="clearfix">
		<hamburger></hamburger>

		<div id="nav-search" class="clearfix">
			<search-people></search-people>
		</div>

		<div id="nav-links">
			<div id="nav-left" class="clearfix">
				<a href="/profile" class="link-enhanced clearfix">
					<img src="/img/icon/profile-64x64.png">
					<p class="fake-link">
						{{ Auth::user()->first_name }}
					</p>
				</a>

				@unless (isset($trips))
					<a href="/trips">My Trips</a>
				@endif

				@yield('nav-left')
			</div>

			<div id="nav-right" class="clearfix">

				@yield('nav-right')

				<logout></logout>

				@yield('nav-settings')
			</div>
		</div>

	</nav>
@stop
