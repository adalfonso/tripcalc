@extends('layout')

@section('content')
	<div id="post-standalone" class="activity-item single-col">
		<post :data="data" :type="type"></post>
	</div>
@endsection

@section('vue')
	<script>
		new Vue({
		    el: '#app',

		    data: {
				data: {!! json_encode($post) !!},
				type: '{{ $type }}'
		    }
		});
	</script>
@overwrite
