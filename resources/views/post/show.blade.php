@extends('layout')

@section('content')
	<div id="post-standalone" class="activity-item single-col">
		<post :data="data"
			:id="id"
			:type="type"
			:is-owner="isOwner">
		</post>
	</div>
@endsection

@section('vue')
	<script>
		new Vue({
		    el: '#app',

		    data: {
				data: {!! json_encode($mapped) !!},
				id: {{ $post->postable->id }},
				type: '{{ $type }}',
				isOwner: {{ json_encode($isOwner) }}
		    }
		});
	</script>
@overwrite
